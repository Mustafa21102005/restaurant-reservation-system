@extends('layouts.theme')

@section('title', 'Edit Reservation')
@section('content')
    <section class="s-pageheader pageheader mb-5"
        style="background-image: url('{{ asset('theme/images/pageheader/pageheader-generic-bg-3000.jpg') }}')">
        <div class="row">
            <div class="column xl-12 s-pageheader__content">
                <h1 class="page-title">
                    Reservation Details
                </h1>
            </div>
        </div>
    </section>

    <div class="row width-narrower content-block mt-5" id="reservation">
        <div class="card shadow-lg rounded-3 border-0">
            <h4 class="mb-4 border-bottom pb-3 text-center">Reservation #{{ $reservation->id }}</h4>

            <div class="row mb-4 text-center">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong class="text-muted">Table Number:</strong>
                        <p class="h6 mt-1">{{ $reservation->table->id }}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Date & Time:</strong>
                        <p class="h6 mt-1">{{ $reservation->datetime }}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Status:</strong>
                        <p class="h6 mt-1">
                            @if ($reservation->status == 'canceled')
                                <span class="badge bg-danger">{{ ucfirst($reservation->status) }}</span>
                            @elseif($reservation->status == 'ongoing')
                                <span class="badge bg-success">{{ ucfirst($reservation->status) }}</span>
                            @elseif($reservation->status == 'seated')
                                <span class="badge bg-info">{{ ucfirst($reservation->status) }}</span>
                            @elseif($reservation->status == 'no_show')
                                <span class="badge bg-warning">{{ ucfirst($reservation->status) }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong class="text-muted">Number of Guests:</strong>
                        <p class="h6 mt-1">{{ $reservation->table->capacity }}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Additional Info:</strong>
                        <p class="h6 mt-1"
                            style="max-height: 100px; overflow-y: auto; margin-right: 30px; padding-right: 15px;">
                            {{ $reservation->info ?? 'No Additional Info Provided.' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-muted">Verification Code:</strong>
                        <p class="h6 mt-1">
                            <span id="code" class="text-danger fw-bold" onclick="toggleCode()"
                                style="cursor: pointer;">
                                ••••••••••
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center my-4">
                <a href="{{ route('website.myReservation') }}" class="btn btn-primary btn-lg px-5">Back to My
                    Reservations</a>
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
