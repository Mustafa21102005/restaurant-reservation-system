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
    <link href="{{ asset('admin/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">

    @yield('styles')
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">

        {{-- loading spinner --}}
        <div id="spinner"
            class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        {{-- left navbar --}}
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="{{ route('dashboard') }}" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">Maillard</h3>
                </a>
                <div class="navbar-nav w-100">
                    <a href="{{ route('home') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}">
                        <i class="fa fa-home"></i> Website</a>
                    <a href="{{ route('dashboard') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}"><i
                            class="fa fa-tachometer-alt"></i> Dashboard</a>
                    <a href="{{ route('categories.index') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'categories.index' ? 'active' : '' }}"><i
                            class="fa fa-th"></i> Categories</a>
                    <a href="{{ route('products.index') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'products.index' ? 'active' : '' }}"><i
                            class="fa fa-utensils"></i> Products</a>
                    <a href="{{ route('reservations.index') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'reservations.index' ? 'active' : '' }}">
                        <i class="fa fa-bookmark"></i> Reservations</a>
                    <a href="{{ route('tables.index') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'tables.index' ? 'active' : '' }}">
                        <i class="fa fa-receipt"></i> Tables</a>
                    <a href="{{ route('customers.index') }}"
                        class="nav-item nav-link {{ Route::currentRouteName() == 'customers.index' ? 'active' : '' }}">
                        <i class="fa fa-user"></i> Customers</a>
                </div>
            </nav>
        </div>

        <div class="content">

            {{-- right navbar --}}
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="#" title="Hide Side-Bar" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">My Profile</a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}" class="dropdown-item"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    Log Out
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            @yield('content')

            {{-- footer --}}
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-6 col-sm-6 text-center text-sm-start">
                            &copy; Maillard, All Rights Reserved.
                        </div>
                        <div class="col-6 text-sm-end">
                            Designed By <a href="https://htmlcodex.com" target="_blank">HTML Codex</a> | Distributed
                            By: <a href="https://themewagon.com" target="_blank">ThemeWagon</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- back to top button --}}
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    {{-- jquery and bootstrap scripts --}}
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    {{-- datatables js --}}
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- js --}}
    <script src="{{ asset('admin/lib/chart/chart.min.js') }}"></script>
    <script src="{{ asset('admin/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('admin/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('admin/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('admin/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ asset('admin/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ asset('admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('admin/js/main.js') }}"></script>

    @yield('scripts')
</body>

</html>
