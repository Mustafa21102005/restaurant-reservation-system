@extends('layouts.admin')

@section('title', 'Reservation Details')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="text-white">Reservation Details</h4>
                <x-back-button :route="route('reservations.index')">Reservations</x-back-button>
            </div>

            <div class="row">
                {{-- Left Column - Reservation Information --}}
                <div class="col-md-6">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td>{{ $reservation->id }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $reservation->datetime }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ ucfirst($reservation->status) }}</td>
                            </tr>
                            <tr>
                                <th>More Info</th>
                                <td>{{ $reservation->info ?? 'No Info Provided' }}</td>
                            </tr>
                            <tr>
                                <th>Warning email</th>
                                <td>{{ $reservation->warning_sent_at ?? 'Email not sent' }}</td>
                            </tr>
                            <tr>
                                <th>Verification Code</th>
                                <td>
                                    <span id="code" class="text-danger" onclick="toggleCode()" style="cursor: pointer;">
                                        ••••••••••
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Right Column - Associated Table and Customer --}}
                <div class="col-md-6">
                    <h5 class="text-white mb-3">Associated Details</h5>
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>Table</th>
                                <td>Table
                                    <a href="{{ route('tables.show', $reservation->table_id) }}">
                                        #{{ $reservation->table->id }}</a> (Capacity:
                                    {{ $reservation->table->capacity }})

                                </td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td>
                                    <a href="{{ route('customers.show', $reservation->user_id) }}">
                                        {{ $reservation->user->name }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const realCode = @json($reservation->verification_code);
        let codeVisible = false;

        function toggleCode() {
            const codeEl = document.getElementById('code');
            if (codeVisible) {
                codeEl.textContent = '••••••••••';
            } else {
                codeEl.textContent = realCode;
            }
            codeVisible = !codeVisible;
        }
    </script>
@endsection
