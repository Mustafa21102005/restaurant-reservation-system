@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">Create a Product</h4>
            </div>

            <div>
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-floating mb-3">
                        <input name="name" value="{{ old('name') }}" type="text" class="form-control"
                            id="floatingInput" placeholder="Product Name" required autofocus>
                        <label for="floatingInput">Product Name</label>
                        <x-error-message field="name" />
                    </div>

                    <div class="mb-3">
                        <textarea name="description" style="height: 150px;" class="form-control" id="floatingDesc"
                            placeholder="Product Description" required>{{ old('description') }}</textarea>
                        <x-error-message field="description" />
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">$</span>
                        <input name="price" value="{{ old('price') }}" type="text" class="form-control"
                            id="floatingPrice" placeholder="Product Price" required
                            aria-label="Amount (to the nearest dollar)">
                        <x-error-message field="price" />
                    </div>

                    <div class="mb-3">
                        <label for="formFile">Product Image</label>
                        <input class="form-control bg-dark mt-1" type="file" name="image" id="formFile">
                        <x-error-message field="image" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="category_id" id="floatingCat">
                            <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>
                                Choose a Category
                            </option>

                            @forelse ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @empty
                                <option disabled>No categories available</option>
                            @endforelse
                        </select>

                        <label for="floatingCat">Category</label>
                        <x-error-message field="category_id" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="type" id="floatingSelect">
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>
                                Choose a Type
                            </option>

                            @foreach ($types as $type)
                                <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="floatingSelect">Type</label>
                        <x-error-message field="type" />
                    </div>

                    <x-primary-button>Create</x-primary-button>
                    <x-cancel-button :href="route('products.index')" />
                </form>
            </div>

        </div>
    </div>
@endsection
