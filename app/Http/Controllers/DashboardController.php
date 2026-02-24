<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\TableSeat;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $reservationCount = Reservation::count(); // Get total number of reservations

        $todayReservations = Reservation::whereDate('created_at', Carbon::today())->count(); // Today's reservations

        $customerCount = User::role('customer')->count(); // Count of customers

        $ongoingReservations = Reservation::where('status', 'ongoing')->count(); // Count of ongoing reservations

        $recentReservations = Reservation::latest()->take(3)->get(); // Get 3 most recent reservations

        // Get monthly reservation counts
        $reservations = Reservation::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Format data for the chart
        $months = [];
        $reservationCounts = [];

        foreach ($reservations as $reservation) {
            $months[] = Carbon::create()->month($reservation->month)->format('F'); // Convert month number to name
            $reservationCounts[] = $reservation->count;
        }

        $totalTables = TableSeat::count();
        $occupiedTables = Reservation::whereIn('status', ['ongoing', 'seated'])->count();
        $occupancyRate = $totalTables > 0 ? ($occupiedTables / $totalTables) * 100 : 0;

        // Fetch top customers based on reservation count, excluding users with 0 reservations
        $topCustomers = User::withCount('reservations')
            ->having('reservations_count', '>', 0) // Exclude users with 0 reservations
            ->orderBy('reservations_count', 'desc')
            ->take(3) // Limit to top 3 customers
            ->get();

        return view('admin.index', compact(
            'reservationCount',
            'todayReservations',
            'recentReservations',
            'customerCount',
            'ongoingReservations',
            'reservationCounts',
            'months',
            'totalTables',
            'occupiedTables',
            'occupancyRate',
            'topCustomers'
        ));
    }
}
