<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Hotel Management System') | Faculty</title>
    <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        ::selection { background: #DB2777; color: #fff; }
        body { font-family: 'Manrope', sans-serif; }

        .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }
        .brand-gradient-alt { background: linear-gradient(135deg, #FB7185, #DB2777, #A855F7); }
        .gradient-text {
            background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .app-sidebar {
            background: linear-gradient(180deg, #DB2777 0%, #BE185D 38%, #9D174D 72%, #500724 100%);
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

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
        .dropdown-menu.active {
            transform: translateY(0); opacity: 1; visibility: visible;
        }

        .stat-card { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1); }

        .progress-bar { transition: width 1s cubic-bezier(0.16, 1, 0.3, 1); }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer-bar {
            background: linear-gradient(90deg, #F472B6, #DB2777, #FB7185, #DB2777);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }

        @keyframes fadeUp {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease-out forwards; }
        .fade-up-1 { animation: fadeUp 0.5s ease-out 0.1s forwards; opacity: 0; }
        .fade-up-2 { animation: fadeUp 0.5s ease-out 0.2s forwards; opacity: 0; }
        .fade-up-3 { animation: fadeUp 0.5s ease-out 0.3s forwards; opacity: 0; }
        .fade-up-4 { animation: fadeUp 0.5s ease-out 0.4s forwards; opacity: 0; }

        .dept-tag { transition: all 0.2s ease; }
        .dept-tag:hover { transform: scale(1.05); }

        /* Mobile sidebar */
        .sidebar-overlay { transition: opacity 0.3s ease; }
        .sidebar-mobile {
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .sidebar-mobile.open { transform: translateX(0); }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 antialiased">

    <div class="flex h-screen overflow-hidden">

        <!-- ==================== SIDEBAR ==================== -->
        <aside id="sidebar" class="app-sidebar hidden lg:flex w-72 text-white flex-col shrink-0 shadow-2xl z-20">

            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
                <a href="#" class="flex items-center gap-3 group">
                    <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-16 w-auto object-contain drop-shadow-sm group-hover:scale-105 transition-transform duration-300" />
                    <div>
                        <span class="text-sm font-bold tracking-tight text-white block leading-tight">Hotel Management System</span>
                        <span class="text-[10px] text-white uppercase tracking-widest">Faculty Portal</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-6 overflow-y-auto">
                <p class="px-8 mb-3 text-[10px] font-bold uppercase tracking-widest text-white">Main Menu</p>
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="{{ route('faculty.dashboard') }}" class="nav-item {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white">
                            <span class="font-semibold text-sm">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.students') }}" class="nav-item {{ request()->routeIs('faculty.students') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white">
                            <span class="font-medium text-sm">Manage Students</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.role') }}" class="nav-item {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white">
                            <span class="font-medium text-sm">Manage Teams</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.reports') }}" class="nav-item {{ request()->routeIs('faculty.reports') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white">
                            <span class="font-medium text-sm">Reports</span>
                        </a>
                    </li>
                </ul>


            </nav>

            <!-- Sidebar Footer -->
            @php
                $authUser = auth()->user();
                $displayName = trim(implode(' ', array_filter([
                    $authUser->first_name ?? null,
                    $authUser->last_name ?? null,
                ]))) ?: ($authUser->name ?? 'Faculty');
                $avatarUrl = $authUser?->avatar_url
                    ?? ('https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=DB2777&color=fff&size=40&font-size=0.4');
            @endphp
            <div class="p-4 border-t border-white/10 shrink-0">
                <div class="bg-white/5 rounded-2xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-10 h-10 rounded-xl border-2 border-white/20 object-cover">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ $displayName }}</p>
                            <p class="text-[10px] text-white">Faculty</p>
                        </div>
                    </div>
                    <a href="{{ route('faculty.profile') }}" class="mb-2 flex items-center gap-2 text-xs font-semibold text-white hover:text-white/80 transition-colors">
                        <span class="iconify" data-icon="mdi:account-outline"></span>
                        My Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-xs font-semibold text-white hover:text-white/80 transition-colors">
                            <span class="iconify" data-icon="mdi:logout"></span>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </aside>


        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Header -->
            <header class="glass-header h-20 flex items-center justify-between px-6 md:px-8 sticky top-0 z-10 shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Mobile menu toggle -->
                    <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-600" data-icon="mdi:menu"></span>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        <p class="text-xs text-slate-400 font-light">Welcome back, {{ $displayName }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:gap-5">
                    <!-- Notification -->
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-500" data-icon="mdi:bell-outline"></span>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand rounded-full"></span>
                    </button>
                </div>
            </header>


            <!-- ==================== PAGE CONTENT ==================== -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @yield('content')
            </main>
        </div>
    </div>



    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    <aside id="sidebarMobile" class="app-sidebar sidebar-mobile fixed top-0 left-0 bottom-0 w-72 text-white flex flex-col z-40 lg:hidden shadow-2xl">
        <!-- Same sidebar content -->
        <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System logo" class="h-16 w-auto object-contain drop-shadow-sm" />
                <div>
                    <span class="text-sm font-bold tracking-tight text-white block leading-tight">Hotel Management System</span>
                    <span class="text-[10px] text-white uppercase tracking-widest">Faculty Portal</span>
                </div>
            </a>
        </div>
        <nav class="flex-1 py-6 overflow-y-auto">
            <ul class="space-y-1 px-4">
                <li><a href="{{ route('faculty.dashboard') }}" class="nav-item {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white"><span class="font-semibold text-sm">Dashboard</span></a></li>
                <li><a href="{{ route('faculty.students') }}" class="nav-item {{ request()->routeIs('faculty.students') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Manage Students</span></a></li>
                <li><a href="{{ route('faculty.role') }}" class="nav-item {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Manage Teams</span></a></li>
                <li><a href="{{ route('faculty.reports') }}" class="nav-item {{ request()->routeIs('faculty.reports') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl text-white"><span class="font-medium text-sm">Reports</span></a></li>
            </ul>
        </nav>
    </aside>


    <!-- ==================== SCRIPTS ==================== -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    iconColor: '#DB2777',
                    width: '22rem'
                });
            });
        </script>
    @endif
    <script>
        // Mobile sidebar
        function toggleSidebar() {
            const overlay = document.getElementById('sidebarOverlay');
            const sidebar = document.getElementById('sidebarMobile');
            overlay.classList.toggle('hidden');
            sidebar.classList.toggle('open');
        }

        // Animate progress bars on load
        window.addEventListener('load', function() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => { bar.style.width = width; }, 300);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>