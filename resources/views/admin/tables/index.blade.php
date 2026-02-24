@extends('layouts.admin')

@section('title', 'Tables')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Tables</h3>
                <x-create-button route="tables.create">Create Table</x-create-button>
            </div>

            <x-alert-success />
            <x-alert-error />

            <div class="table-responsive">
                @if ($tables->isEmpty())
                    <div class="alert alert-dark text-center">No tables available.
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f928/512.gif" width="26">
                    </div>
                @else
                    <table id="seats-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">ID</th>
                                <th scope="col">Capacity</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tables as $table)
                                <tr>
                                    <td><a href="{{ route('tables.show', $table->id) }}">{{ $table->id }}</a></td>
                                    <td>{{ $table->capacity }}</td>
                                    <td>{{ ucfirst($table->status) }}</td>
                                    <td>
                                        <x-edit-button route="tables.edit" :resource-id="$table->id" />
                                        <x-delete-button :resourceId="$table->id" :resourceRoute="route('tables.destroy', $table->id)" />
                                        @if ($table->status !== 'reserved')
                                            <form action="{{ route('tables.changeStatus', $table->id) }}" method="POST"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit"
                                                    class="btn
                                                    {{ $table->status === 'available' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                    {{ $table->status === 'available' ? 'Mark Unavailable' : 'Mark Available' }}
                                                </button>
                                            </form>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#seats-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Table-Seats"
                }
            });
        });
    </script>
@endsection
