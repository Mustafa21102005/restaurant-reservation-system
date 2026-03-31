@extends('layouts.admin')

@section('title', 'Product Details')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="text-white">Product Details</h4>
                <x-back-button :route="route('products.index')">Products</x-back-button>
            </div>

            <div class="row">
                {{-- Left Column - Product Image --}}
                <div class="col-md-5 text-center">
                    @if ($product->imageable)
                        @php
                            $imagePath = $product->imageable->path;
                        @endphp

                        @if (Str::startsWith($imagePath, ['http://', 'https://']))
                            <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                style="width: 100%; height: 400px; object-fit: cover;" class="mb-4">
                        @else
                            <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ $product->name }}"
                                style="width: 100%; height: 400px; object-fit: cover;" class="mb-4">
                        @endif
                    @else
                        <p class="menublock-item__text">No image available
                            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f63f/512.gif" width="30"
                                height="30">
                        </p>
                    @endif
                </div>

                {{-- Right Column - Product Information --}}
                <div class="col-md-7">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $product->description }}</td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td>${{ number_format($product->price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>
                                    <a href="{{ route('categories.show', $product->category->id) }}">
                                        {{ $product->category->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td>{{ ucfirst($product->type) }}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection
