@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Products</h3>
                <x-create-button route="products.create">Create Product</x-create-button>
            </div>

            <x-alert-success />
            <x-alert-error />

            <div class="table-responsive">
                @if ($products->isEmpty())
                    <div class="alert alert-dark text-center">No products available.
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f616/512.gif" width="26">
                    </div>
                @else
                    <table id="products-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Price</th>
                                <th scope="col">Type</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></td>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>{{ ucfirst($product->type) }}</td>
                                    <td>
                                        <x-edit-button route="products.edit" :resource-id="$product->id" />
                                        <x-delete-button :resourceId="$product->id" :resourceRoute="route('products.destroy', $product->id)" />
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
            $('#products-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Product"
                }
            });
        });
    </script>
@endsection
