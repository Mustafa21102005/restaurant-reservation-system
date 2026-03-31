@extends('layouts.theme')

@section('title', 'Reservations')

@section('content')
    <article class="s-content">

        <section class="s-pageheader pageheader"
            style="background-image: url('{{ asset('theme/images/pageheader/pageheader-reservations-bg-3000.jpg') }}')">
            <div class="row">
                <div class="column xl-12 s-pageheader__content">
                    <h1 class="page-title">
                        Reservations
                    </h1>
                </div>
            </div>
        </section>

        <section class="s-pagecontent pagecontent">

            <div class="row width-narrower pageintro text-center">
                <div class="column xl-12">
                    <p class="lead">
                        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Alias eos quas blanditiis, quos
                        sint nostrum fugit aperiam
                        inventore optio itaque molestias corporis, ipsa tenetur eligendi nihil iste porro, natus
                        culpa consequuntur.
                    </p>
                </div>
            </div>

            <div class="row width-narrower content-block" id="reservation">
                <div class="column xl-12">

                    <h2>Reservation Form</h2>

                    <p class="contact-info">
                        If you want to reserve more than one table, please contact us at (123) 456-7890.
                    </p>

                    <x-alert-success />
                    <x-alert-error />

                    <form class="reservation-form" method="post" action="{{ route('sendReservation') }}" class="mt-3">
                        @csrf
                        <fieldset class="row">

                            <div class="column xl-6 tab-12">
                                <label for="datetime">Date & Time: </label>
                                <input type="datetime-local" name="datetime" id="datetime" class="u-fullwidth" required>
                            </div>

                            <div class="column xl-6 tab-12">
                                <label for="table">Table Number</label>
                                <select name="table" id="table" class="u-fullwidth" required>
                                    @if ($tables->isEmpty())
                                        <option selected disabled>No tables available, please check after some time.
                                        </option>
                                    @else
                                        <option selected disabled>Select a Table</option>
                                        @foreach ($tables as $table)
                                            <option value="{{ $table->id }}">Table {{ $table->id }} with a capacity of
                                                {{ $table->capacity }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="column xl-12 message u-add-bottom">
                                <label for="info">Any Additional Information: </label>
                                <textarea name="info" id="info" class="u-fullwidth" placeholder="Type your additional info here"></textarea>
                            </div>

                            <div class="column xl-12 mb-5">
                                <input type="checkbox" name="wants_reminder" id="wants_reminder">
                                Send me an email reminder
                            </div>

                            <div class="column xl-12" id="reminder_time_container" style="display: none;">
                                <label for="reminder_time">When should we remind you?</label>
                                <select name="reminder_time" id="reminder_time" class="u-fullwidth">
                                    <option value="30">30 minutes before</option>
                                    <option value="60">1 hour before</option>
                                    <option value="120">2 hours before</option>
                                </select>
                            </div>

                            <div class="rform__bottom column xl-12">
                                <input name="submit" id="submit"
                                    class="btn btn--primary btn-wide btn--large u-fullwidth" value="Submit Reservation"
                                    type="submit">
                            </div>

                        </fieldset>
                    </form>

                </div>
            </div>

        </section>

    </article>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const datetimeInput = document.getElementById('datetime');

            // Get current date and time (minimum: now)
            const now = new Date();
            const minDate = now.toISOString().slice(0, 16);

            // Get the date for 2 weeks ahead (maximum: 2 weeks from now)
            const maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 14);
            const maxDateFormatted = maxDate.toISOString().slice(0, 16);

            // Set min and max attributes for datetime input
            datetimeInput.setAttribute('min', minDate);
            datetimeInput.setAttribute('max', maxDateFormatted);

            // Check if the page was refreshed after form submission
            if (localStorage.getItem("scrollToReservation")) {
                document.getElementById("reservation").scrollIntoView({
                    behavior: "smooth"
                });
                localStorage.removeItem("scrollToReservation"); // Remove flag after scrolling
            }

            // Set flag when submitting the form
            document.querySelector(".reservation-form").addEventListener("submit", function() {
                localStorage.setItem("scrollToReservation", "true");
            });
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
