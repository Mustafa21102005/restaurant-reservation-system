<?php

namespace App\Http\Controllers;

use App\Http\Requests\BanCustomerRequest;
use App\Http\Requests\TimeoutCustomerRequest;
use App\Mail\CustomerBannedMail;
use App\Mail\CustomerTimeoutMail;
use App\Mail\CustomerUnbannedMail;
use App\Mail\TimeoutExpiredMail;
use App\Models\Ban;
use App\Models\Timeout;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = User::role('customer')->get();

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $customer)
    {
        // Fetch customer reservations
        $reservations = $customer->reservations;

        // Count the number of reservations canceled by the customer
        $canceledReservationsCount = $customer->reservations()->where('status', 'canceled')->count();

        // Fetch customer timeout
        $timeouts = $customer->timeout ?? collect();

        // Count the number of times the customer has been timed out, including soft-deleted records
        $timeoutsCount = $customer->timeouts()->withTrashed()->count();

        // Count the number of times the customer has been banned, including soft-deleted records
        $bansCount = $customer->bans()->withTrashed()->count();

        return view(
            'admin.customers.show',
            compact(
                'customer',
                'reservations',
                'canceledReservationsCount',
                'timeouts',
                'timeoutsCount',
                'bansCount'
            )
        );
    }

    /**
     * Ban the specified user.
     */
    public function ban(BanCustomerRequest $request, User $customer)
    {
        if (!$customer->hasRole('customer')) {
            return redirect()->back()->with('error', 'Only customers can be banned.');
        }

        if ($customer->isBanned()) {
            return redirect()->back()->with('error', 'Customer is already banned.');
        }

        // Cancel all reservations and free up the associated tables
        foreach ($customer->reservations as $reservation) {
            app(ReservationController::class)->cancelReservation($reservation);
        }

        $data = $request->safe()->all();

        Ban::create([
            'user_id' => $customer->id,
            'reason' => $data['reason'],
            'banned_by' => Auth::id(),
        ]);

        Mail::to($customer->email)->queue(new CustomerBannedMail($data['reason'], $customer));

        return redirect()->back()->with('success', 'Customer has been banned, all reservations cancelled, and tables released.');
    }

    /**
     * Unban the specified user.
     */
    public function unban(User $customer)
    {
        if ($customer->isBanned()) {
            $customer->bans()->latest()->first()->delete();

            Mail::to($customer->email)->queue(new CustomerUnbannedMail($customer));

            return redirect()->back()->with('success', 'Customer has been unbanned and notified via email.');
        }

        return redirect()->back()->with('error', 'Customer is not banned.');
    }

    /**
     * Timeout the specified user.
     */
    public function timeout(TimeoutCustomerRequest $request, User $customer)
    {
        $data = $request->safe()->all();

        Timeout::create([
            'user_id'     => $customer->id,
            'reason'     => $data['reason'],
            'expires_at' => $data['expires_at'],
            'timeout_by'  => Auth::id(),
        ]);

        Mail::to($customer->email)->queue(new CustomerTimeoutMail(
            $customer,
            $data['reason'],
            $data['expires_at']
        ));

        return redirect()->back()->with('success', 'Customer has been timed out and notified via email.');
    }

    /**
     * Remove the Timeout of this specified user.
     */
    public function untimeout(User $customer)
    {
        $timeout = $customer->timeouts()->where('expires_at', '>', now())->latest()->first();

        if ($timeout) {
            $timeout->delete();

            Mail::to($customer->email)->queue(new TimeoutExpiredMail($timeout));

            return redirect()->back()->with('success', 'Customer timeout has been removed and email sent.');
        }

        return redirect()->back()->with('error', 'No active timeout found for this customer.');
    }
}
