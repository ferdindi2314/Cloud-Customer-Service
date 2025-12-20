<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Cloud Ticketing'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2d5a96 100%);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            padding: 20px 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar-header h4 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
            margin: 0;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin: 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #fbbf24;
        }

        .sidebar-nav a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #fbbf24;
            font-weight: 500;
        }

        .sidebar-nav i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .sidebar-footer a:hover {
            background-color: rgba(255,255,255,0.15);
        }

        /* MAIN CONTENT */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
        }

        /* TOP HEADER */
        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 500;
        }

        .topbar-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e3a8a;
            margin: 0;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 20px;
            font-size: 14px;
        }

        .user-name {
            font-weight: 500;
            color: #1f2937;
        }

        /* CONTENT AREA */
        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* ALERTS */
        .alert {
            border: none;
            border-radius: 6px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }

            .main-wrapper {
                margin-left: 220px;
            }

            .content {
                padding: 15px;
            }

            .topbar {
                padding: 12px 15px;
            }

            .topbar-title {
                font-size: 18px;
            }
        }

        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .sidebar-footer {
                position: relative;
                border-top: 1px solid rgba(255,255,255,0.1);
            }

            .topbar {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }

            .topbar-title {
                width: 100%;
                text-align: center;
            }

            .topbar-user {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h4>☁️ CloudTicket</h4>
            <p>Support System</p>
        </div>

        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Tiket</span>
                </a>
            </li>
            
            @auth
                @if(auth()->user()->role === 'customer')
                    <li>
                        <a href="{{ route('tickets.create') }}" class="nav-link">
                            <i class="fas fa-plus-circle"></i>
                            <span>Buat Tiket</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->role === 'admin')
                    <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; padding-top: 10px;">
                        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="nav-link">
                            <i class="fas fa-user-shield"></i>
                            <span>Kelola Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index', ['role' => 'agent']) }}" class="nav-link">
                            <i class="fas fa-user-cog"></i>
                            <span>Kelola Operator</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index', ['role' => 'customer']) }}" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Kelola User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="nav-link">
                            <i class="fas fa-list"></i>
                            <span>Kategori</span>
                        </a>
                    </li>
                @endif
            @endauth
        </ul>

        <!-- SIDEBAR FOOTER -->
        <div class="sidebar-footer">
            @auth
                <div style="color: white; font-size: 13px; margin-bottom: 10px; text-align: center;">
                    <div class="fw-semibold">{{ auth()->user()->name }}</div>
                    @php($role = auth()->user()->role)
                    @if($role === 'admin')
                        <span style="font-size: 11px; opacity: 0.8;">👑 Admin</span>
                    @elseif($role === 'agent')
                        <span style="font-size: 11px; opacity: 0.8;">🔧 Agent</span>
                    @else
                        <span style="font-size: 11px; opacity: 0.8;">👤 Customer</span>
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}" class="d-block" id="logoutForm">
                    @csrf
                    <button type="button" class="btn btn-sm btn-light w-100" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-light w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            @endauth
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">
        <!-- TOP HEADER -->
        <div class="topbar">
            <h2 class="topbar-title">@yield('page-title', 'Dashboard')</h2>
            <div class="topbar-user">
                @auth
                    <div class="user-badge">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </div>
                @endauth
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Berhasil!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Apakah Anda ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }

        // Show success alert if logout successful
        @if(session('logout_success'))
            Swal.fire({
                icon: 'success',
                title: 'Anda berhasil logout',
                showConfirmButton: false,
                timer: 1500
            });
        @endif
    </script>
</body>
</html>
