<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'HMS') | Faculty</title>
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
            background: linear-gradient(90deg, rgba(219,39,119,0.2) 0%, rgba(255,255,255,0) 100%);
            border-left: 3px solid #F472B6;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); padding-left: 1.75rem; }
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
        <aside id="sidebar" class="hidden lg:flex w-72 bg-gradient-to-b from-sidebar to-sidebar-light text-white flex-col shrink-0 shadow-2xl z-20">

            <!-- Logo -->
            <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
                <a href="#" class="flex items-center gap-3 group">
                    <img src="{{ asset('chtm-logoo.png') }}" alt="HMS logo" class="h-16 w-auto object-contain drop-shadow-sm group-hover:scale-105 transition-transform duration-300" />
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white block leading-tight">HMS</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest">Faculty Portal</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-6 overflow-y-auto">
                <p class="px-8 mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Main Menu</p>
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="{{ route('faculty.dashboard') }}" class="nav-item {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.dashboard') ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                            <span class="font-semibold text-sm">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.students') }}" class="nav-item {{ request()->routeIs('faculty.students') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.students') ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                            <span class="font-medium text-sm">Manage Students</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.role') }}" class="nav-item {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                            <span class="font-medium text-sm">Manage Teams</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faculty.reports') }}" class="nav-item {{ request()->routeIs('faculty.reports') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.reports') ? 'text-white' : 'text-slate-400 hover:text-white' }}">
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
                            <p class="text-[10px] text-slate-400">Faculty</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-xs font-semibold text-brand-light hover:text-white transition-colors">
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
                    <!-- Search (hidden on Manage Students — search lives in page toolbar) -->
                    @unless(request()->routeIs('faculty.students'))
                    <div class="hidden md:flex items-center bg-slate-100 rounded-xl px-3.5 h-10 gap-2 w-56">
                        <span class="iconify text-slate-400" data-icon="mdi:magnify"></span>
                        <input type="text" placeholder="Search students..." class="bg-transparent text-sm outline-none w-full placeholder-slate-400">
                    </div>
                    @endunless

                    <!-- Notification -->
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-500" data-icon="mdi:bell-outline"></span>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand rounded-full"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button onclick="toggleProfileMenu()" class="flex items-center gap-2.5 hover:bg-slate-100 p-1.5 pr-3 rounded-xl transition cursor-pointer focus:outline-none">
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-9 h-9 rounded-xl border-2 border-white shadow-sm object-cover">
                            <div class="text-right hidden sm:block">
                                <p class="text-xs font-bold text-slate-700">{{ $displayName }}</p>
                                <p class="text-[10px] text-slate-400">Faculty</p>
                            </div>
                            <span class="iconify text-slate-400 text-sm" data-icon="mdi:chevron-down"></span>
                        </button>

                        <div id="profileMenu" class="dropdown-menu absolute right-0 mt-3 w-60 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                            <div class="px-5 py-4 bg-brand-soft border-b border-brand/10 flex items-center gap-3">
                                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-10 h-10 rounded-xl object-cover">
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">{{ $displayName }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $authUser->email }}</p>
                                </div>
                            </div>
                            <div class="py-2">
                                <a href="{{ route('faculty.profile') }}" class="flex items-center gap-3 px-5 py-2.5 text-slate-600 hover:bg-slate-50 transition">
                                    <span class="iconify text-slate-400" data-icon="mdi:account-outline"></span>
                                    <span class="text-sm font-medium">My Profile</span>
                                </a>
                            </div>
                            <div class="border-t border-slate-100 py-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-5 py-2.5 text-red-500 hover:bg-red-50 transition font-medium">
                                        <span class="iconify" data-icon="mdi:logout"></span>
                                        <span class="text-sm">Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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
    <aside id="sidebarMobile" class="sidebar-mobile fixed top-0 left-0 bottom-0 w-72 bg-gradient-to-b from-sidebar to-sidebar-light text-white flex flex-col z-40 lg:hidden shadow-2xl">
        <!-- Same sidebar content -->
        <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('chtm-logoo.png') }}" alt="HMS logo" class="h-16 w-auto object-contain drop-shadow-sm" />
                <div>
                    <span class="text-lg font-bold tracking-tight text-white block leading-tight">HMS</span>
                    <span class="text-[10px] text-slate-400 uppercase tracking-widest">Faculty Portal</span>
                </div>
            </a>
        </div>
        <nav class="flex-1 py-6 overflow-y-auto">
            <ul class="space-y-1 px-4">
                <li><a href="{{ route('faculty.dashboard') }}" class="nav-item {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.dashboard') ? 'text-white' : 'text-slate-400 hover:text-white' }}"><span class="font-semibold text-sm">Dashboard</span></a></li>
                <li><a href="{{ route('faculty.students') }}" class="nav-item {{ request()->routeIs('faculty.students') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.students') ? 'text-white' : 'text-slate-400 hover:text-white' }}"><span class="font-medium text-sm">Manage Students</span></a></li>
                <li><a href="{{ route('faculty.role') }}" class="nav-item {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.role') || request()->routeIs('faculty.activity') ? 'text-white' : 'text-slate-400 hover:text-white' }}"><span class="font-medium text-sm">Manage Teams</span></a></li>
                <li><a href="{{ route('faculty.reports') }}" class="nav-item {{ request()->routeIs('faculty.reports') ? 'active' : '' }} flex items-center px-4 py-3 rounded-xl {{ request()->routeIs('faculty.reports') ? 'text-white' : 'text-slate-400 hover:text-white' }}"><span class="font-medium text-sm">Reports</span></a></li>
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
        // Profile dropdown
        function toggleProfileMenu() {
            document.getElementById('profileMenu').classList.toggle('active');
        }
        window.addEventListener('click', function(e) {
            const menu = document.getElementById('profileMenu');
            const btn = e.target.closest('button[onclick="toggleProfileMenu()"]');
            if (!btn && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });

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