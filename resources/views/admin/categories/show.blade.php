@extends('layouts.admin')

@section('title', 'Category Details')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="text-white">Category Details</h4>
                <x-back-button :route="route('categories.index')">Categories</x-back-button>
            </div>

            <div class="row">
                {{-- Left Column - Category Information --}}
                <div class="col-md-6">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                            </tr>
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Right Column - Products --}}
                <div class="col-md-6">
                    <h5 class="text-white">Products</h5>
                    @if ($products->isEmpty())
                        <div class="alert alert-dark text-center">No products found in this category.
                        </div>
                    @else
                        <table id="categories-table" class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                                        </td>
                                        <td>${{ $product->price }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#categories-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Product"
                }
            });
        });
    </script>
@endsection
