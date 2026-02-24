@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Customers</h3>
            </div>

            <x-alert-success />
            <x-alert-error />

            <div class="table-responsive">
                @if ($customers->isEmpty())
                    <div class="alert alert-dark text-center">No Customer Available.
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f928/512.gif" width="26">
                    </div>
                @else
                    <table id="customer-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer->id) }}">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        @if ($customer->isBanned())
                                            <span class="badge bg-danger">Banned</span>
                                        @elseif ($customer->isTimedOut())
                                            <span class="badge bg-warning text-dark">Timed Out</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            {{-- Timeout Button --}}
                                            @if (!$customer->isBanned() && !$customer->isTimedOut())
                                                <button type="button" class="btn btn-outline-warning me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#timeoutModal-{{ $customer->id }}">
                                                    Time out
                                                </button>
                                            @endif

                                            {{-- Timeout Modal --}}
                                            <div class="modal fade" id="timeoutModal-{{ $customer->id }}" tabindex="-1"
                                                aria-labelledby="timeoutModalLabel-{{ $customer->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content bg-dark text-white">
                                                        <form action="{{ route('customers.timeout', $customer->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('POST')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="timeoutModalLabel-{{ $customer->id }}">
                                                                    Time Out Customer</h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="timeoutReason-{{ $customer->id }}"
                                                                        class="form-label">Reason for timeout</label>
                                                                    <textarea class="form-control bg-secondary text-white" id="timeoutReason-{{ $customer->id }}" name="reason"
                                                                        rows="3" required></textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="timeoutExpiresAt-{{ $customer->id }}"
                                                                        class="form-label">Expires At</label>
                                                                    <input type="datetime-local"
                                                                        class="form-control bg-secondary text-white"
                                                                        id="timeoutExpiresAt-{{ $customer->id }}"
                                                                        name="expires_at" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning">Time
                                                                    Out</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($customer->isTimedOut())
                                                <form action="{{ route('customers.untimeout', $customer->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">Remove
                                                        Timeout</button>
                                                </form>
                                            @endif

                                            @if ($customer->isBanned())
                                                <form action="{{ route('customers.unban', $customer->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">
                                                        Unban
                                                    </button>
                                                </form>
                                            @elseif($customer->isTimedOut())
                                            @else
                                                <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#banModal-{{ $customer->id }}">
                                                    Ban
                                                </button>
                                            @endif

                                            {{-- Ban Modal --}}
                                            <div class="modal fade" id="banModal-{{ $customer->id }}" tabindex="-1"
                                                aria-labelledby="banModalLabel-{{ $customer->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content bg-dark text-white">
                                                        <form action="{{ route('customers.ban', $customer->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('POST')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="banModalLabel-{{ $customer->id }}">
                                                                    Ban Customer</h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="banReason-{{ $customer->id }}"
                                                                        class="form-label">Reason for banning</label>
                                                                    <textarea class="form-control bg-secondary text-white" id="banReason-{{ $customer->id }}" name="reason" rows="3"
                                                                        required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit"
                                                                    class="btn btn-primary">Ban</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#customer-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Customers"
                }
            });
        });
    </script>
@endsection
