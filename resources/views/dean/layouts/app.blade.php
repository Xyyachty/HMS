<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hotel Management System | Dean Panel</title>
    <link rel="icon" type="image/png" href="{{ asset('new_logo_in_chtm....png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        // Same four colours as the Student portal: wine, gold, cream, ink.
        // Every Tailwind stock family below is remapped to a tint/shade of one of
        // those four rather than left at its default hue, so utility classes used
        // all over the Dean pages resolve to the same four-colour system without
        // editing every call site.
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'sans-serif'] },
                    colors: {
                        brand: '#7B1730',
                        'brand-light': '#C9A45C',
                        'brand-dark': '#4A0D1C',
                        'brand-soft': '#FBEEE9',
                        'rose-accent': '#C4425E',
                        'plum-accent': '#C9A45C',
                        'plum-soft': '#FBEEE9',
                        'amber-soft': '#FBF3E0',
                        surface: '#FDF6F3',
                        'sidebar': '#4A0D1C',
                        'sidebar-light': '#2A1118',

                        // Neutral text/border scale, warmed toward ink instead of
                        // Tailwind's default cool blue-gray.
                        slate: {
                            50: '#FAF6F5', 100: '#F2E9E7', 200: '#E4D3CF', 300: '#C9AFAA',
                            400: '#7A6068', 500: '#6B4A54', 600: '#5A3941', 700: '#47262D',
                            800: '#341620', 900: '#2A1118',
                        },

                        // Danger / overdue / "cool" accents all fold into the wine family.
                        red:    { 50: '#FBEAEE', 100: '#F6D5DC', 200: '#ECAFBE', 400: '#C4425E', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },
                        rose:   { 50: '#FBEAEE', 100: '#F6D5DC', 200: '#ECAFBE', 300: '#DE8299', 400: '#C4425E', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },
                        pink:   { 50: '#FBEAEE', 100: '#F6D5DC', 200: '#ECAFBE', 300: '#DE8299', 400: '#C4425E', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },
                        violet: { 50: '#FBEAEE', 100: '#F6D5DC', 500: '#9E1B3C', 700: '#5E1024' },
                        indigo: { 50: '#FBEAEE', 100: '#F6D5DC', 500: '#9E1B3C', 700: '#5E1024' },
                        blue:   { 50: '#FBEAEE', 100: '#F6D5DC', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },
                        sky:    { 50: '#FBEAEE', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },
                        teal:   { 50: '#FBEAEE', 500: '#9E1B3C', 600: '#7B1730', 700: '#5E1024' },

                        // Success / pending / warning accents fold into the gold family.
                        emerald: { 50: '#FBF3E0', 100: '#F2E3BE', 200: '#E9D3A0', 400: '#D9B86A', 500: '#C9A45C', 600: '#B8873C', 700: '#96692C', 800: '#7A5322' },
                        amber:   { 50: '#FBF3E0', 100: '#F2E3BE', 200: '#E9D3A0', 400: '#D9B86A', 500: '#C9A45C', 600: '#B8873C', 700: '#96692C', 800: '#7A5322' },
                        green:   { 50: '#FBF3E0', 100: '#F2E3BE', 200: '#E9D3A0', 400: '#D9B86A', 500: '#C9A45C', 600: '#B8873C', 700: '#96692C' },
                        orange:  { 50: '#FBF3E0', 500: '#C9A45C', 600: '#B8873C', 700: '#96692C' },
                    }
                }
            }
        }
    </script>
    <style>

        ::selection { background: #7B1730; color: #fff; }
        body { font-family: 'Manrope', sans-serif; }
        /* SweetAlert defaults to z-index 1060 and the dean modals sit at 9999, so a
           confirm fired from inside a modal opened behind it. */
        .swal2-container { z-index: 20000 !important; }
        /* Flat wine, not a gradient: buttons wear this class, and a button reads
           as one solid colour. The name stays so every call site keeps working. */
        .brand-gradient { background: #7B1730; }
        .app-sidebar {
            background: linear-gradient(180deg, #7B1730 0%, #5E1024 38%
        , #4A0D1C 72%, #2A1118 100%);
        }

        ::-webkit-scrollbar { width: 0.3125rem; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 0.625rem; }

        .glass-header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .nav-item { position: relative; transition: all 0.3s ease; }
        .nav-item.active {
            background: rgba(255,255,255,0.22);
            border-left: 3px solid #fff;
            box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.25);
        }
        .nav-item:hover { background: rgba(255,255,255,0.12); padding-left: 1.75rem; }
        .nav-item.active:hover { padding-left: 1.75rem; }

        .dropdown-menu {
            transform: translateY(10px); opacity: 0; visibility: hidden;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .dropdown-menu.active { transform: translateY(0); opacity: 1; visibility: visible; }

        .sidebar-mobile {
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-mobile.open { transform: translateX(0); }
    </style>
    @stack('styles')
    @include('partials.gray-scheme-styles')
    @include('partials.status-badge-styles')
    @include('partials.ink-all-styles')
    @include('partials.portal-scale')
    @include('partials.select-caret-styles')
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- ==================== SIDEBAR ==================== -->
        <aside id="sidebar" class="app-sidebar hidden lg:flex w-72 text-white flex-col shrink-0 shadow-2xl z-20">

            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
                <a href="#" class="flex items-center gap-3 group">
                    <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" class="h-16 w-auto object-contain drop-shadow-sm group-hover:scale-105 transition-transform duration-300" />
                    <div>
                        <span class="text-sm font-bold tracking-tight text-white block leading-tight">Hotel Management System</span>
                        <span class="text-[0.625rem] text-white uppercase tracking-widest">Dean Portal</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-6 overflow-y-auto">
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="{{ route('dean.dashboard') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white @yield('dashboard_active')">
                            <span class="font-medium text-sm">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.users') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white @yield('users_active')">
                            <span class="font-medium text-sm">Manage User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.faculties') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white @yield('faculties_active')">
                            <span class="font-medium text-sm">Teams Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.reports') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white @yield('reports_active')">
                            <span class="font-medium text-sm">Reports</span>
                        </a>
                    </li>
                </ul>


            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-white/10 shrink-0">
                @php
                    $deanUser = auth()->user();
                    $deanDisplayName = trim(implode(' ', array_filter([
                        $deanUser?->first_name,
                        $deanUser?->last_name,
                    ]))) ?: ($deanUser?->name ?? 'Dean');
                    $deanEmail = $deanUser?->email ?? 'dean@hms.edu';
                @endphp
                @include('partials.sidebar-profile-card', [
                    'user'        => $deanUser,
                    'name'        => $deanDisplayName,
                    'role'        => 'Dean Admin',
                    'profileHref' => route('dean.profile'),
                ])
            </div>
        </aside>


        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Header -->
            <header class="glass-header h-20 flex items-center justify-between px-6 md:px-8 sticky top-0 z-10 shrink-0">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-600" data-icon="mdi:menu"></span>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        {{-- A greeting is worth reading once, on the page you land on.
                             Repeated under every heading it says nothing, so each page
                             says what it is instead and only the dashboard says hello. --}}
                        <p class="text-xs text-slate-400 font-light">@yield('page_subtitle', 'Welcome back, ' . $deanDisplayName)</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:gap-5">
                    @include('partials.datetime-clock')
                    @include('partials.notification-bell')
                </div>
            </header>

            <!-- Page Content Area -->
            <main class="gray-accents flex-1 overflow-y-auto p-4 md:p-6" style="background-color:#dadada">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    <aside id="sidebarMobile" class="app-sidebar sidebar-mobile fixed top-0 left-0 bottom-0 w-72 text-white flex flex-col z-40 lg:hidden shadow-2xl">
        <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('new_logo_in_chtm....png') }}" alt="College of Hospitality and Tourism Management logo" class="h-16 w-auto object-contain drop-shadow-sm" />
                <div>
                    <span class="text-sm font-bold tracking-tight text-white block leading-tight">Hotel Management System</span>
                    <span class="text-[0.625rem] text-white uppercase tracking-widest">Dean Portal</span>
                </div>
            </a>
        </div>
        <nav class="flex-1 py-6 overflow-y-auto">
            <ul class="space-y-1 px-4">
                <li><a href="{{ route('dean.dashboard') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Dashboard</span></a></li>
                <li><a href="{{ route('dean.users') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Manage User</span></a></li>
                <li><a href="{{ route('dean.faculties') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Teams Overview</span></a></li>
                <li><a href="{{ route('dean.reports') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Reports</span></a></li>
                <li><a href="{{ route('dean.profile') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">My Profile</span></a></li>
            </ul>
        </nav>
    </aside>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.swal-theme')
    <script src="{{ asset('js/hms-swal-theme.js') }}?v={{ filemtime(public_path('js/hms-swal-theme.js')) }}"></script>
    @if (session('welcome'))
        <script>
            window.addEventListener('load', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome',
                    text: '{{ session('welcome.name') }} to {{ ucfirst(session('welcome.role')) }}',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    iconColor: '#C9A45C',
                    width: '22rem'
                });
            });
        </script>
    @endif
    <script>
        function toggleSidebar() {
            const overlay = document.getElementById('sidebarOverlay');
            const sidebar = document.getElementById('sidebarMobile');
            overlay.classList.toggle('hidden');
            sidebar.classList.toggle('open');
        }
    </script>
    @include('partials.ph-phone-input')
    @stack('scripts')
@include('partials.logout-confirm')
@include('partials.session-guard')
</body>
</html>
