<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS | Dean Panel</title>
    <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Manrope', 'sans-serif'] },
                    colors: {
                        brand: '#DB2777',
                        'brand-light': '#F472B6',
                        'brand-dark': '#9D174D',
                        'brand-soft': '#FDF2F8',
                        'rose-accent': '#FB7185',
                        'plum-accent': '#A855F7',
                        'plum-soft': '#FAF5FF',
                        'amber-soft': '#FFFBEB',
                        'sidebar': '#0F172A',
                        'sidebar-light': '#1E293B',
                    }
                }
            }
        }
    </script>
    <style>
        ::selection { background: #DB2777; color: #fff; }
        body { font-family: 'Manrope', sans-serif); }
        .brand-gradient { background: linear-gradient(135deg, #F472B6, #DB2777, #9D174D); }

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
        .dropdown-menu.active { transform: translateY(0); opacity: 1; visibility: visible; }

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
                        <span class="text-[10px] text-slate-400 uppercase tracking-widest">Dean Portal</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-6 overflow-y-auto">
                <p class="px-8 mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Main Menu</p>
                <ul class="space-y-1 px-4">
                    <li>
                        <a href="{{ route('dean.dashboard') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white @yield('dashboard_active')">
                            <span class="font-medium text-sm">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.users') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white @yield('users_active')">
                            <span class="font-medium text-sm">Manage User</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.faculties') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white @yield('faculties_active')">
                            <span class="font-medium text-sm">Manage Team</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dean.reports') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white @yield('reports_active')">
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
                    $deanAvatarName = urlencode($deanDisplayName);
                    $deanEmail = $deanUser?->email ?? 'dean@hms.edu';
                @endphp
                <div class="bg-white/5 rounded-2xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ $deanAvatarName }}&background=DB2777&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl border-2 border-white/20" alt="{{ $deanDisplayName }}">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white truncate">{{ $deanDisplayName }}</p>
                            <p class="text-[10px] text-slate-400">Dean Admin</p>
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
                    <button onclick="toggleSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-600" data-icon="mdi:menu"></span>
                    </button>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">@yield('page_title', 'Dashboard')</h2>
                        <p class="text-xs text-slate-400 font-light">Welcome back, {{ $deanDisplayName }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:gap-5">
                    <!-- Notification -->
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-500" data-icon="mdi:bell-outline"></span>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand rounded-full"></span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button onclick="toggleProfileMenu()" class="flex items-center gap-2.5 hover:bg-slate-100 p-1.5 pr-3 rounded-xl transition cursor-pointer focus:outline-none">
                            <img src="https://ui-avatars.com/api/?name={{ $deanAvatarName }}&background=DB2777&color=fff&size=40&font-size=0.4" class="w-9 h-9 rounded-xl border-2 border-white shadow-sm" alt="{{ $deanDisplayName }}">
                            <div class="text-right hidden sm:block">
                                <p class="text-xs font-bold text-slate-700">{{ $deanDisplayName }}</p>
                                <p class="text-[10px] text-slate-400">Dean Admin</p>
                            </div>
                            <span class="iconify text-slate-400 text-sm" data-icon="mdi:chevron-down"></span>
                        </button>

                        <div id="profileMenu" class="dropdown-menu absolute right-0 mt-3 w-60 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
                            <div class="px-5 py-4 bg-brand-soft border-b border-brand/10 flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ $deanAvatarName }}&background=DB2777&color=fff&size=40&font-size=0.4" class="w-10 h-10 rounded-xl" alt="{{ $deanDisplayName }}">
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">{{ $deanDisplayName }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $deanEmail }}</p>
                                </div>
                            </div>
                            <div class="py-2">
                                <a href="#" class="flex items-center gap-3 px-5 py-2.5 text-slate-600 hover:bg-slate-50 transition">
                                    <span class="iconify text-slate-400" data-icon="mdi:account-outline"></span>
                                    <span class="text-sm font-medium">My Profile</span>
                                </a>
                                <a href="#" class="flex items-center gap-3 px-5 py-2.5 text-slate-600 hover:bg-slate-50 transition">
                                    <span class="iconify text-slate-400" data-icon="mdi:cog-outline"></span>
                                    <span class="text-sm font-medium">Settings</span>
                                </a>
                                <a href="#" class="flex items-center gap-3 px-5 py-2.5 text-slate-600 hover:bg-slate-50 transition">
                                    <span class="iconify text-slate-400" data-icon="mdi:help-circle-outline"></span>
                                    <span class="text-sm font-medium">Help</span>
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

            <!-- Page Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>
    <aside id="sidebarMobile" class="sidebar-mobile fixed top-0 left-0 bottom-0 w-72 bg-gradient-to-b from-sidebar to-sidebar-light text-white flex flex-col z-40 lg:hidden shadow-2xl">
        <div class="h-20 flex items-center px-6 border-b border-white/10 shrink-0">
            <a href="#" class="flex items-center gap-3">
                <img src="{{ asset('chtm-logoo.png') }}" alt="HMS logo" class="h-16 w-auto object-contain drop-shadow-sm" />
                <div>
                    <span class="text-lg font-bold tracking-tight text-white block leading-tight">HMS</span>
                    <span class="text-[10px] text-slate-400 uppercase tracking-widest">Dean Portal</span>
                </div>
            </a>
        </div>
        <nav class="flex-1 py-6 overflow-y-auto">
            <ul class="space-y-1 px-4">
                <li><a href="{{ route('dean.dashboard') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white"><span class="font-medium text-sm">Dashboard</span></a></li>
                <li><a href="{{ route('dean.users') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white"><span class="font-medium text-sm">Manage User</span></a></li>
                <li><a href="{{ route('dean.faculties') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white"><span class="font-medium text-sm">Manage Team</span></a></li>
                <li><a href="{{ route('dean.reports') }}" class="nav-item flex items-center px-4 py-3 rounded-xl text-slate-400 hover:text-white"><span class="font-medium text-sm">Reports</span></a></li>
            </ul>
        </nav>
    </aside>

    <!-- Scripts -->
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

        function toggleSidebar() {
            const overlay = document.getElementById('sidebarOverlay');
            const sidebar = document.getElementById('sidebarMobile');
            overlay.classList.toggle('hidden');
            sidebar.classList.toggle('open');
        }
    </script>
    @stack('scripts')
</body>
</html>