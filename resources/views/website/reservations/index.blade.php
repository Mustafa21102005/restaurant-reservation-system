@extends('layouts.theme')

@section('title', 'My Reservations')

@section('content')
    <article class="s-content">

        <section class="s-pageheader pageheader"
            style="background-image:url('{{ asset('theme/images/pageheader/pageheader-menu-bg-3000.jpg') }}')">
            <div class="row">
                <div class="column xl-12 s-pageheader__content">
                    <h1 class="page-title">
                        My Reservations
                    </h1>
                </div>
            </div>
        </section>

        <div class="container mb-5">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-5">Reservation List</h2>

                    <x-alert-success />
                    <x-alert-error />

                    @if ($reservations->isEmpty())
                        <div class="alert-box alert-box--info">
                            <p class="text-center">No reservations made yet.
                                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f640/512.gif" width="28">
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="mb-5">
                                <thead>
                                    <tr>
                                        <th>Table Number</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reservations as $reservation)
                                        <tr>
                                            <td>{{ $reservation->table->id }}</td>
                                            <td>{{ $reservation->datetime }}</td>
                                            <td>{{ ucfirst($reservation->status) }}</td>
                                            <td>
                                                @if (
                                                    $reservation->status !== 'canceled' &&
                                                        $reservation->status !== 'done' &&
                                                        $reservation->status !== 'seated' &&
                                                        $reservation->status !== 'no_show')
                                                    {{-- Cancel Button --}}
                                                    <button type="button" class="btn btn-danger mt-4"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#cancelModal-{{ $reservation->id }}">
                                                        Cancel
                                                    </button>

                                                    {{-- Edit Button --}}
                                                    <x-edit-button route="website.edit.reservation" :resource-id="$reservation->id"
                                                        class="mt-4" />
                                                @endif <a
                                                    href="{{ route('website.showReservation', $reservation->id) }}"
                                                    class="btn btn-info mt-4">View</a>
                                            </td>
                                        </tr>

                                        {{-- cancel modal --}}
                                        <div class="modal fade" id="cancelModal-{{ $reservation->id }}" tabindex="-1"
                                            aria-labelledby="cancelModalLabel-{{ $reservation->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="cancelModalLabel-{{ $reservation->id }}">
                                                            Cancel Reservation
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to cancel this reservation?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <form
                                                            action="{{ route('website.reservation.cancel', $reservation->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-danger">Yes,
                                                                Cancel</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($reservations->hasPages())
                                <nav class="pgn">
                                    <ul>
                                        {{-- Previous Page Link --}}
                                        @if ($reservations->onFirstPage())
                                            <li><span class="pgn__prev disabled"><svg width="24" height="24">
                                                        <path
                                                            d="M12.707 17.293L8.414 13H18v-2H8.414l4.293-4.293-1.414-1.414L4.586 12l6.707 6.707z" />
                                                    </svg></span></li>
                                        @else
                                            <li><a class="pgn__prev" href="{{ $reservations->previousPageUrl() }}"><svg
                                                        width="24" height="24">
                                                        <path
                                                            d="M12.707 17.293L8.414 13H18v-2H8.414l4.293-4.293-1.414-1.414L4.586 12l6.707 6.707z" />
                                                    </svg></a></li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @foreach ($reservations->links()->elements[0] as $page => $url)
                                            @if ($page == $reservations->currentPage())
                                                <li><span class="pgn__num current">{{ $page }}</span></li>
                                            @else
                                                <li><a class="pgn__num" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach

                                        {{-- Next Page Link --}}
                                        @if ($reservations->hasMorePages())
                                            <li><a class="pgn__next" href="{{ $reservations->nextPageUrl() }}"><svg
                                                        width="24" height="24">
                                                        <path
                                                            d="M11.293 17.293l1.414 1.414L19.414 12l-6.707-6.707-1.414 1.414L15.586 11H6v2h9.586z" />
                                                    </svg></a></li>
                                        @else
                                            <li><span class="pgn__next disabled"><svg width="24" height="24">
                                                        <path
                                                            d="M11.293 17.293l1.414 1.414L19.414 12l-6.707-6.707-1.414 1.414L15.586 11H6v2h9.586z" />
                                                    </svg></span></li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </article>
@endsection
