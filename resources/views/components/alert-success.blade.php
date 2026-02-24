@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show admin-alert" role="alert" id="success-alert">
        <i class="fa fa-exclamation-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
