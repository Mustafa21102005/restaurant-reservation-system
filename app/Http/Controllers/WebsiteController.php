<?php

namespace App\Http\Controllers;

use App\Http\Requests\{SendReservationRequest, UpdateReservationRequest};
use Illuminate\Support\Facades\Auth;
use App\Models\{Product, Reservation, TableSeat};
use App\Services\ReservationService;
use Carbon\Carbon;
use Exception;

class WebsiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(private ReservationService $reservationService) {}

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
     * Store a new reservation.
     *
     * @param  \App\Http\Requests\SendReservationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendReservation(SendReservationRequest $request)
    {
        try {
            $this->reservationService->create(
                $request->safe()->all(),
                Auth::user(),
                $request->boolean('wants_reminder'),
                (int) $request->input('reminder_time', 10)
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'Reservation successfully made! Check your email for the verification code.');
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
     * Update a reservation.
     *
     * @param  \App\Http\Requests\UpdateReservationRequest  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateReservation(UpdateReservationRequest $request, Reservation $reservation)
    {
        try {
            $this->reservationService->update($reservation, $request->safe()->all(), Auth::user());
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('website.edit.reservation', $reservation->id)
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
        try {
            $this->reservationService->cancel($reservation, Auth::user());
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Reservation successfully cancelled.');
    }
}
