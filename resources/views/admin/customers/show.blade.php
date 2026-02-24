@extends('layouts.admin')

@section('title', 'Customer Details')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="text-white">Customer Details</h4>
                <x-back-button :route="route('customers.index')">Customers</x-back-button>
            </div>

            <div class="row">
                {{-- Left Column - Customer Information --}}
                <div class="col-md-6">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td>{{ $customer->id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $customer->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($customer->isBanned())
                                        <span class="badge bg-danger">Banned</span>
                                    @elseif ($customer->isTimedOut())
                                        <span class="badge bg-warning text-dark">Timed Out</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif

                                </td>
                            </tr>
                            <tr>
                                <th>Email Verified</th>
                                <td>
                                    @if ($customer->email_verified_at)
                                        <i class="fas fa-check text-success"></i>
                                    @else
                                        <i class="fas fa-times text-danger"></i>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Amount of Canceled Reservations</th>
                                <td>{{ $canceledReservationsCount }}</td>
                            </tr>
                            <tr>
                                <th>Times Timed Out</th>
                                <td>{{ $timeoutsCount }}</td>
                            </tr>
                            <tr>
                                <th>Times Banned</th>
                                <td>{{ $bansCount }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Right Column - Reservations --}}
                <div class="col-md-6">
                    <h5 class="text-white">Reservations</h5>
                    @if ($reservations->isEmpty())
                        <div class="alert alert-dark text-center">No reservations found.
                        </div>
                    @else
                        <table id="customer-table" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reservations as $reservation)
                                    <tr>
                                        <td><a
                                                href="{{ route('reservations.show', $reservation->id) }}">{{ $reservation->id }}</a>
                                        </td>
                                        <td>{{ $reservation->datetime }}</td>
                                        <td>{{ ucfirst($reservation->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#customer-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Reservations"
                }
            });
        });
    </script>
@endsection
