@extends('layouts.theme')

@section('title', 'Edit Reservation')
@section('content')
    <section class="s-pageheader pageheader mb-5"
        style="background-image: url('{{ asset('theme/images/pageheader/pageheader-reservations-bg-3000.jpg') }}')">
        <div class="row">
            <div class="column xl-12 s-pageheader__content">
                <h1 class="page-title">
                    Edit Reservation
                </h1>
            </div>
        </div>
    </section>

    <div class="row width-narrower content-block mt-5" id="reservation">
        <div class="column xl-12">

            <h2>Edit Reservation Form</h2>

            <x-alert-success />
            <x-alert-error />

            <p class="alert alert-warning mb-5">
                You can only edit a reservation once, so please make sure all details are correct before submitting.
            </p>

            <form class="reservation-form" method="post"
                action="{{ route('website.update.reservation', ['reservation' => $reservation->id]) }}" class="mt-3">
                @csrf
                @method('PUT')
                <fieldset class="row">

                    <div class="column xl-6 tab-12">
                        <label for="datetime">Date & Time: </label>
                        <input type="datetime-local" name="datetime" id="datetime" class="u-fullwidth"
                            value="{{ old('datetime', $reservation->datetime) }}" required>
                    </div>

                    <div class="column xl-6 tab-12">
                        <label for="table">Table Number</label>
                        <select name="table" id="table" class="u-fullwidth" required>
                            @if ($availableTables->isEmpty())
                                <option selected disabled>No tables available, please check after some time.</option>
                            @else
                                <option selected disabled>Select a Table</option>
                                @foreach ($availableTables as $table)
                                    <option value="{{ $table->id }}"
                                        {{ $reservation->table_id == $table->id ? 'selected' : '' }}>
                                        Table {{ $table->id }} with a capacity of {{ $table->capacity }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="column xl-12 message u-add-bottom">
                        <label for="radd-info">Any Additional Information: </label>
                        <textarea name="info" id="info" class="u-fullwidth" placeholder="Type your additional info here">{{ old('info', $reservation->info) }}</textarea>
                    </div>

                    <div class="rform__bottom column xl-12">
                        <div class="column xl-12 tab-12">
                            <input name="submit" id="submit" class="btn btn--primary btn-wide btn--large u-fullwidth"
                                value="Update Reservation" type="submit">
                        </div>
                    </div>
                </fieldset>
            </form>

        </div>
    </div>
@endsection
