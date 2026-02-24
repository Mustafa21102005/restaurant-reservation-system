@extends('layouts.admin')

@section('title', 'Edit Reservation')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">Edit Reservation</h4>
            </div>
            <div>
                <form action="{{ route('reservations.update', $reservation->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-floating mb-3">
                        <select class="form-select" name="user" id="floatingSelect"
                            aria-label="Floating label select example" disabled>
                            <option selected disabled>Choose Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ $reservation->user_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="floatingSelect">Customer</label>
                        <x-error-message field="user" />
                        <input type="hidden" name="user" value="{{ $reservation->user_id }}">
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="table" id="table"
                            aria-label="Floating label select example">
                            <option selected disabled>Choose Table</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}"
                                    {{ $reservation->table_id == $table->id ? 'selected' : '' }}>Table {{ $table->id }}
                                    with a capacity of
                                    {{ $table->capacity }}</option>
                            @endforeach
                        </select>
                        <label for="table">Table</label>
                        <x-error-message field="table" />
                    </div>

                    <div class="form-floating mb-3">
                        <input type="datetime-local" class="form-control" name="datetime" id="datetime"
                            placeholder="Date & Time"
                            value="{{ \Carbon\Carbon::parse($reservation->datetime)->format('Y-m-d\TH:i') }}" required>
                        <label for="datetime">Date & Time</label>
                        <x-error-message field="datetime" />
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" name="info" id="info" placeholder="Additional Information" style="height: 200px"
                            disabled>{{ $reservation->info ?? 'No Adittional Info Provided' }} </textarea>
                        <label for="info">Additional Information</label>
                        <x-error-message field="info" />
                        <input type="hidden" name="info" value="{{ $reservation->info }}">
                    </div>

                    <x-primary-button>Edit Reservation</x-primary-button>
                    <x-cancel-button :href="route('reservations.index')" />
                </form>

            </div>
        </div>
    </div>
@endsection
