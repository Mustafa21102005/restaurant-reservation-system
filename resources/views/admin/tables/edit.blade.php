@extends('layouts.admin')

@section('title', 'Edit Table')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Edit Table</h3>
            </div>
            <div>
                <form action="{{ route('tables.update', $table->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-floating mb-3">
                        <input name="capacity" value="{{ old('capacity', $table->capacity) }}" type="number"
                            class="form-control" id="floatingInput" placeholder="Capacity" required autofocus>
                        <label for="floatingInput">Capacity</label>
                        <x-error-message field="capacity" />
                    </div>

                    <x-primary-button>Update</x-primary-button>
                    <x-cancel-button :href="route('tables.index')" />
                </form>
            </div>
        </div>
    </div>
@endsection
