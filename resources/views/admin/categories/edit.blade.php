@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">Edit Category</h4>
            </div>
            <div>
                <form action="{{ route('categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-floating mb-3">
                        <input name="name" value="{{ old('name', $category->name) }}" type="text" class="form-control"
                            id="name" placeholder="Category Name" required autofocus>
                        <label for="name">Category Name</label>
                        <x-error-message field="name" />
                    </div>

                    <x-primary-button>Update</x-primary-button>
                    <x-cancel-button :href="route('categories.index')" />
                </form>
            </div>
        </div>
    </div>
@endsection
