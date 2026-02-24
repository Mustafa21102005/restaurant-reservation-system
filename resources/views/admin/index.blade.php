@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total Reservations</p>
                        <h6 class="mb-0">{{ $reservationCount }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-area fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Today Reservation</p>
                        <h6 class="mb-0">{{ $todayReservations }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-users fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total Customers</p>
                        <h6 class="mb-0">{{ $customerCount }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-clock fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Ongoing Reservations</p>
                        <h6 class="mb-0">{{ $ongoingReservations }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('reservations.index') }}" class="btn btn-primary w-100">Manage Reservations</a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('reservations.create') }}" class="btn btn-primary w-100">Create Reservation</a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('tables.index') }}" class="btn btn-primary w-100">Manage Tables</a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('customers.index') }}" class="btn btn-primary w-100">Manage Customers</a>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="mb-0">Recent Reservations</h5>
                <a href="{{ route('reservations.index') }}">Show All</a>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-white">
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Table Number</th>
                            <th scope="col">Date & Time</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($recentReservations->isEmpty())
                            <div class="alert alert-dark text-center">No recent reservations made.
                                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f613/512.gif" width="26">
                            </div>
                        @else
                            @foreach ($recentReservations as $reservation)
                                <tr>
                                    <td><a href="{{ route('reservations.show', $reservation->id) }}">{{ $reservation->id }}
                                        </a>
                                    </td>
                                    <td><a
                                            href="{{ route('customers.show', $reservation->user->id) }}">{{ $reservation->user->name }}</a>
                                    </td>
                                    <td><a
                                            href="{{ route('tables.show', $reservation->table->id) }}">{{ $reservation->table->id }}</a>
                                    </td>
                                    <td>{{ $reservation->created_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ ucfirst($reservation->status) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Calender</h6>
                    </div>
                    <div id="calender"></div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Reservation Trends</h6>
                    </div>
                    <canvas id="reservationChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-xl-4">
                <div class="h-100 bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Top Customers</h6>
                        <a href="{{ route('customers.index') }}">Show All</a>
                    </div>
                    @if ($topCustomers->isEmpty())
                        <div class="alert alert-dark text-center">No top customers found.
                        </div>
                    @else
                        @foreach ($topCustomers as $customer)
                            <div class="d-flex align-items-center border-bottom py-3">
                                <div class="w-100 ms-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-0"><a
                                                href="{{ route('customers.show', $customer->id) }}">{{ $customer->name }}</a>
                                        </h6>
                                        <small>{{ $customer->reservations_count }} Reservations</small>
                                    </div>
                                    <span>{{ $customer->email }}</span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <h5 class="mb-4">Table Occupancy Rate</h5>
            <h3>{{ round($occupancyRate, 2) }}%</h3>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Init Chart js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById("reservationChart").getContext("2d");

        var myChart = new Chart(ctx, {
            // Specify the chart type as a line chart
            type: "line",
            data: {
                // Labels for the x-axis (e.g., months), passed from PHP and encoded as JSON
                labels: {!! json_encode($months) !!},

                // Define the dataset to be plotted
                datasets: [{
                    label: "Reservations", // Name shown in the legend and tooltip
                    fill: false, // Do not fill the area under the line
                    backgroundColor: "rgba(235, 22, 22, .7)", // Dot color on the line
                    borderColor: "rgba(235, 22, 22, 1)", // Line color
                    // Y-axis values, passed from PHP and encoded as JSON
                    data: {!! json_encode($reservationCounts) !!}
                }]
            },
            options: {
                // Make the chart responsive to window resizing
                responsive: true,
                scales: {
                    y: {
                        // Start the y-axis at 0 instead of the minimum data value
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
