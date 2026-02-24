@extends('layouts.admin')

@section('title', 'Table Details')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="text-white">Table Details</h4>
                <x-back-button :route="route('tables.index')">Tables</x-back-button>
            </div>

            <div class="row">
                {{-- Left Column - Table Information --}}
                <div class="col-md-6">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Capacity</th>
                            </tr>
                            <tr>
                                <td>{{ $table->id }}</td>
                                <td>{{ $table->capacity }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Right Column - Reservations --}}
                <div class="col-md-6">
                    <h5 class="text-white mb-3">Reservation for this Table</h5>
                    @if ($reservations)
                        <table id="table-reserv" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reservation ID</th>
                                    <th>Customer Name</th>
                                    <th>Reservation Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <a href="{{ route('reservations.show', $reservations->id) }}">
                                            {{ $reservations->id }}
                                        </a>
                                    </td>
                                    <td><a href="{{ route('customers.show', $reservations->user_id) }}">
                                            {{ $reservations->user->name }}
                                        </a>
                                    </td>
                                    <td>{{ $reservations->datetime }}</td>
                                    <td>{{ ucfirst($reservations->status) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-dark text-center">
                            This table has no reservation.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#table-reserv').DataTable({
                "language": {
                    "searchPlaceholder": "Search Reservations"
                }
            });
        });
    </script>
@endsection
