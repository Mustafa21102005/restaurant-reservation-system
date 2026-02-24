<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmation;
use App\Mail\ReservationReminder;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\TableSeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WebsiteController extends Controller
{
    /**
     * Display the home page with signature products.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        $signatureProducts = Product::where('type', 'signature')->get();

        return view('website.home', compact('signatureProducts'));
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('website.about');
    }

    /**
     * Display the menu with products grouped by category.
     *
     * @return \Illuminate\View\View
     */
    public function menu()
    {
        $products = Product::orderBy('category_id')->get()->groupBy(function ($product) {
            return strtolower(trim($product->category->name));
        });

        return view('website.menu', compact('products'));
    }

    /**
     * Display the reservation page with available tables.
     *
     * @return \Illuminate\View\View
     */
    public function reservation()
    {
        $tables = TableSeat::where('status', 'available')->get();

        return view('website.reservation', compact('tables'));
    }

    /**
     * Send a reservation request.
     *
     * @param  \App\Http\Requests\SendReservationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendReservation(SendReservationRequest $request)
    {
        $user = Auth::user();
        $data = $request->safe()->all();

        // Ban check
        if ($user->isBanned()) {
            return redirect()->back()
                ->withErrors(['error' => 'Your account is banned. You cannot make a reservation.']);
        }

        // Timeout check
        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()
                ->where('expires_at', '>', now())
                ->first();

            if ($timeout) {
                $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                return redirect()->back()
                    ->withErrors(['error' => "You are currently in timeout. Please try again after $expiresAt."]);
            }
        }

        // Existing reservation check
        $hasActiveReservation = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['ongoing', 'seated'])
            ->exists();

        if ($hasActiveReservation) {
            return redirect()->back()
                ->withErrors(['error' => 'You already have an active reservation.']);
        }

        try {
            DB::beginTransaction();

            // Lock table inside transaction
            $table = TableSeat::lockForUpdate()->find($data['table']);

            if (! $table || $table->status !== 'available') {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['error' => 'Table is not available.']);
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

            Mail::to($user->email)
                ->queue(new ReservationConfirmation($user, $verificationCode));

            if ($request->boolean('wants_reminder')) {
                $reminderMinutes = (int) $request->input('reminder_time', 10);
                $sendAt = Carbon::parse($data['datetime'])->subMinutes($reminderMinutes);

                Mail::to($user->email)
                    ->later($sendAt, new ReservationReminder($reservation));
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Reservation successfully made! Check your email for the verification code.');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Reservation error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Display the user's reservations.
     *
     * @return \Illuminate\View\View
     */
    public function myReservation()
    {
        $reservations = Reservation::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(4);

        return view('website.reservations.index', compact('reservations'));
    }

    /**
     * Display the edit reservation page.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editReservation(Reservation $reservation)
    {
        $user = Auth::user();

        if ($user->isBanned()) {
            return redirect()->back()->withErrors(['error' => 'Your account is banned. You cannot edit reservations.']);
        }

        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                return redirect()->back()->withErrors(['error' => "You are currently in timeout. Please try again after $expiresAt."]);
            }
        }

        if ($reservation->user_id !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to edit this reservation.']);
        }

        if ($reservation->status !== 'ongoing') {
            return redirect()->back()->withErrors(['error' => 'Reservation is not ongoing.']);
        }

        $availableTables = TableSeat::where('status', 'available')->get();

        if (!$availableTables->contains('id', $reservation->table_id)) {
            $reservedTable = TableSeat::find($reservation->table_id);
            if ($reservedTable) {
                $availableTables->push($reservedTable);
            }
        }

        return view('website.reservations.edit', compact('reservation', 'availableTables'));
    }

    /**
     * Update the reservation.
     *
     * @param  \App\Http\Requests\UpdateReservationRequest  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateReservation(UpdateReservationRequest $request, Reservation $reservation)
    {
        $user = Auth::user();
        $data = $request->safe()->all();

        if ($user->isBanned()) {
            return redirect()->back()->withErrors(['error' => 'Your account is banned. You cannot update the reservation.']);
        }

        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                return redirect()->back()->withErrors(['error' => "You are currently in timeout. Please try again after $expiresAt."]);
            }
        }

        // Check if user has already updated
        $hasUpdated = DB::table('reservation_updates')
            ->where('reservation_id', $reservation->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($hasUpdated && !$user->hasRole('admin')) {
            return redirect()->back()->withErrors(['error' => 'You can only update your reservation once.']);
        }

        // Update reservation
        $reservation->update([
            'table_id' => $request->table,
            'datetime' => $request->datetime,
            'info' => $request->info,
        ]);

        DB::transaction(function () use ($reservation, $user, $data) {
            $reservation->update([
                'table_id' => $data['table'],
                'datetime' => $data['datetime'],
                'info' => $data['info'] ?? null,
            ]);

            DB::table('reservation_updates')->insert([
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('website.edit.reservation', ['reservation' => $reservation->id])
            ->with('success', 'Reservation updated successfully.');
    }

    /**
     * Display a specific reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\View\View
     */
    public function showReservation(Reservation $reservation)
    {
        abort_unless($reservation->user_id === Auth::id(), 403);

        return view('website.reservations.show', compact('reservation'));
    }

    /**
     * Cancel a reservation.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelReservation(Reservation $reservation)
    {
        $user = Auth::user();

        if ($user->isBanned()) {
            return redirect()->back()->withErrors(['error' => 'Your account is banned. You cannot cancel the reservation.']);
        }

        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                $expiresAt = Carbon::parse($timeout->expires_at)->diffForHumans();
                return redirect()->back()->withErrors(['error' => "You are currently in timeout. Please try again after $expiresAt."]);
            }
        }

        if ($reservation->user_id !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to cancel this reservation.']);
        }

        if ($reservation->status !== 'ongoing') {
            return redirect()->back()->withErrors(['error' => 'Reservation is not ongoing.']);
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update(['status' => 'canceled']);
            $reservation->table->update(['status' => 'available']);
        });

        return redirect()->back()->with('success', 'Reservation successfully cancelled.');
    }
}
