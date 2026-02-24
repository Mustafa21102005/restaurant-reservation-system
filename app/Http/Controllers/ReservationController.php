<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelReservationRequest;
use App\Http\Requests\FinishReservationRequest;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Mail\ReservationCanceled;
use App\Mail\ReservationCompleted;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\ReservationReminder;
use App\Mail\ReservationUpdated;
use App\Models\Discount;
use App\Models\TableSeat;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::all();

        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = User::role('customer')->get();
        $tables = TableSeat::where('status', 'available')->get();

        return view('admin.reservations.create', compact('customers', 'tables'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReservationRequest $request)
    {
        $data = $request->validated();

        $user = User::find($data['user']);

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'User not found.']);
        }

        // Check if the user is banned
        if ($user->isBanned()) {
            return redirect()->back()->withErrors(['error' => 'This customer is banned and cannot make a reservation.']);
        }

        // Check if the user is in timeout
        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                return redirect()->back()
                    ->withErrors(['error' => 'This customer is in timeout until ' . $timeout->expires_at . '.']);
            }
        }

        // Check for existing reservation
        $existingReservation = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['ongoing', 'seated'])
            ->first();

        if ($existingReservation) {
            return redirect()->back()->withErrors(['error' => 'Customer already has a reservation.']);
        }

        // Lock the table and check availability
        $table = TableSeat::lockForUpdate()->find($request->table);
        if (!$table || $table->status !== 'available') {
            return redirect()->back()->withErrors(['error' => 'Table is not available.']);
        }

        DB::transaction(function () use ($data, $user) {

            $table = TableSeat::lockForUpdate()->find($data['table']);

            if (!$table || $table->status !== 'available') {
                abort(422, 'Table is not available.');
            }

            $verificationCode = strtoupper(Str::random(6));

            $reservation = Reservation::create([
                'user_id'           => $user->id,
                'table_id'          => $data['table'],
                'datetime'          => $data['datetime'],
                'info'              => $data['info'] ?? null,
                'verification_code' => $verificationCode,
                'status'            => 'ongoing',
            ]);

            $table->update(['status' => 'reserved']);

            Mail::to($user->email)->queue(
                new ReservationConfirmation($user, $verificationCode)
            );

            if (request()->boolean('wants_reminder')) {
                $minutes = (int) request()->input('reminder_time', 10);
                $sendAt  = Carbon::parse($data['datetime'])->subMinutes($minutes);

                Mail::to($user->email)->later(
                    $sendAt,
                    new ReservationReminder($reservation)
                );
            }
        });

        return redirect()->route('reservations.index')->with('success', 'Reservation Created Successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $customers = User::role('customer')->get();
        // Get all available tables or the reserved table
        $tables = TableSeat::where('status', 'available')->orWhere('id', $reservation->table_id)->get();

        return view('admin.reservations.edit', compact('reservation', 'customers', 'tables'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $reservation) {

            // Lock current reservation row
            $reservation->lockForUpdate();

            // If table changed, update table statuses safely
            if ($reservation->table_id != $validated['table']) {

                $oldTable = TableSeat::lockForUpdate()->find($reservation->table_id);
                $newTable = TableSeat::lockForUpdate()->find($validated['table']);

                if ($newTable->status !== 'available') {
                    abort(422, 'Selected table is no longer available.');
                }

                $oldTable->update(['status' => 'available']);
                $newTable->update(['status' => 'reserved']);
            }

            $reservation->update([
                'table_id' => $validated['table'],
                'datetime' => $validated['datetime'],
                'info'     => $validated['info'] ?? null,
            ]);
        });

        Mail::to($reservation->user->email)->queue(new ReservationUpdated($reservation));

        return redirect()->route('reservations.index')->with('success', 'Reservation Updated Successfully.');
    }

    /**
     * Show the details of a specific reservation.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'table']);

        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation and update the related table's status to available.
     *
     * @param \App\Models\Reservation $reservation The reservation to be canceled.
     * @param string|null $reason Optional reason for the cancellation, used for notifying the user via email.
     *
     * @return void
     */
    public function cancelReservation(Reservation $reservation, string $reason = null)
    {
        $table = TableSeat::find($reservation->table_id);

        if ($table) {
            $table->status = 'available';
            $table->save();
        }

        $reservation->status = 'canceled';
        $reservation->save();

        if ($reason) {
            Mail::to($reservation->user->email)->queue(new ReservationCanceled($reservation, $reason));
        }
    }

    /**
     * Cancel the specified reservation.
     */
    public function cancel(CancelReservationRequest $request, Reservation $reservation)
    {
        $reason = $request->input('reason');

        $this->cancelReservation($reservation, $reason);

        return redirect()->route('reservations.index')->with('success', 'Reservation Canceled Successfully.');
    }

    /**
     * Verify the specified reservation.
     */
    public function verify(Reservation $reservation)
    {
        // Get the reservation by verification code
        $reservation = Reservation::where('verification_code', request('verification_code'))->first();

        if (!$reservation) {
            return redirect()->back()->withErrors(['error' => 'Verification code is incorrect.']);
        }

        // Check if a discount code is provided
        if (request('discount_code')) {
            $discount = Discount::where('code', request('discount_code'))->where('used', false)->first();

            // Validate the discount code
            if (!$discount) {
                return redirect()->back()->withErrors(['error' => 'Invalid or already used discount code.']);
            }

            // Mark the discount code as used
            $discount->used = true;
            $discount->save();
        }

        // Update the reservation status to 'seated'
        $reservation->status = 'seated';
        $reservation->save();

        return redirect()->route('reservations.index')->with('success', 'Reservation Verified Successfully.');
    }

    /**
     * Finish the specified reservation.
     */
    public function finish(FinishReservationRequest $request, Reservation $reservation)
    {
        $data = $request->validated();

        DB::transaction(function () use ($reservation, $data) {

            // Finish reservation
            $reservation->update(['status' => 'done']);

            // Release table
            $reservation->table->update(['status' => 'available']);

            $discount = null;

            if (($data['send_discount'] ?? null) === 'yes') {
                $discount = Discount::create([
                    'reservation_id' => $reservation->id,
                    'code'            => strtoupper(Str::random(8)),
                    'percentage'      => $data['discount_percentage'],
                ]);
            }

            Mail::to($reservation->user->email)->queue(
                new ReservationCompleted([
                    'name'               => $reservation->user->name,
                    'discount'           => $discount?->percentage,
                    'discount_code'      => $discount?->code,
                ])
            );
        });

        return redirect()->route('reservations.index')->with('success', 'Reservation completed successfully.');
    }
}
