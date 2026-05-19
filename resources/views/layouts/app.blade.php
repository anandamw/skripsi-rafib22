<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - JJ TOP COSMINDO</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --sidebar-bg: #1a337e;
            --main-bg: #f8f9fa;
            --accent-blue: #0d6efd;
        }

        body {
            background-color: var(--main-bg);
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            color: white;
            width: 260px;
            position: fixed;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            padding: 10px 20px;
            transition: all 0.2s;
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid #fff;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .menu-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            padding: 20px 20px 5px;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
            font-weight: bold;
        }

        /* Main Content */
        .content {
            margin-left: 260px;
            padding: 25px;
            min-height: 100vh;
        }

        .navbar {
            padding: 15px 25px;
        }

        .card {
            border-radius: 12px;
            border: none;
        }

        .card-stat {
            transition: transform 0.2s;
        }

        .card-stat:hover {
            transform: translateY(-5px);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .user-dropdown-img {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border: 2px solid #eee;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: -260px;
                transition: all 0.3s;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .content {
                margin-left: 0;
            }
        }

        @stack('styles')
    </style>
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
       @include('components.sidebar')

        <!-- Main Content -->
        <div class="content w-100">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4 rounded-4 shadow-sm">
                <button class="btn btn-light d-lg-none me-2" id="sidebarCollapse"><i class="fas fa-bars"></i></button>
                <div class="navbar-brand fw-bold text-primary">@yield('page_title', 'Dashboard')</div>
                
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <div class="d-flex align-items-center cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end me-3 d-none d-sm-block">
                                <div class="fw-bold small">{{ Auth::user()->nama }}</div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ Auth::user()->role_label }}</small>
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=1a337e&color=fff" class="rounded-circle user-dropdown-img" alt="User">
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-3">
                            <li><h6 class="dropdown-header">Manajemen Akun</h6></li>
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user-circle me-2 text-muted"></i> Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            @yield('content')
            
            <footer class="mt-5 text-center text-muted pb-3">
                <small>© 2024 PT. JJ Top Cosmindo Sidoarjo • SI Pengelolaan Persediaan EOQ & ROP</small>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            $('#sidebarCollapse').on('click', function() {
                $('.sidebar').toggleClass('active');
            });
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: '{{ session('error') }}',
                confirmButtonColor: '#1a337e'
            });
        @endif
    </script>
    
    @stack('scripts')
</body>

</html>