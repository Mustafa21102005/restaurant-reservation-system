@if ($errors->any() || session('error'))
    <div class="alert alert-danger alert-dismissible fade show admin-alert" role="alert" id="error-alert">
        <ul class="mb-0">
            @if (session('error'))
                <li>{{ session('error') }}</li>
            @endif
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
