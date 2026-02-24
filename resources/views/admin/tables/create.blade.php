@extends('layouts.admin')

@section('title', 'Create Table')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Create a Table</h3>
            </div>

            <div>
                <form action="{{ route('tables.store') }}" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <input name="capacity" value="{{ old('capacity') }}" type="number" class="form-control"
                            id="floatingInput" placeholder="Table Capacity" required autofocus>
                        <label for="floatingInput">Table Capacity</label>
                        <x-error-message field="capacity" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="status" id="status">
                            <option value="" disabled {{ old('status') ? '' : 'selected' }}>
                                Choose status
                            </option>

                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="status">Status</label>
                        <x-error-message field="status" />
                    </div>

                    <x-primary-button>Create</x-primary-button>
                    <x-cancel-button :href="route('tables.index')" />
                </form>
            </div>

        </div>
    </div>
@endsection
