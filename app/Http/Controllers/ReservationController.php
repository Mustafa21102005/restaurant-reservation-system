<?php

namespace App\Http\Controllers;

use App\Http\Requests\{
    CancelReservationRequest,
    FinishReservationRequest,
    StoreReservationRequest,
    UpdateReservationRequest
};
use App\Models\{Reservation, User, Discount, TableSeat};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCompleted;
use App\Services\ReservationService;
use Exception;

class ReservationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private ReservationService $reservationService) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reservations = Reservation::all();

        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = User::role('customer')->get();
        $tables = TableSeat::where('status', 'available')->get();

        return view('admin.reservations.create', compact('customers', 'tables'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReservationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReservationRequest $request)
    {
        $data = $request->validated();
        $user = User::find($data['user']);

        if (!$user) {
            return redirect()->back()->withErrors(['error' => 'User not found.']);
        }

        try {
            $this->reservationService->create(
                $data,
                $user,
                request()->boolean('wants_reminder'),
                (int) request()->input('reminder_time', 10)
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation Created Successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
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
     *
     * @param  \App\Http\Requests\UpdateReservationRequest  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        try {
            $this->reservationService->update($reservation, $request->validated());
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation Updated Successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'table']);

        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation.
     *
     * @param  \App\Http\Requests\CancelReservationRequest  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(CancelReservationRequest $request, Reservation $reservation)
    {
        $this->reservationService->cancel($reservation, reason: $request->input('reason'));

        return redirect()->route('reservations.index')->with('success', 'Reservation Canceled Successfully.');
    }

    /**
     * Verify a reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
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
     * Finish a reservation.
     *
     * @param  \App\Http\Requests\FinishReservationRequest  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
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
