@extends('layouts.admin')

@section('title', 'Create Reservation')

@section('styles')
    <style>
        /* Select2 container */
        .select2-container--default .select2-selection--single {
            background-color: #2a2e33;
            /* matches dark admin bg */
            border: 1px solid #444;
            height: 38px;
            display: flex;
            align-items: center;
        }

        /* Text inside */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #fff;
            line-height: 38px;
        }

        /* Arrow */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        /* Dropdown */
        .select2-container--default .select2-dropdown {
            background-color: #2a2e33;
            border: 1px solid #444;
        }

        /* Options */
        .select2-container--default .select2-results__option {
            color: #ddd;
        }

        /* Hover / selected */
        .select2-container--default .select2-results__option--highlighted {
            background-color: #eb1616;
            color: white;
        }

        /* Selected option */
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eb1616;
        }
    </style>
@endsection

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">Create a Reservation</h4>
            </div>

            <x-alert-error />

            <div>
                <form action="{{ route('reservations.store') }}" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <select class="form-select" name="user" id="user" aria-label="Choose a Customer">
                            <option disabled selected>Choose a Customer</option>
                            @if ($customers->isEmpty())
                                <option disabled>No customers available</option>
                            @else
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->email }}</option>
                                @endforeach
                            @endif
                        </select>
                        <x-error-message field="user" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="table" id="table" aria-label="Choose a Table">
                            <option disabled selected>Choose a Table</option>
                            @if ($tables->isEmpty())
                                <option disabled>No tables available</option>
                            @else
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}">
                                        Table {{ $table->id }} with a capacity of {{ $table->capacity }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <x-error-message field="table" />
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="datetime" id="datetime" required
                            value="{{ old('datetime') }}">
                        <label for="datetime">Date & Time</label>
                        <x-error-message field="datetime" />
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="info" id="info" placeholder="Additional Information" style="height: 200px">{{ old('info') }}</textarea>
                        <label for="info">Additional Information</label>
                        <x-error-message field="info" />
                    </div>

                    <div class="form-check mb-3">
                        <label class="form-check-label" for="wants_reminder">
                            <input class="form-check-input" type="checkbox" name="wants_reminder" id="wants_reminder">
                            Send an email reminder
                        </label>
                    </div>

                    <div class="form-floating mb-3" id="reminder_time_container" style="display: none;">
                        <select class="form-select" name="reminder_time" id="reminder_time">
                            <option value="30">30 minutes before</option>
                            <option value="60">1 hour before</option>
                            <option value="120">2 hours before</option>
                        </select>
                        <label for="reminder_time">When should we remind the customer?</label>
                    </div>

                    <x-primary-button>Create Reservation</x-primary-button>
                    <x-cancel-button :href="route('reservations.index')" />
                </form>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#user').select2();
        $('#table').select2();

        flatpickr("#datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            allowInput: true
        });

        document.getElementById('wants_reminder').addEventListener('change', function() {
            const reminderBox = document.getElementById('reminder_time_container');
            if (this.checked) {
                reminderBox.style.display = 'block';
                reminderBox.style.opacity = 0;
                let opacity = 0;
                const fadeIn = setInterval(() => {
                    opacity += 0.1;
                    reminderBox.style.opacity = opacity;
                    if (opacity >= 1) clearInterval(fadeIn);
                }, 30);
            } else {
                let opacity = 1;
                const fadeOut = setInterval(() => {
                    opacity -= 0.1;
                    reminderBox.style.opacity = opacity;
                    if (opacity <= 0) {
                        clearInterval(fadeOut);
                        reminderBox.style.display = 'none';
                    }
                }, 30);
            }
        });
    </script>
@endsection
