<!DOCTYPE html>
<html lang="en">

<head>
    <title>Maillard | @yield('title')</title>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    {{-- fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap"
        rel="stylesheet">

    {{-- icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    {{-- datatables css --}}
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    {{-- styles --}}
    <link href="{{ secure_asset('admin/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ secure_asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ secure_asset('admin/css/style.css') }}" rel="stylesheet">

    @yield('styles')
</head>

<body>
    <div id="spinner"
        class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ secure_asset('admin/lib/chart/chart.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/easing/easing.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ secure_asset('admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ secure_asset('admin/js/main.js') }}"></script>

    @yield('scripts')
</body>

</html>
