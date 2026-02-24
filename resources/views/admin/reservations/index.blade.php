@extends('layouts.admin')

@section('title', 'Reservations')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Reservations</h3>
                <x-create-button route="reservations.create">Create Reservation</x-create-button>
            </div>

            <x-alert-success />
            <x-alert-error />

            <div class="table-responsive">
                @if ($reservations->isEmpty())
                    <div class="alert alert-dark text-center">No reservations made.
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f613/512.gif" width="26">
                    </div>
                @else
                    <table id="reservations-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">ID</th>
                                <th scope="col">Email</th>
                                <th scope="col">Table Number</th>
                                <th scope="col">Date & Time</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservations as $reservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('reservations.show', $reservation->id) }}">
                                            {{ $reservation->id }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('customers.show', $reservation->user->id) }}">
                                            {{ $reservation->user->email }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('tables.show', $reservation->table->id) }}">
                                            {{ $reservation->table->id }}
                                        </a>
                                    </td>
                                    <td>{{ $reservation->datetime }}</td>
                                    <td>{{ ucfirst($reservation->status) }}</td>
                                    <td>
                                        @if ($reservation->status == 'done' || $reservation->status == 'canceled' || $reservation->status == 'no_show')
                                            <div>No Actions Needed.
                                            </div>
                                        @elseif ($reservation->status == 'seated')
                                            {{-- finish button --}}
                                            <button class="btn btn-outline-primary me-1 finish-btn" data-bs-toggle="modal"
                                                data-bs-target="#finishModal" data-id="{{ $reservation->id }}">
                                                Finish
                                            </button>
                                        @elseif ($reservation->status == 'ongoing')
                                            {{-- verify button --}}
                                            <button class="btn btn-outline-success me-1 verify-btn" data-bs-toggle="modal"
                                                data-bs-target="#verifyModal" data-id="{{ $reservation->id }}">
                                                Verify
                                            </button>

                                            {{-- edit button --}}
                                            <x-edit-button route="reservations.edit" :resource-id="$reservation->id" />

                                            {{-- cancel button --}}
                                            <button type="button" class="btn btn-outline-danger me-1"
                                                data-bs-toggle="modal" data-bs-target="#cancelModal"
                                                data-id="{{ $reservation->id }}">
                                                Cancel
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Cancel Reservation Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="cancel-form">
                @csrf
                @method('PATCH')
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Cancel Reservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for cancellation</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required
                                placeholder="Reason for Cancelation"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Verification Reservation Modal --}}
    <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyModalLabel">Confirm Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter the verification code to confirm this reservation:</p>
                    <form id="verifyForm" method="POST"
                        action="{{ isset($reservation) ? route('reservations.verify', $reservation->id) : '#' }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" name="verification_code" id="verification_code"
                                placeholder="Enter verification code" required>
                        </div>
                        <div class="mb-3">
                            <label for="discount_code" class="form-label">Discount Code (Optional)</label>
                            <input type="text" class="form-control" name="discount_code" id="discount_code"
                                placeholder="Enter discount code">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Finish Reservation Modal --}}
    <div class="modal fade" id="finishModal" tabindex="-1" aria-labelledby="finishModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="finishModalLabel">Finish Reservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="finishForm" method="POST"
                        action="{{ isset($reservation) ? route('reservations.finish', $reservation->id) : '#' }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="reservation_id" id="reservationId">

                        <div class="mb-3">
                            <label class="form-label">Send Discount?</label>
                            <input type="checkbox" id="sendDiscount" name="send_discount" value="yes">
                        </div>

                        <div class="mb-3" id="discountSelection" style="display: none;">
                            <label for="discountPercentage" class="form-label">Enter Discount Percentage</label>
                            <input type="number" class="form-control" name="discount_percentage"
                                id="discountPercentage" min="0" max="100"
                                placeholder="Enter discount percentage">
                        </div>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm & Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Cancel Modal Script
        const cancelModal = document.getElementById('cancelModal');
        cancelModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const reservationId = button.getAttribute('data-id');
            const form = document.getElementById('cancel-form');

            form.action = "{{ url('reservations') }}/" + reservationId + "/cancel";
        });

        // Verify Modal Script
        document.addEventListener("DOMContentLoaded", function() {
            const verifyButtons = document.querySelectorAll(".verify-btn");
            verifyButtons.forEach(button => {
                button.addEventListener("click", function() {
                    let reservationId = this.getAttribute("data-id");
                    let verifyForm = document.getElementById("verifyForm");

                    // Ensure we set the correct action
                    verifyForm.action = "{{ url('reservations') }}/" + reservationId + "/verify";

                    // Set the reservation ID in the hidden input field
                    document.getElementById("reservationId").value = reservationId;
                });
            });
        });

        // Finish Modal Script
        document.addEventListener("DOMContentLoaded", function() {
            // Handle Finish button click
            const finishButtons = document.querySelectorAll(".finish-btn");
            finishButtons.forEach(button => {
                button.addEventListener("click", function() {
                    let reservationId = this.getAttribute("data-id");
                    document.getElementById("reservationId").value = reservationId;
                });
            });

            // Handle discount checkbox
            document.getElementById("sendDiscount").addEventListener("change", function() {
                let discountSelection = document.getElementById("discountSelection");
                if (this.checked) {
                    discountSelection.style.display = "block";
                } else {
                    discountSelection.style.display = "none";
                }
            });
        });

        // Datatables Init
        $(document).ready(function() {
            $('#reservations-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Reservations"
                },
                "order": [0, "desc"]
            });
        });
    </script>
@endsection
