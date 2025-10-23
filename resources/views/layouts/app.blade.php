<!DOCTYPE html>
<?php
/** @var \App\Models\User|null $authUser */
// Help static analyzers understand the type of auth()->user()
?>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ravon Bakers') }} - Restaurant Management System</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1050;
            overflow-y: auto;
        }

        .sidebar-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .sidebar-logo:hover {
            border-color: rgba(255, 255, 255, 0.6);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .sidebar.collapsed .sidebar-logo {
            width: 40px;
            height: 40px;
        }

        .sidebar.collapsed .sidebar-logo:hover {
            transform: scale(1.1);
        }

        /* Fallback styling if logo fails to load */
        .sidebar-logo-fallback {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-logo-fallback {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 15px 10px;
        }

        .sidebar-title {
            font-size: 22px;
            font-weight: bold;
            color: white;
            margin: 12px 0 5px 0;
            letter-spacing: 1.5px;
            text-align: center;
            display: block;
        }

        .sidebar-subtitle {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            text-align: center;
            display: block;
        }

        .sidebar.collapsed .sidebar-title,
        .sidebar.collapsed .sidebar-subtitle {
            display: none;
        }

        .sidebar.collapsed {
            width: 70px !important;
            min-width: 70px !important;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 12px 8px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .sidebar.collapsed h4,
        .sidebar.collapsed small {
            display: none;
        }

        .content-wrapper {
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
            margin-left: 250px;
            /* Default sidebar width */
            display: flex;
            flex-direction: column;
        }

        .content-wrapper.sidebar-collapsed {
            margin-left: 70px !important;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Reports submenu styles */
        .sidebar .nav-link .bi-chevron-down {
            transition: transform 0.3s ease;
            font-size: 12px;
        }

        .sidebar .nav-link[aria-expanded="true"] .bi-chevron-down {
            transform: rotate(180deg);
        }

        .sidebar #reportsSubmenu {
            transition: all 0.3s ease;
        }

        .sidebar #reportsSubmenu .nav-link {
            padding-left: 45px;
            font-size: 14px;
            margin: 1px 0;
        }

        .sidebar #reportsSubmenu .nav-link i {
            font-size: 14px;
            margin-right: 8px;
        }

        /* Admin Reports submenu styles */
        .sidebar #adminReportsSubmenu {
            transition: all 0.3s ease;
        }

        .sidebar #adminReportsSubmenu .nav-link {
            padding-left: 45px;
            font-size: 14px;
            margin: 1px 0;
        }

        .sidebar #adminReportsSubmenu .nav-link i {
            font-size: 14px;
            margin-right: 8px;
        }

        /* Submenu container for custom toggle */
        .submenu-container {
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            max-height: 0;
            opacity: 0;
        }

        .submenu-container.show {
            max-height: 500px;
            opacity: 1;
        }

        .navbar-brand {
            font-weight: 600;
            color: #667eea !important;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .pos-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .pos-btn:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            transform: translateY(-2px);
            color: white;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .table th {
            background: #667eea;
            color: white;
            border: none;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        #sidebarToggle {
            border: 1px solid #dee2e6;
            color: #495057;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }

        #sidebarToggle:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                will-change: transform;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .content-wrapper,
            .content-wrapper.sidebar-collapsed {
                margin-left: 0 !important;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }

            #sidebarToggle {
                display: none;
            }

            .navbar-toggler {
                display: block !important;
                border: none;
                padding: 8px;
                background: rgba(255, 255, 255, 0.1);
                color: #495057;
                border-radius: 6px;
            }

            .navbar-toggler:focus {
                box-shadow: none;
            }
        }

        @media (min-width: 769px) {
            .navbar-toggler {
                display: none !important;
            }

            .sidebar-overlay {
                display: none !important;
            }
        }

        /* Footer Styles */
        .main-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            padding: 20px 0;
            margin-top: auto;
            position: relative;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .footer-content {
            text-align: center;
        }

        .footer-content .copyright {
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .footer-content .designer {
            font-size: 12px;
            color: #bdc3c7;
            font-style: italic;
        }

        .footer-content .designer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-content .designer a:hover {
            color: #5dade2;
            text-decoration: underline;
        }

        /* Ensure body has flex layout for sticky footer */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .content-wrapper .container-fluid {
            flex: 1;
            padding-bottom: 20px;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="position-sticky pt-3">
            <div class="sidebar-header">
                @if(auth()->user()->isSupervisor())
                <a href="{{ route('supervisor.dashboard') }}" style="text-decoration: none;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Bakers Logo" class="sidebar-logo">
                </a>
                @else
                @if(auth()->user()->role !== 'admin')
                <a href="{{ route('dashboard') }}" style="text-decoration: none;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Bakers Logo" class="sidebar-logo">
                </a>
                @else
                <!-- Admins link logo to user management -->
                <a href="{{ route('users.index') }}" style="text-decoration: none;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Bakers Logo" class="sidebar-logo">
                </a>
                @endif
                @endif
                <h4 class="sidebar-title">RAVON</h4>
                <p class="sidebar-subtitle">Bakers & Restaurant</p>
            </div>

            <ul class="nav flex-column px-3">
                @if(auth()->user()->isSupervisor())
                <!-- Supervisor Navigation -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}"
                        href="{{ route('supervisor.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('supervisor.add-inventory') ? 'active' : '' }}"
                        href="{{ route('supervisor.add-inventory') }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add Production</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('supervisor.add-wastage') ? 'active' : '' }}"
                        href="{{ route('supervisor.add-wastage') }}">
                        <i class="bi bi-trash"></i>
                        <span>Production Wastage</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('supervisor.stock-transfer.create') ? 'active' : '' }}"
                        href="{{ route('supervisor.stock-transfer.create') }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>Create Transfer</span>
                    </a>
                </li>

                <!-- Reports dropdown -->
                @php
                $reportsActive = request()->routeIs('supervisor.wastage-view') || request()->routeIs('stock-transfer.transfers') || request()->routeIs('supervisor.inventory-history') || request()->routeIs('supervisor.productions.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $reportsActive ? 'active' : '' }}"
                        href="javascript:void(0);"
                        onclick="toggleReportsMenu(event)"
                        id="reportsToggle"
                        aria-expanded="{{ $reportsActive ? 'true' : 'false' }}">
                        <div><i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span></div>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="submenu-container {{ $reportsActive ? 'show' : '' }}" id="reportsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('supervisor.wastage-view') ? 'active' : '' }}" href="{{ route('supervisor.wastage-view') }}">
                                    <i class="bi bi-eye"></i>
                                    <span>View Wastage</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('stock-transfer.transfers') ? 'active' : '' }}" href="{{ route('stock-transfer.transfers') }}">
                                    <i class="bi bi-list-check"></i>
                                    <span>View Transfer</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('supervisor.inventory-history') ? 'active' : '' }}" href="{{ route('supervisor.inventory-history') }}">
                                    <i class="bi bi-boxes"></i>
                                    <span>Stock Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('supervisor.productions.*') ? 'active' : '' }}" href="{{ route('supervisor.productions.index') }}">
                                    <i class="bi bi-clipboard-data"></i>
                                    <span>View Production</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @else
                <!-- Regular Navigation for Admin and Staff -->
                @if(auth()->user()->role !== 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endif

                @can('manage-users')


                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                        href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i>
                        <span>User Management</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}"
                        href="{{ route('branches.index') }}">
                        <i class="bi bi-building"></i>
                        <span>Branch Management</span>
                    </a>
                </li>
                @endcan

                <!-- Categories and Items - Only for Admin -->
                @if(auth()->user()->role === 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                        href="{{ route('categories.index') }}">
                        <i class="bi bi-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}"
                        href="{{ route('items.index') }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Item Management</span>
                    </a>
                </li>

                <!-- Admin Reports dropdown -->
                @php
                $adminReportsActive = request()->routeIs('sales-report.*') || request()->routeIs('reports.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ $adminReportsActive ? 'active' : '' }}"
                        href="javascript:void(0);"
                        onclick="toggleAdminReportsMenu(event)"
                        id="adminReportsToggle"
                        aria-expanded="{{ $adminReportsActive ? 'true' : 'false' }}">
                        <div><i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span></div>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <div class="submenu-container {{ $adminReportsActive ? 'show' : '' }}" id="adminReportsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('sales-report.*') ? 'active' : '' }}" href="{{ route('sales-report.index') }}">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Daily Sales Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.stock-report') ? 'active' : '' }}" href="{{ route('reports.stock-report') }}">
                                    <i class="bi bi-boxes"></i>
                                    <span>Stock Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.item-sales') ? 'active' : '' }}" href="{{ route('reports.item-sales') }}">
                                    <i class="bi bi-graph-up"></i>
                                    <span>Item Sales</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif

                <!-- Stock Transfer (For Staff) -->
                @if(auth()->user()->role === 'staff' && auth()->user()->branch_id)
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('stock-transfer.transfers') ? 'active' : '' }}"
                        href="{{ route('stock-transfer.transfers') }}">
                        <i class="bi bi-inbox"></i>
                        <span>Stock Transfers</span>
                    </a>
                </li>
                @endif

                <!-- Branch Inventory (For Staff) -->
                @if(auth()->user()->role === 'staff')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('staff.branch-inventory') ? 'active' : '' }}"
                        href="{{ route('staff.branch-inventory') }}">
                        <i class="bi bi-building"></i>
                        <span>Branch Stock</span>
                    </a>
                </li>
                @endif
                @endif
            </ul>
        </div>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main content -->
    <main class="content-wrapper">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
            <div class="container-fluid">
                <!-- Sidebar Toggle Button (Desktop) -->
                <button class="btn btn-outline-secondary me-3" type="button" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Mobile Hamburger Menu -->
                <button class="navbar-toggler d-md-none" type="button" id="mobileMenuToggle">
                    <span class="navbar-toggler-icon"></span>
                </button>

                @if(auth()->user()->isSupervisor())
                <a class="navbar-brand d-flex align-items-center" href="{{ route('supervisor.dashboard') }}">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Logo" style="width: 32px; height: 32px; margin-right: 10px; border-radius: 50%;">
                    <span>Ravon Restaurant</span>
                </a>
                @else
                @if(auth()->user()->role === 'admin')
                <a class="navbar-brand d-flex align-items-center" href="{{ route('users.index') }}">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Logo" style="width: 32px; height: 32px; margin-right: 10px; border-radius: 50%;">
                    <span>Ravon Restaurant</span>
                </a>
                @else
                <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Ravon Logo" style="width: 32px; height: 32px; margin-right: 10px; border-radius: 50%;">
                    <span>Ravon Restaurant</span>
                </a>
                @endif
                @endif

                <div class="d-flex align-items-center">
                    <!-- POS Button (visible to staff only, not supervisors) -->
                    @if(auth()->check() && auth()->user()->isStaff())
                    <a href="{{ route('pos.index') }}" class="btn pos-btn me-3">
                        <i class="bi bi-calculator"></i>
                        POS System
                    </a>
                    @endif

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content', $slot ?? '')
        </div>

        <!-- Footer -->
        <footer class="main-footer" id="mainFooter">
            <div class="container-fluid">
                <div class="footer-content">
                    <div class="copyright">
                        Copyright © Ravon Bakers All Rights Reserved
                    </div>
                    <div class="designer">
                        Designed by <a href="#" target="_blank">SKM Labs</a>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const contentWrapper = document.querySelector('.content-wrapper');
            const overlay = document.getElementById('sidebarOverlay');

            let isAnimating = false;

            // Function to close sidebar
            function closeSidebar() {
                if (isAnimating) return;
                isAnimating = true;

                sidebar.classList.remove('show');
                overlay.classList.remove('show');

                setTimeout(() => {
                    document.body.classList.remove('sidebar-open');
                    isAnimating = false;
                }, 300);
            }

            // Function to open sidebar
            function openSidebar() {
                if (isAnimating) return;
                isAnimating = true;

                document.body.classList.add('sidebar-open');

                // Force reflow
                sidebar.offsetHeight;

                requestAnimationFrame(() => {
                    sidebar.classList.add('show');
                    overlay.classList.add('show');

                    setTimeout(() => {
                        isAnimating = false;
                    }, 300);
                });
            }

            // Desktop sidebar toggle (collapse/expand)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    // Only collapse on desktop
                    if (window.innerWidth > 768) {
                        sidebar.classList.toggle('collapsed');
                        contentWrapper.classList.toggle('sidebar-collapsed');

                        // Change icon based on state
                        const icon = sidebarToggle.querySelector('i');
                        if (sidebar.classList.contains('collapsed')) {
                            icon.classList.remove('bi-list');
                            icon.classList.add('bi-chevron-right');
                        } else {
                            icon.classList.remove('bi-chevron-right');
                            icon.classList.add('bi-list');
                        }
                    }
                });
            }

            // Mobile menu toggle
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();

                    if (sidebar.classList.contains('show')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            // Overlay click - close sidebar
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeSidebar();
                });
            }

            // Close sidebar when clicking nav links on mobile
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(() => {
                            closeSidebar();
                        }, 150);
                    }
                });
            });

            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                        document.body.classList.remove('sidebar-open');
                        sidebar.classList.remove('collapsed');
                        contentWrapper.classList.remove('sidebar-collapsed');
                    }
                }, 100);
            });

            // Prevent clicks inside sidebar from closing it
            sidebar.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Close sidebar when nav link is clicked on mobile
            const mobileNavLinks = sidebar.querySelectorAll('.nav-link');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeMobileSidebar();
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileSidebar();
                    document.body.style.overflow = '';
                }
            });

            // Keep 'Reports' submenu stable: open on related routes and avoid accidental collapse
            // Simple toggle function that won't auto-close
            window.toggleReportsMenu = function(event) {
                event.preventDefault();
                event.stopPropagation();

                const toggle = document.getElementById('reportsToggle');
                const submenu = document.getElementById('reportsSubmenu');

                if (!toggle || !submenu) return;

                const isExpanded = submenu.classList.contains('show');

                if (isExpanded) {
                    submenu.classList.remove('show');
                    submenu.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                } else {
                    submenu.classList.add('show');
                    submenu.style.display = 'block';
                    toggle.setAttribute('aria-expanded', 'true');
                }
            };

            // Admin Reports submenu toggle (similar functionality)
            window.toggleAdminReportsMenu = function(event) {
                event.preventDefault();
                event.stopPropagation();

                const toggle = document.getElementById('adminReportsToggle');
                const submenu = document.getElementById('adminReportsSubmenu');

                if (!toggle || !submenu) return;

                const isExpanded = submenu.classList.contains('show');

                if (isExpanded) {
                    submenu.classList.remove('show');
                    submenu.style.display = 'none';
                    toggle.setAttribute('aria-expanded', 'false');
                } else {
                    submenu.classList.add('show');
                    submenu.style.display = 'block';
                    toggle.setAttribute('aria-expanded', 'true');
                }
            };

            // Ensure submenu links don't trigger any parent toggles
            document.addEventListener('DOMContentLoaded', function() {
                const supervisorSubmenuLinks = document.querySelectorAll('#reportsSubmenu a');
                supervisorSubmenuLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                });

                const adminSubmenuLinks = document.querySelectorAll('#adminReportsSubmenu a');
                adminSubmenuLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>