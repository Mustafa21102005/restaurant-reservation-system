@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-secondary text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">Categories</h3>
                <x-create-button route="categories.create">Create Category</x-create-button>
            </div>

            <x-alert-success />
            <x-alert-error />

            <div class="table-responsive">
                @if ($categories->isEmpty())
                    <div class="alert alert-dark text-center">No categories available.
                        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f623/512.gif" width="26">
                    </div>
                @else
                    <table id="categories-table" class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td><a href="{{ route('categories.show', $category->id) }}">{{ $category->name }}</a>
                                    </td>
                                    <td>
                                        <x-edit-button route="categories.edit" :resource-id="$category->id" />
                                        <x-delete-button :resourceId="$category->id" :resourceRoute="route('categories.destroy', $category->id)" />
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
            $('#categories-table').DataTable({
                "language": {
                    "searchPlaceholder": "Search Category"
                }
            });
        });
    </script>
@endsection
