@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
    <div class="container pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-0">Edit Product</h4>
            </div>

            <div>
                <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-floating mb-3">
                        <input name="name" value="{{ old('name', $product->name) }}" type="text" class="form-control"
                            id="floatingInput" placeholder="Product Name" required autofocus>
                        <label for="floatingInput">Product Name</label>
                        <x-error-message field="name" />
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="description" class="form-control" id="floatingDesc" placeholder="Product Description" required
                            style="height: 150px;">{{ old('description', $product->description) }}</textarea>
                        <label for="floatingDesc">Product Description</label>
                        <x-error-message field="description" />
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">$</span>
                        <input name="price" value="{{ old('price', $product->price) }}" type="text"
                            class="form-control" id="floatingPrice" placeholder="Product Price" required
                            aria-label="Amount (to the nearest dollar)">
                        <x-error-message field="price" />
                    </div>

                    <div class="mb-3">
                        <label for="formFile" style="float: left;">Product Image:</label>
                        @if ($product->imageable)
                            @php
                                $imagePath = $product->imageable->path;
                            @endphp

                            @if (Str::startsWith($imagePath, ['http://', 'https://']))
                                <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                    style="width: 400px; height: 300px; object-fit: cover;" class="my-4">
                            @else
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $product->name }}"
                                    style="width: 400px; height: 300px; object-fit: cover;" class="my-4">
                            @endif
                        @else
                            <p class="menublock-item__text">No image available
                                <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f63f/512.gif" width="30"
                                    height="30">
                            </p>
                        @endif
                        <input class="form-control bg-dark" type="file" name="image" id="formFile">
                        <x-error-message field="image" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="category_id" id="floatingCat">
                            <option value="" disabled
                                {{ old('category_id', $product->category_id) ? '' : 'selected' }}>
                                Choose a Category
                            </option>

                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (string) old('category_id', $product->category_id) === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <label for="floatingCat">Category</label>
                        <x-error-message field="category_id" />
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="type" id="floatingSelect">
                            <option value="" disabled {{ old('type', $product->type) ? '' : 'selected' }}>
                                Choose a Type
                            </option>

                            @foreach ($types as $type)
                                <option value="{{ $type }}"
                                    {{ old('type', $product->type) === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>

                        <label for="floatingSelect">Type</label>
                        <x-error-message field="type" />
                    </div>

                    <x-primary-button>Update</x-primary-button>
                    <x-cancel-button :href="route('products.index')" />
                </form>
            </div>

        </div>
    </div>
@endsection
