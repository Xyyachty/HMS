<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hotel Management System | Student Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('chtm-logoo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        surface: '#F8FAFC',
                        'surface-raised': '#FFFFFF',
                        'sidebar': '#9D174D',
                        'sidebar-hover': '#831843',
                        'sidebar-active': '#DB2777',
                    },
                }
            }
        }
    </script>
    <style>
        ::selection { background: #DB2777; color: #fff; }
        body { font-family: 'Manrope', sans-serif; }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .brand-gradient { background: linear-gradient(135deg, #F472B6 0%, #DB2777 50%, #9D174D 100%); }
        .brand-gradient-subtle { background: linear-gradient(135deg, #FDF2F8 0%, #FCE7F3 50%, #FBCFE8 100%); }

        .app-sidebar {
            background: linear-gradient(180deg, #DB2777 0%, #BE185D 38%, #9D174D 72%, #500724 100%);
        }
        .sidebar-link {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,0.12);
            transform: translateX(2px);
        }
        .sidebar-link.active {
            background: rgba(255,255,255,0.22);
            box-shadow: 0 4px 16px -4px rgba(0, 0, 0, 0.25);
        }

        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px -12px rgba(0,0,0,0.1);
        }
        .stat-card:active {
            transform: translateY(0);
        }

        .role-badge-room { background: #FDF2F8; color: #DB2777; border: 1px solid #FBCFE8; }
        .role-badge-frontdesk { background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA; }
        .role-badge-restaurant { background: #FFFBEB; color: #D97706; border: 1px solid #FDE68A; }
        .role-badge-maintenance { background: #F5F3FF; color: #7C3AED; border: 1px solid #DDD6FE; }
        .role-badge-housekeeping { background: #F0FDFA; color: #0D9488; border: 1px solid #99F6E4; }

        .member-card {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .member-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -8px rgba(0,0,0,0.08);
        }

        .task-card {
            transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }
        .task-card:hover {
            transform: translateY(-1px);
            border-color: #FBCFE8;
            box-shadow: 0 10px 30px -12px rgba(219, 39, 119, 0.18);
        }

        .tab-btn {
            position: relative;
            transition: all 0.2s ease;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #F472B6, #DB2777);
            border-radius: 2px 2px 0 0;
        }

        .fade-in {
            animation: fadeIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Mobile sidebar overlay */
        .sidebar-overlay {
            transition: opacity 0.3s ease;
        }
        .sidebar-panel {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 1023px) {
            .sidebar-panel {
                transform: translateX(-100%);
            }
            .sidebar-panel.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-surface min-h-screen flex">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebarPanel" class="sidebar-panel app-sidebar fixed lg:sticky top-0 left-0 z-50 w-64 h-screen flex flex-col overflow-hidden">
        <!-- Logo -->
        <div class="px-6 py-4 flex items-center gap-3 border-b border-white/[0.06]">
            <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System" class="h-10 w-auto object-contain">
            <div>
                <h1 class="text-sm font-bold text-white tracking-tight leading-tight">Hotel Management System</h1>
                @php
                    $sidebarRoleLabel = !empty($studentRoles)
                        ? implode(' & ', array_map(fn ($r) => \App\Support\HotelTemplateBuilder::seatLabel($r), $studentRoles))
                        : 'Student';
                @endphp
                <p class="text-[10px] text-white font-medium uppercase tracking-widest">{{ $sidebarRoleLabel }}</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 mb-2 text-[10px] font-bold text-white uppercase tracking-[0.15em]">Main Menu</p>

            <button onclick="showSection('home'); closeMobileSidebar();"
                    id="nav-home"
                    class="sidebar-link active w-full flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                Dashboard 
            </button>
            <button onclick="showSection('group'); closeMobileSidebar();"
                    id="nav-group"
                    class="sidebar-link w-full flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                Team
                @if(isset($group) && !empty($group->name))
                    — {{ $group->name }}
                @endif
            </button>
            <button onclick="showSection('tasks'); closeMobileSidebar();"
                    id="nav-tasks"
                    class="sidebar-link w-full flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                Tasks
                {{-- Always rendered, hidden at zero: the concept task can close while
                     the page is open, and a badge that only exists server-side could
                     not then be taken away. --}}
                @if(!empty($studentRoles))
                    <span id="navTasksBadge" class="ml-auto bg-white/25 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md{{ $myRoleTasks->count() > 0 ? '' : ' hidden' }}"><span data-task-count="active">{{ $myRoleTasks->count() }}</span></span>
                @endif
            </button>
            <button onclick="showSection('activity'); closeMobileSidebar();"
                    id="nav-activity"
                    class="sidebar-link w-full flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                Activity Logs
            </button>
            <button onclick="showSection('reports'); closeMobileSidebar();"
                    id="nav-reports"
                    class="sidebar-link w-full flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                Reports
            </button>

        </nav>

        <!-- User Profile Bottom -->
        @php
            $authUser = auth()->user();
            $profileName = $studentDisplayName
                ?? (trim(implode(' ', array_filter([$authUser?->first_name, $authUser?->last_name])))
                    ?: ($authUser?->name ?? 'Student'));
        @endphp
        <div class="px-3 pb-4 border-t border-white/[0.06] pt-4">
            <div class="relative" id="studentProfileMenu">
                <button type="button"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/[0.06] transition-colors"
                        aria-haspopup="true" aria-expanded="false"
                        onclick="toggleStudentProfileMenu()">
                    @include('partials.user-avatar', [
                        'user'        => $authUser,
                        'name'        => $profileName,
                        'size'        => 'w-9 h-9',
                        'currentUser' => true,
                        'extraClasses' => 'shadow-lg shadow-brand/20',
                    ])
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $profileName }}</p>
                        <p class="text-[10px] text-white">Student</p>
                    </div>
                    <span class="iconify text-white text-sm transition-transform" data-icon="mdi:chevron-up" id="profileChevron"></span>
                </button>
                <div class="absolute bottom-full left-0 mb-2 w-full rounded-xl border border-white/10 bg-sidebar-hover shadow-2xl p-1.5 hidden" id="studentProfileDropdown" role="menu">
                    <a href="#profile" onclick="showSection('profile'); closeStudentProfileMenu(); closeMobileSidebar();" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-white hover:bg-white/[0.06] transition-colors" role="menuitem">
                        <span class="iconify text-base" data-icon="mdi:account-outline"></span>
                        My Profile
                    </a>
                    <div class="h-px bg-white/[0.06] my-1"></div>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-red-400 hover:bg-red-500/10 transition-colors" role="menuitem">
                            <span class="iconify text-base" data-icon="mdi:logout-variant"></span>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Area -->
    <div class="flex-1 flex flex-col min-h-screen min-w-0">
        <!-- Top Bar -->
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200/60">
            <div class="px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
                <!-- Mobile hamburger -->
                <button onclick="openMobileSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors -ml-2">
                    <span class="iconify text-xl text-slate-600" data-icon="mdi:menu"></span>
                </button>

                <!-- Breadcrumb / Page Title -->
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-slate-400">Hotel Management System</span>
                    <span class="iconify text-slate-300 text-xs" data-icon="mdi:chevron-right"></span>
                    <span id="breadcrumb-current" class="font-semibold text-slate-700">Dashboard</span>
                </div>

                <div class="flex-1"></div>

                <!-- Right side actions -->
                <div class="flex items-center gap-2">
                    @include('partials.datetime-clock')
                    @include('partials.notification-bell')
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 px-4 sm:px-6 py-3 overflow-y-auto" style="background-color:#F5F5F5">
            @php
                $getMemberValue = function ($member, $field, $fallback = '') {
                    if (is_array($member)) return $member[$field] ?? $fallback;
                    if (is_object($member)) return $member->{$field} ?? $fallback;
                    if ($field === 'name') return (string) $member;
                    return $fallback;
                };
                $roleLabels = [
                    'front_desk'            => 'Front Desk',
                    'restaurant_management' => 'Restaurant Management',
                    'room_management'       => 'Room Management',
                    'maintenance'           => 'Maintenance',
                    'housekeeping'          => 'Housekeeping / Maintenance',
                ];
                $roleIcons = [
                    'front_desk'            => 'mdi:desk',
                    'restaurant_management' => 'mdi:silverware-fork-knife',
                    'room_management'       => 'mdi:bed-outline',
                    'maintenance'           => 'mdi:broom',
                    'housekeeping'          => 'mdi:sparkles',
                ];
                $roleBadgeClasses = [
                    'front_desk'            => 'role-badge-frontdesk',
                    'restaurant_management' => 'role-badge-restaurant',
                    'room_management'       => 'role-badge-room',
                    'maintenance'           => 'role-badge-maintenance',
                    'housekeeping'          => 'role-badge-housekeeping',
                ];
                $myRoleLabels = array_map(fn($r) => $roleLabels[$r] ?? ucfirst(str_replace('_', ' ', $r)), $studentRoles ?? []);
                $myRoleIcons = array_map(fn($r) => $roleIcons[$r] ?? 'mdi:account-outline', $studentRoles ?? []);
                $myRoleBadge = !empty($studentRoles) ? ($roleBadgeClasses[$studentRoles[0]] ?? 'role-badge-room') : 'role-badge-room';
                $myRoleLabel = !empty($myRoleLabels) ? implode(' & ', $myRoleLabels) : 'Not Assigned';
                $myRoleIcon = !empty($myRoleIcons) ? $myRoleIcons[0] : 'mdi:account-question-outline';
            @endphp

            <!-- ==================== HOME SECTION ==================== -->
            @php
                // Everything the home cards and panels read, derived once here so the
                // markup below stays declarative.
                $homeRoleLabels = [
                    'front_desk'            => 'Front Desk',
                    'restaurant_management' => 'Restaurant Services',
                    'room_management'       => 'Room Management',
                    'maintenance'           => 'Maintenance',
                    'housekeeping'          => 'Housekeeping',
                ];
                $homeRoleTints = [
                    'front_desk'            => ['bg' => 'bg-orange-50',  'text' => 'text-orange-500',  'bar' => 'bg-orange-500'],
                    'restaurant_management' => ['bg' => 'bg-amber-50',   'text' => 'text-amber-500',   'bar' => 'bg-amber-500'],
                    'room_management'       => ['bg' => 'bg-pink-50',    'text' => 'text-brand',       'bar' => 'bg-brand'],
                    'maintenance'           => ['bg' => 'bg-violet-50',  'text' => 'text-violet-500',  'bar' => 'bg-violet-500'],
                    'housekeeping'          => ['bg' => 'bg-teal-50',    'text' => 'text-teal-500',    'bar' => 'bg-teal-500'],
                ];
                $homeTint = fn($role, $key) => $homeRoleTints[$role][$key] ?? ($key === 'bar' ? 'bg-slate-400' : ($key === 'text' ? 'text-slate-400' : 'bg-slate-50'));

                $homeRecentActivities = ($selfActivityLogs ?? collect())->take(5);
                $homeTeamProgress     = ($teamRoleProgress ?? collect());
                $homeDeadlines        = ($upcomingDeadlines ?? collect());

                $homeMemberCount   = ($groupMembers ?? collect())->count();
                $homeActiveTasks   = $pendingTasksCount ?? 0;
                $homeMyPending     = !empty($studentRoles) ? $myRoleTasks->count() : 0;
                $homeTotalTasks    = ($completedTasksCount ?? 0) + ($pendingTasksCount ?? 0);
                // Only the tasks this student actually owns can be "late" for them.
                $homeOverdueCount  = !empty($studentRoles)
                    ? $myRoleTasks->filter(fn($t) => $t->due_date && $t->due_date->isPast())->count()
                    : 0;

                // Greeting follows Philippine time, not the server's own timezone
                // (production runs on UTC) — refreshHomeGreeting() below keeps it
                // live client-side the same way past the first paint.
                $homeManilaHour = (int) now('Asia/Manila')->format('G');
                $homeGreeting = ($homeManilaHour >= 5 && $homeManilaHour < 12)
                    ? 'Good morning'
                    : (($homeManilaHour >= 12 && $homeManilaHour < 18) ? 'Good afternoon' : 'Good evening');
                $homeFirstName = trim(explode(' ', trim($studentDisplayName ?? (auth()->user()->name ?? 'Student')))[0]);

                // The upcoming-task rows show how far the owning role has got, so the
                // bar on each row is real team data rather than a per-task guess.
                $homeProgressByRole = collect($homeTeamProgress)->keyBy('role');

                $homeRate = (int) ($completionRate ?? 0);
                $homeRingLength = 175.9; // 2 * pi * r, with r = 28
                $homeRingOffset = $homeRingLength - ($homeRingLength * min(100, max(0, $homeRate)) / 100);
            @endphp

            <div id="home-section" class="section-content fade-in space-y-5">
                <!-- Welcome -->
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-2xl sm:text-[32px] font-extrabold tracking-tight text-slate-900 leading-tight">
                            {{-- refreshHomeGreeting() rewrites this text on a timer, so the
                                 greeting turns over on its own when the Manila hour crosses
                                 5am/12pm/6pm without the student refreshing. --}}
                            <span id="homeGreetingText">{{ $homeGreeting }}, {{ $homeFirstName }}!</span> <span class="align-middle">&#128075;</span>
                        </h2>
                        <p class="text-sm text-slate-500 mt-1.5">Here's your progress and tasks for today.</p>
                    </div>
                    <p class="text-sm font-semibold italic text-brand shrink-0 mt-1">"Learn Today. Serve Tomorrow."</p>
                </div>

                {{-- Stats Row. Each card opens the section that owns its number, through the
                     same showSection() the sidebar calls — so the sidebar highlight, the
                     breadcrumb and the ?section= URL all follow along for free. --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Team members -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-full bg-brand flex items-center justify-center shrink-0">
                                <span class="iconify text-white text-2xl" data-icon="mdi:account-group-outline"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-bold text-slate-800 leading-tight">Team Members</p>
                                <p class="text-[34px] font-extrabold text-slate-900 leading-none mt-1">{{ $homeMemberCount }}</p>
                                <p class="text-[12px] text-slate-400 font-medium mt-1.5 truncate">{{ $studentClass->name ?? $group->name ?? 'No team yet' }}</p>
                                <button type="button" onclick="showSection('group')" aria-label="Open My Team"
                                        class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-bold text-brand hover:text-brand-dark transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
                                    View Team <span class="iconify text-base" data-icon="mdi:arrow-right"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active tasks (team) -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-full bg-violet-50 flex items-center justify-center shrink-0">
                                <span class="iconify text-violet-500 text-2xl" data-icon="mdi:clipboard-text-outline"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-bold text-slate-800 leading-tight">Active Tasks</p>
                                <p class="text-[34px] font-extrabold text-slate-900 leading-none mt-1">{{ $homeActiveTasks }}</p>
                                <p class="text-[12px] text-slate-400 font-medium mt-1.5 truncate">{{ $homeMyPending }} assigned to your role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                                <button type="button" onclick="showSection('tasks')" aria-label="Open My Tasks"
                                        class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-bold text-brand hover:text-brand-dark transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
                                    View Tasks <span class="iconify text-base" data-icon="mdi:arrow-right"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending tasks (mine) -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center shrink-0">
                                <span class="iconify text-amber-500 text-2xl" data-icon="mdi:clock-outline"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-bold text-slate-800 leading-tight">Pending Tasks</p>
                                <p class="text-[34px] font-extrabold text-slate-900 leading-none mt-1"><span data-task-count="active">{{ $homeMyPending }}</span></p>
                                @if($homeOverdueCount > 0)
                                    <p class="text-[12px] font-semibold text-red-500 mt-1.5 truncate">{{ $homeOverdueCount }} past due date</p>
                                @else
                                    <p class="text-[12px] text-slate-400 font-medium mt-1.5 truncate">Awaiting submission</p>
                                @endif
                                <button type="button" onclick="showSection('tasks')" aria-label="Open My Tasks"
                                        class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-bold text-brand hover:text-brand-dark transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
                                    View Pending <span class="iconify text-base" data-icon="mdi:arrow-right"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Completion rate, drawn as a ring: the same number the reports page
                         shows, read at a glance. --}}
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-start gap-4">
                            <div class="relative w-[86px] h-[86px] shrink-0">
                                <svg class="w-[86px] h-[86px] -rotate-90" viewBox="0 0 64 64" aria-hidden="true">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#F1F5F9" stroke-width="7"></circle>
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#DB2777" stroke-width="7" stroke-linecap="round"
                                            stroke-dasharray="{{ $homeRingLength }}" stroke-dashoffset="{{ $homeRingOffset }}"></circle>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-[20px] font-extrabold text-slate-900">{{ $homeRate }}%</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[15px] font-bold text-slate-800 leading-tight">Overall Progress</p>
                                <p class="text-[12px] text-slate-400 font-medium mt-1.5 leading-snug">
                                    <span data-task-count="completed">{{ $completedTasksCount ?? 0 }}</span> of {{ $homeTotalTasks }} team tasks done.<br>
                                    {{ $homeRate >= 70 ? "You're on the right track!" : 'Keep completing tasks.' }}
                                </p>
                                <button type="button" onclick="showSection('reports')" aria-label="Open Reports"
                                        class="mt-3 inline-flex items-center gap-1.5 text-[13px] font-bold text-brand hover:text-brand-dark transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
                                    View Progress <span class="iconify text-base" data-icon="mdi:arrow-right"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Three-panel row: upcoming tasks, team progress, activity -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

                    <!-- My Upcoming Tasks -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                            <p class="text-lg font-bold text-slate-800">My Upcoming Tasks</p>
                            <button type="button" onclick="showSection('tasks')"
                                    class="text-[12px] font-bold text-brand hover:text-brand-dark transition-colors">View All</button>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse($homeDeadlines as $index => $task)
                                @php
                                    $isLate    = $task->due_date && $task->due_date->isPast();
                                    $roleRow   = $homeProgressByRole[$task->role] ?? null;
                                    $rolePct   = $roleRow['percent'] ?? 0;
                                    $roleDone  = $roleRow['done'] ?? 0;
                                    $roleTotal = $roleRow['total'] ?? 0;
                                @endphp
                                <div class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 {{ $homeTint($task->role, 'bg') }} {{ $homeTint($task->role, 'text') }}">
                                            <span class="iconify text-xl" data-icon="{{ $roleIcons[$task->role] ?? 'mdi:clipboard-text-outline' }}"></span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            {{-- The task's own number on the checklist, not its position in
                                                 this list: counting rows made the same work read as a
                                                 different task here than on the faculty's Set Task screen. --}}
                                            @php $homeTaskStep = \App\Support\TaskChecklist::stepForTitle($task->title); @endphp
                                            @if($homeTaskStep !== null)
                                                <p class="text-[13px] font-extrabold text-slate-800 tracking-wide">TASK {{ str_pad($homeTaskStep + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                            @endif
                                            <p class="text-[13px] text-slate-500 leading-snug line-clamp-2">{{ $task->title }}</p>
                                            <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $homeTint($task->role, 'bg') }} {{ $homeTint($task->role, 'text') }}">
                                                {{ $homeRoleLabels[$task->role] ?? $task->role }}
                                            </span>
                                        </div>
                                        <div class="shrink-0 w-12 rounded-xl border py-1 text-center {{ $isLate ? 'border-red-100 bg-red-50' : 'border-pink-100 bg-brand-soft' }}">
                                            <p class="text-[9px] font-semibold {{ $isLate ? 'text-red-400' : 'text-slate-400' }}">{{ $isLate ? 'Late' : 'Due' }}</p>
                                            <p class="text-[9px] font-bold uppercase {{ $isLate ? 'text-red-400' : 'text-brand-light' }}">{{ $task->due_date->format('M') }}</p>
                                            <p class="text-[15px] font-extrabold leading-tight {{ $isLate ? 'text-red-500' : 'text-slate-800' }}">{{ $task->due_date->format('j') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="h-1.5 w-24 rounded-full bg-slate-100 overflow-hidden shrink-0">
                                            <div class="h-full rounded-full {{ $homeTint($task->role, 'bar') }}" style="width: {{ $rolePct }}%"></div>
                                        </div>
                                        <p class="text-[12px] font-extrabold text-slate-700 shrink-0">{{ $rolePct }}%</p>
                                        <p class="text-[12px] text-slate-400 font-medium truncate">{{ $roleDone }} of {{ $roleTotal }} tasks done in this role</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:calendar-blank-outline"></span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-400">No upcoming deadlines</p>
                                    <p class="text-xs text-slate-300 mt-1">Tasks with a due date will show up here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- My Progress Overview — this student's own team only, broken down by
                         the role that owns each task, so a member can see where the team
                         still has work outstanding. --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-slate-800">My Progress Overview</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $group->name ?? 'Your team' }}</p>
                            </div>
                            <button type="button" onclick="showSection('reports')"
                                    class="text-[12px] font-bold text-brand hover:text-brand-dark transition-colors shrink-0">View Report</button>
                        </div>
                        <div class="px-5 pb-5 space-y-4">
                            @forelse($homeTeamProgress as $row)
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 {{ $homeTint($row['role'], 'bg') }} {{ $homeTint($row['role'], 'text') }}">
                                        <span class="iconify text-xl" data-icon="{{ $roleIcons[$row['role']] ?? 'mdi:account-outline' }}"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[14px] font-bold text-slate-800 truncate">{{ $homeRoleLabels[$row['role']] ?? $row['role'] }}</p>
                                        <div class="mt-1.5 flex items-center gap-2.5">
                                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden flex-1">
                                                <div class="h-full rounded-full {{ $homeTint($row['role'], 'bar') }}" style="width: {{ $row['percent'] }}%"></div>
                                            </div>
                                            <p class="text-[14px] font-extrabold text-slate-700 shrink-0 w-10 text-right">{{ $row['percent'] }}%</p>
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-1 text-right">{{ $row['done'] }} / {{ $row['total'] }} tasks</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:chart-timeline-variant"></span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-400">No tasks yet</p>
                                    <p class="text-xs text-slate-300 mt-1">Progress appears once your faculty assigns work.</p>
                                </div>
                            @endforelse

                            @if($homeTeamProgress->isNotEmpty())
                                <div class="rounded-2xl bg-brand-soft border border-pink-100 px-4 py-3 flex items-start gap-3">
                                    <span class="iconify text-brand text-xl shrink-0" data-icon="mdi:trophy-outline"></span>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-brand-dark">
                                            {{ $homeRate >= 70 ? "You're on the right track!" : ($homeRate > 0 ? 'Good start — keep going!' : 'Time to get started!') }}
                                        </p>
                                        <p class="text-[11px] text-brand-dark/70 mt-0.5">Keep completing tasks to improve your progress.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                            <p class="text-lg font-bold text-slate-800">Recent Activity</p>
                            <button type="button" onclick="showSection('activity')"
                                    class="text-[12px] font-bold text-brand hover:text-brand-dark transition-colors">View All</button>
                        </div>
                        <div class="px-5 pb-5 space-y-4">
                            @forelse($homeRecentActivities as $task)
                                @php
                                    $isDone = $task->status === 'archived';
                                    $needsRevision = !$isDone && filled($task->feedback ?? null);
                                    $actionLabel = $isDone ? 'You completed task' : ($needsRevision ? 'Faculty sent back task' : 'Task assigned to you');
                                    $actionIcon  = $isDone ? 'mdi:check-circle-outline' : ($needsRevision ? 'mdi:pencil-outline' : 'mdi:file-document-outline');
                                    $actionTint  = $isDone ? 'bg-emerald-50 text-emerald-500' : ($needsRevision ? 'bg-red-50 text-red-500' : 'bg-blue-50 text-blue-500');
                                    $stamp       = $task->updated_at;
                                    $stampDay    = $stamp
                                        ? ($stamp->isToday() ? 'Today' : ($stamp->isYesterday() ? 'Yesterday' : $stamp->format('M j, Y')))
                                        : '';
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 {{ $actionTint }}">
                                        <span class="iconify text-lg" data-icon="{{ $actionIcon }}"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12px] text-slate-500">{{ $actionLabel }}</p>
                                        <p class="text-[13px] font-bold text-slate-800 leading-snug line-clamp-2">{{ $task->title }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $homeRoleLabels[$task->role] ?? $task->role }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[11px] text-slate-400 font-medium whitespace-nowrap">{{ $stampDay }}</p>
                                        <p class="text-[11px] text-slate-300 font-medium whitespace-nowrap mt-0.5">{{ optional($stamp)->format('g:i A') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-2xl text-slate-300" data-icon="mdi:history"></span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-400">No recent activity</p>
                                    <p class="text-xs text-slate-300 mt-1">Your assigned and completed tasks will appear here.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Closing banner: the same photo the landing page uses, behind a light
                     wash so the headline stays readable. --}}
                <div class="relative rounded-2xl overflow-hidden border border-slate-100 min-h-[185px] flex items-center"
                     style="background-image: linear-gradient(90deg, rgba(255,255,255,.97) 0%, rgba(255,255,255,.90) 38%, rgba(253,242,248,.55) 68%, rgba(157,23,77,.18) 100%), url('{{ asset('images/hotel/try.jpg') }}'); background-size: cover; background-position: center;">
                    <div class="px-6 sm:px-10 py-7 w-full flex flex-wrap items-end justify-between gap-4">
                        <div class="min-w-0 max-w-xl">
                            <h3 class="text-2xl sm:text-[38px] font-extrabold tracking-tight text-slate-900 leading-[1.1]">
                                Great Hospitality<br class="hidden sm:block"> Starts with You.
                            </h3>
                            <p class="text-[13px] sm:text-sm text-slate-600 mt-3">Develop your skills. Work as a team. Make every guest feel at home.</p>
                        </div>
                        <p class="text-lg sm:text-2xl font-semibold italic text-brand-dark shrink-0 border-b-2 border-brand-light pb-1">
                            Same People, Brighter Stays.
                        </p>
                    </div>
                </div>
            </div>


            <!-- ==================== GROUP SECTION ==================== -->
            @php
                $teamMemberCount  = ($groupMembers ?? collect())->count();
                $teamTaskTotal    = ($completedTasksCount ?? 0) + ($pendingTasksCount ?? 0);
                $teamNextDeadline = ($upcomingDeadlines ?? collect())->first()?->due_date;
                $teamPercent      = (int) ($completionRate ?? 0);
                // Donut geometry for the Overall Progress ring in the team header.
                $ringRadius = 26;
                $ringLength = 2 * M_PI * $ringRadius;
                $ringFilled = $ringLength * min(max($teamPercent, 0), 100) / 100;

                $groupRoleTints = [
                    'front_desk'            => ['bg' => 'bg-blue-50',   'text' => 'text-blue-500',   'bar' => 'bg-blue-500'],
                    'restaurant_management' => ['bg' => 'bg-amber-50',  'text' => 'text-amber-500',  'bar' => 'bg-amber-500'],
                    'room_management'       => ['bg' => 'bg-pink-50',   'text' => 'text-brand',      'bar' => 'bg-brand'],
                    'maintenance'           => ['bg' => 'bg-violet-50', 'text' => 'text-violet-500', 'bar' => 'bg-violet-500'],
                    'housekeeping'          => ['bg' => 'bg-teal-50',   'text' => 'text-teal-500',   'bar' => 'bg-teal-500'],
                ];
                $groupTint = fn($role, $key) => $groupRoleTints[$role][$key] ?? ($key === 'bar' ? 'bg-slate-400' : ($key === 'text' ? 'text-slate-400' : 'bg-slate-100'));
            @endphp

            <div id="group-section" class="section-content hidden fade-in space-y-4">
                <div>
                    <h2 class="text-2xl sm:text-[30px] font-extrabold tracking-tight text-slate-900 leading-tight">My Team</h2>
                    <p class="text-sm text-slate-500 mt-1">View your team details, members, tasks and progress.</p>
                </div>

                {{-- Team header. The heading, eyebrow, type line and description block
                     are repainted by paintTeamHeaderConcept() once faculty approves a
                     concept, so their ids have to stay as they are. --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 sm:px-7 py-6">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                        <div class="flex items-start gap-5 flex-1 min-w-0">
                            <div class="w-[88px] h-[88px] rounded-full bg-brand-soft flex items-center justify-center shrink-0">
                                <span class="iconify text-brand text-[42px]" data-icon="mdi:account-group"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($group)
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <h3 id="teamHeaderName" class="text-2xl font-extrabold text-slate-900 leading-tight"
                                            data-team-name="{{ $group->name }}">{{ $group->name }}</h3>
                                        @if(!empty($studentClass))
                                            <span class="px-2.5 py-1 rounded-lg bg-brand-soft text-brand text-[11px] font-bold uppercase tracking-wider">{{ $studentClass->name }}</span>
                                        @endif
                                    </div>
                                    <p id="teamHeaderEyebrow" class="text-slate-400 text-[13px] font-semibold mt-1">Hotel Management Simulation</p>
                                    <p id="teamHeaderType" class="hidden text-[12px] font-bold text-slate-500 mt-0.5"></p>
                                @else
                                    <h3 class="text-2xl font-extrabold text-slate-900 leading-tight">Not assigned yet</h3>
                                    <p class="text-slate-400 text-[13px] font-semibold mt-1">Team</p>
                                @endif

                                {{-- The approved concept's description. Painted from the same
                                     payload as the proposal cards, so there is one description
                                     of "what the team's concept is" rather than two that drift. --}}
                                <div id="teamHeaderConcept" class="hidden mt-2 max-w-lg"></div>
                            </div>
                        </div>

                        <!-- Team figures -->
                        <div class="flex items-center gap-4 sm:gap-7 shrink-0 lg:border-l lg:border-slate-100 lg:pl-7 overflow-x-auto">
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="iconify text-slate-300 text-[26px]" data-icon="mdi:account-multiple-outline"></span>
                                <div>
                                    <p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $teamMemberCount }}</p>
                                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Members</p>
                                </div>
                            </div>
                            <div class="w-px h-11 bg-slate-100 shrink-0"></div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="iconify text-slate-300 text-[26px]" data-icon="mdi:clipboard-text-outline"></span>
                                <div>
                                    <p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $teamTaskTotal }}</p>
                                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Assigned Tasks</p>
                                </div>
                            </div>
                            <div class="w-px h-11 bg-slate-100 shrink-0"></div>
                            <div class="flex items-center gap-3 shrink-0">
                                <svg class="w-[60px] h-[60px] -rotate-90 shrink-0" viewBox="0 0 60 60" aria-hidden="true">
                                    <circle cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#F1F5F9" stroke-width="7"></circle>
                                    <circle cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#DB2777" stroke-width="7" stroke-linecap="round"
                                            stroke-dasharray="{{ round($ringFilled, 2) }} {{ round($ringLength, 2) }}"></circle>
                                </svg>
                                <div>
                                    <p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $teamPercent }}%</p>
                                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Overall Progress</p>
                                </div>
                            </div>
                            <div class="w-px h-11 bg-slate-100 shrink-0"></div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="iconify text-slate-300 text-[26px]" data-icon="mdi:calendar-outline"></span>
                                <div>
                                    <p class="text-xl font-extrabold text-slate-900 leading-none whitespace-nowrap">{{ $teamNextDeadline ? $teamNextDeadline->format('M j, Y') : 'None set' }}</p>
                                    <p class="text-[12px] text-slate-400 font-semibold mt-1">Next Deadline</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

                    <!-- Team Members -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                            <p class="text-lg font-bold text-slate-800">Team Members</p>
                            <span class="text-[13px] font-bold text-brand">{{ $teamMemberCount }}</span>
                        </div>

                        @if(isset($groupMembers) && $groupMembers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[620px] text-left">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500 w-10">#</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Member</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Role</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500 text-center">Tasks Assigned</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500 w-32">Progress</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Last Active</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groupMembers as $index => $member)
                                            @php
                                                $mRoles = is_string($member)
                                                    ? []
                                                    : (array) ($member->roles ?? []);
                                                if ($mRoles === [] && !empty($member->role ?? null)) {
                                                    $mRoles = [$member->role];
                                                }
                                                $memberUserId  = $getMemberValue($member, 'id');
                                                $isCurrentUser = $memberUserId === (auth()->id() ?? null);
                                                $memberName    = $getMemberValue($member, 'name', 'Unknown');
                                                $stats         = ($memberTaskStats ?? collect())->get($memberUserId, ['total' => 0, 'done' => 0, 'percent' => 0]);
                                                $lastSeen      = is_object($member) ? ($member->user?->last_seen_at ?? null) : null;
                                                $primaryRole   = $mRoles[0] ?? null;
                                            @endphp
                                            <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors" data-member-row data-member-id="{{ $memberUserId }}">
                                                <td class="px-4 py-3.5 text-[13px] font-semibold text-slate-400">{{ $index + 1 }}</td>

                                                <!-- Member -->
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        @include('partials.user-avatar', [
                                                            'user'        => is_object($member) ? ($member->user ?? null) : null,
                                                            'name'        => $memberName,
                                                            'size'        => 'w-10 h-10',
                                                            'currentUser' => $isCurrentUser,
                                                        ])
                                                        <div class="min-w-0">
                                                            <p class="text-[13px] font-bold text-slate-800 truncate">
                                                                {{ $memberName }}
                                                                @if($isCurrentUser)
                                                                    <span class="ml-1 px-1.5 py-0.5 bg-brand-soft text-brand text-[8px] font-bold rounded-md uppercase">You</span>
                                                                @endif
                                                            </p>
                                                            <div class="flex items-center gap-1.5" data-presence-user="{{ $memberUserId }}">
                                                                <span class="w-1.5 h-1.5 rounded-full member-online-dot {{ $isCurrentUser ? 'bg-emerald-400 pulse-dot' : 'bg-slate-200' }}"></span>
                                                                <span class="text-[10px] member-online-label {{ $isCurrentUser ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">{{ $isCurrentUser ? 'Online' : 'Offline' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Role -->
                                                <td class="px-4 py-3.5">
                                                    @if($primaryRole)
                                                        @php
                                                            $displayRole    = $roleLabels[$primaryRole] ?? ucfirst(str_replace('_', ' ', $primaryRole));
                                                            $roleBadgeClass = $roleBadgeClasses[$primaryRole] ?? 'role-badge-room';
                                                        @endphp
                                                        <span class="inline-flex items-center {{ $roleBadgeClass }} px-2.5 py-1 rounded-lg text-[11px] font-semibold whitespace-nowrap">
                                                            {{ $displayRole }}
                                                        </span>
                                                        @if(count($mRoles) > 1)
                                                            <span class="ml-1 text-[10px] font-bold text-slate-400">+{{ count($mRoles) - 1 }}</span>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center bg-slate-100 text-slate-400 px-2.5 py-1 rounded-lg text-[11px] font-semibold">No role</span>
                                                    @endif
                                                </td>

                                                <!-- Tasks assigned -->
                                                <td class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-700">{{ $stats['total'] }}</td>

                                                <!-- Progress -->
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[12px] font-bold text-slate-600 w-8 shrink-0">{{ $stats['percent'] }}%</span>
                                                        <span class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                            <span class="block h-full rounded-full {{ $groupTint($primaryRole, 'bar') }}" style="width: {{ $stats['percent'] }}%"></span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <!-- Last active -->
                                                <td class="px-4 py-3.5">
                                                    @if($lastSeen)
                                                        <p class="text-[12px] font-semibold text-slate-600 whitespace-nowrap">{{ $lastSeen->format('M j, Y') }}</p>
                                                        <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ $lastSeen->format('g:i A') }}</p>
                                                    @else
                                                        <span class="text-[12px] text-slate-300">—</span>
                                                    @endif
                                                </td>

                                                {{-- Teammates only; own history lives in the Activity Logs nav section. --}}
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center justify-end">
                                                        @if(!$isCurrentUser && $memberUserId)
                                                            <button type="button"
                                                                onclick="openMemberActivityModal({{ (int) $memberUserId }}, {{ json_encode($memberName) }})"
                                                                class="w-9 h-9 inline-flex items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:text-brand hover:border-brand/40 transition"
                                                                title="View {{ $memberName }}'s activity logs">
                                                                <span class="iconify text-base" data-icon="mdi:clipboard-text-clock-outline"></span>
                                                            </button>
                                                        @else
                                                            <span class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-brand-soft text-brand" title="This is you">
                                                                <span class="iconify text-base" data-icon="mdi:account-check-outline"></span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-4 py-12 text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-group-outline"></span>
                                </div>
                                <p class="text-sm font-bold text-slate-400">No members yet</p>
                                <p class="text-xs text-slate-300 mt-1">They'll appear once assigned by faculty</p>
                            </div>
                        @endif
                    </div>

                    {{-- Assigned Tasks — faculty address tasks to a department rather than
                         to a person, so each row is one department's workload for this
                         team: how many tasks, how far along, and the nearest date due. --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                            <p class="text-lg font-bold text-slate-800">Assigned Tasks</p>
                            <button type="button" onclick="showSection('tasks')"
                                    class="text-[12px] font-bold text-brand hover:text-brand-dark transition-colors">View All Tasks</button>
                        </div>
                        @if(($teamRoleProgress ?? collect())->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[520px] text-left">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Task</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Status</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500">Due Date</th>
                                            <th class="px-4 py-3 text-[12px] font-bold text-slate-500 w-28">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($teamRoleProgress ?? collect()) as $row)
                                            @php
                                                $rowLabel = $roleLabels[$row['role']] ?? ucfirst(str_replace('_', ' ', $row['role']));
                                                $rowStatus = $row['percent'] >= 100
                                                    ? ['label' => 'Completed',   'class' => 'bg-emerald-50 text-emerald-600']
                                                    : ($row['percent'] > 0
                                                        ? ['label' => 'In Progress', 'class' => 'bg-blue-50 text-blue-600']
                                                        : ['label' => 'Not Started', 'class' => 'bg-slate-100 text-slate-500']);
                                            @endphp
                                            <tr class="border-b border-slate-100">
                                                <td class="px-4 py-3.5">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $groupTint($row['role'], 'bg') }} {{ $groupTint($row['role'], 'text') }}">
                                                            <span class="iconify text-lg" data-icon="{{ $roleIcons[$row['role']] ?? 'mdi:clipboard-text-outline' }}"></span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-[13px] font-bold text-slate-800 truncate">{{ $rowLabel }}</p>
                                                            <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $row['total'] }} task{{ $row['total'] === 1 ? '' : 's' }} &middot; {{ $row['done'] }} done</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap {{ $rowStatus['class'] }}">{{ $rowStatus['label'] }}</span>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <p class="text-[12px] font-semibold text-slate-600 whitespace-nowrap">{{ $row['next_due'] ? $row['next_due']->format('M j, Y') : '—' }}</p>
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <p class="text-[12px] font-bold text-slate-600">{{ $row['percent'] }}%</p>
                                                    <span class="block h-1.5 mt-1 rounded-full bg-slate-100 overflow-hidden">
                                                        <span class="block h-full rounded-full {{ $groupTint($row['role'], 'bar') }}" style="width: {{ $row['percent'] }}%"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-2xl text-slate-300" data-icon="mdi:clipboard-text-outline"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No tasks assigned yet</p>
                                <p class="text-xs text-slate-300 mt-1">Faculty tasks for your team will appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- The team's two hotel concepts, proposed here rather than on the task
                     row over in Tasks: naming the hotel and describing it is the team's
                     own identity work, and this is the page the team reads.

                     Faculty judges each proposal separately, so each card carries its own
                     state, its own feedback and its own history. Front Desk writes each
                     first version and hands the pair in with the one button in this
                     header, because the pair exists so faculty can weigh them against
                     each other.

                     Once faculty decides there is nothing left to propose, so
                     paintTeamHeaderConcept() hides this whole card — the winner has
                     already moved up into the team header above.

                     Drawn from #conceptPanel by paintHotelConcepts(), which repaints
                     after every save and every verdict, so there is one description of
                     this markup rather than a server copy and a client copy that
                     drift. Gated on the team, like the dialog it opens: a member with no
                     team has no concepts to write. --}}
                @if($group)
                <div id="conceptTeamCard" class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800">Hotel Concepts</h3>
                            <p class="text-[11px] text-slate-400">Name the hotel and describe it — two proposals for your faculty to review.</p>
                        </div>
                        {{-- One button for the pair. Hidden until both slots are filled;
                             paintHotelConcepts() toggles it off conceptState.can_submit and
                             relabels it Resubmit whenever a slot is coming back from
                             revision rather than going in for the first time. --}}
                        <button type="button" id="conceptSubmitAllBtn" onclick="submitHotelConcepts()"
                            class="hidden shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-white brand-gradient shadow-md shadow-brand/20 hover:opacity-90 transition disabled:opacity-60">
                            <span class="iconify text-[13px]" data-icon="mdi:send-outline"></span>
                            <span id="conceptSubmitAllLabel">Submit both to Faculty</span>
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="mx-5 mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mx-5 mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div id="conceptPanel" class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                </div>
                @endif

                <div class="flex justify-center pt-1">
                    <button type="button" onclick="showSection('tasks')"
                            class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl bg-white border border-slate-200 text-[14px] font-bold text-slate-700 hover:bg-slate-50 hover:text-brand transition-colors shadow-sm">
                        <span class="iconify text-lg" data-icon="mdi:format-list-checks"></span>
                        View All Team Tasks
                    </button>
                </div>
            </div>

            <!-- ==================== TASKS SECTION ==================== -->
            <div id="tasks-section" class="section-content hidden fade-in space-y-5">
                @php
                    // One entry per assigned role — a member may hold more than one.
                    $myModules = \App\Support\HotelTemplateBuilder::modulesForRoles($studentRoles ?? []);

                    /*
                     * Group every task (active + completed) under its numbered TASK.
                     * TaskChecklist::STEP_ROLES says which role owns which step, so a task's
                     * title (or failing that, its role) decides which group it falls in. A
                     * row that matches neither — hand-written, or a role with no numbered
                     * step, e.g. maintenance — falls into an 'other' bucket shown last.
                     */
                    $taskGroups = collect();
                    $addToGroup = function ($task, string $rowStatus, int $rowPercent) use (&$taskGroups) {
                        $step = \App\Support\TaskChecklist::stepForTitle($task->title);
                        if ($step === null) {
                            $found = array_search($task->role, \App\Support\TaskChecklist::STEP_ROLES, true);
                            $step = $found === false ? null : $found;
                        }
                        $key = $step ?? 'other';

                        if (!$taskGroups->has($key)) {
                            $taskGroups->put($key, (object) ['step' => $key, 'role' => $task->role, 'rows' => collect()]);
                        }
                        $taskGroups->get($key)->rows->push((object) [
                            'task' => $task,
                            'status' => $rowStatus,
                            'percent' => $rowPercent,
                        ]);
                    };

                    foreach ($myRoleTasks as $task) {
                        $activities = $task->activityList();
                        $done  = $task->activitiesDoneCount();
                        $total = count($activities);

                        if ($task->needs_revision) {
                            $rowStatus  = 'revision';
                            $rowPercent = $total > 0 ? (int) round(($done / $total) * 100) : 50;
                        } elseif ($total > 0 && $done > 0) {
                            $rowStatus  = 'in_progress';
                            $rowPercent = (int) round(($done / $total) * 100);
                        } else {
                            $rowStatus  = 'not_started';
                            $rowPercent = 0;
                        }
                        $addToGroup($task, $rowStatus, $rowPercent);
                    }
                    foreach ($myCompletedTasks as $task) {
                        $addToGroup($task, 'completed', 100);
                    }

                    // Numbered steps in order, the 'other' bucket last.
                    $taskGroups = $taskGroups->sortBy(fn ($g) => $g->step === 'other' ? PHP_INT_MAX : $g->step)->values();

                    $groupStatusMeta = [
                        'not_started' => ['label' => 'Not Started',    'badge' => 'bg-slate-100 text-slate-500', 'icon' => 'mdi:circle-outline'],
                        'in_progress' => ['label' => 'In Progress',    'badge' => 'bg-brand-soft text-brand',    'icon' => 'mdi:progress-clock'],
                        'revision'    => ['label' => 'Needs Revision', 'badge' => 'bg-amber-50 text-amber-700',  'icon' => 'mdi:message-alert-outline'],
                        'completed'   => ['label' => 'Completed',      'badge' => 'bg-emerald-50 text-emerald-600', 'icon' => 'mdi:check-decagram-outline'],
                    ];

                    // Each task card is labelled by its role's initials and its place in
                    // the group — 'FD TASK 1'. A role with no entry falls back to the
                    // first two letters of its key.
                    $roleTaskCodes = [
                        'front_desk'            => 'FD',
                        'room_management'       => 'RM',
                        'restaurant_management' => 'RS',
                        'housekeeping'          => 'HK',
                        'maintenance'           => 'MT',
                    ];
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl sm:text-[30px] font-extrabold tracking-tight text-slate-900 leading-tight">Manage Tasks</h2>
                        <p class="text-sm text-slate-500 mt-1">View and manage all tasks assigned to you under the current group assignment.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        @if(!empty($studentRoles))
                            {{-- The one filter the toolbar keeps: everything else the table
                                 used to be narrowed by (search, role) is gone in favour of
                                 the per-TASK grouping below. --}}
                            <select id="taskStatusFilter" onchange="filterTaskRows()"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand">
                                <option value="all">All</option>
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="revision">Needs Revision</option>
                                <option value="completed">Completed</option>
                            </select>

                            {{-- Two doors, two kinds of work. Customize is the website editor, so
                                 it only lists roles that own a page to design; Simulation is the
                                 hotel's day-to-day operations, which every role has. A member
                                 holding one role goes straight in; several roles get a menu. --}}
                            @php
                                $taskAreas = [
                                    [
                                        'id' => 'customizeAreaMenu',
                                        'label' => 'Customize',
                                        'icon' => 'mdi:palette-outline',
                                        'urlKey' => 'customize_url',
                                        'modules' => array_values(array_filter($myModules, fn ($m) => $m['editable'])),
                                        'class' => 'brand-gradient text-white border-transparent shadow-lg shadow-brand/20 hover:opacity-90',
                                    ],
                                    [
                                        'id' => 'simulationAreaMenu',
                                        'label' => 'Simulation',
                                        'icon' => 'mdi:bell-ring-outline',
                                        'urlKey' => 'simulation_url',
                                        'modules' => array_values(array_filter($myModules, fn ($m) => !empty($m['simulation_url']))),
                                        'class' => 'bg-white text-slate-700 border-slate-200 shadow-sm hover:border-brand/40 hover:text-brand',
                                    ],
                                ];
                            @endphp
                            @foreach($taskAreas as $area)
                                @continue(empty($area['modules']))
                                @if(count($area['modules']) === 1)
                                    <a href="{{ $area['modules'][0][$area['urlKey']] }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 border text-xs font-bold rounded-xl transition {{ $area['class'] }}">
                                        <span class="iconify text-base" data-icon="{{ $area['icon'] }}"></span>
                                        {{ $area['label'] }}
                                    </a>
                                @else
                                    <div class="relative" data-task-area-menu>
                                        <button type="button" onclick="toggleTaskAreaMenu('{{ $area['id'] }}')"
                                                aria-haspopup="true" aria-controls="{{ $area['id'] }}"
                                                class="inline-flex items-center gap-2 px-4 py-2 border text-xs font-bold rounded-xl transition {{ $area['class'] }}">
                                            <span class="iconify text-base" data-icon="{{ $area['icon'] }}"></span>
                                            {{ $area['label'] }}
                                            <span class="iconify text-sm" data-icon="mdi:chevron-down"></span>
                                        </button>
                                        <div id="{{ $area['id'] }}" hidden
                                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-slate-100 shadow-xl py-1.5 z-30">
                                            <p class="px-3 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Choose a role</p>
                                            @foreach($area['modules'] as $module)
                                                <a href="{{ $module[$area['urlKey']] }}"
                                                   class="flex items-center gap-2 px-3 py-2 text-[13px] font-semibold text-slate-600 hover:bg-slate-50 hover:text-brand">
                                                    <span class="iconify text-base" data-icon="{{ $roleIcons[$module['role']] ?? 'mdi:clipboard-text-outline' }}"></span>
                                                    {{ $module['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                @if(!empty($studentRoles))
                    @foreach($taskGroups as $taskGroup)
                        @php
                            // Active work first (the concept task sorts first among those
                            // via conceptFirst()), completed work after.
                            $groupRows = $taskGroup->rows->sortBy(fn ($r) => $r->status === 'completed' ? 1 : 0)->values();

                            $groupTotal       = $groupRows->count();
                            $groupDone        = $groupRows->where('status', 'completed')->count();
                            $groupHasRevision = $groupRows->contains(fn ($r) => $r->status === 'revision');
                            $groupPercent     = $groupTotal > 0 ? (int) round(($groupDone / $groupTotal) * 100) : 0;

                            if ($groupTotal > 0 && $groupDone === $groupTotal) {
                                $groupStatus = 'completed';
                            } elseif ($groupHasRevision) {
                                $groupStatus = 'revision';
                            } elseif ($groupRows->every(fn ($r) => $r->status === 'not_started')) {
                                $groupStatus = 'not_started';
                            } else {
                                $groupStatus = 'in_progress';
                            }

                            $groupDue = $groupRows->pluck('task.due_date')->filter()->max();

                            $groupLabel     = $taskGroup->step === 'other' ? 'OTHER TASKS' : ('TASK ' . str_pad($taskGroup->step + 1, 2, '0', STR_PAD_LEFT));
                            $groupRole      = is_int($taskGroup->step) ? (\App\Support\TaskChecklist::STEP_ROLES[$taskGroup->step] ?? $taskGroup->role) : $taskGroup->role;
                            $groupRoleLabel = $homeRoleLabels[$groupRole] ?? ucfirst(str_replace('_', ' ', $groupRole));
                            $groupMeta      = $groupStatusMeta[$groupStatus];

                            // Ring geometry: only the dash offset varies with progress.
                            $ringRadius        = 26;
                            $ringCircumference = round(2 * M_PI * $ringRadius, 2);
                            $ringOffset        = round($ringCircumference * (1 - $groupPercent / 100), 2);

                            $taskRowIndex = 0;
                        @endphp
                        <section data-task-group data-group-status="{{ $groupStatus }}" class="space-y-4">
                            {{-- Overall summary: one rounded card per TASK group, above its own cards. --}}
                            <div class="rounded-3xl border border-pink-100 bg-gradient-to-r from-brand-soft to-white px-5 sm:px-7 py-6">
                                <div class="grid grid-cols-1 lg:grid-cols-[1.7fr_1fr_1fr_1fr] gap-6 lg:gap-0">
                                    <div class="flex items-center lg:pr-6">
                                        <div class="min-w-0">
                                            <p class="text-lg font-extrabold text-slate-900 tracking-wide">{{ $groupLabel }}</p>
                                            <span class="inline-flex items-center px-2.5 py-1 mt-1 rounded-lg text-[11px] font-bold {{ $homeTint($groupRole, 'bg') }} {{ $homeTint($groupRole, 'text') }}">
                                                {{ $groupRoleLabel }}
                                            </span>
                                            <p class="text-[12px] text-slate-500 mt-2">Complete all the tasks below to fulfill the requirements for {{ $groupLabel }}.</p>
                                        </div>
                                    </div>
                                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Progress %</p>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <svg width="56" height="56" viewBox="0 0 60 60" class="shrink-0 -rotate-90">
                                                <circle cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#F1F5F9" stroke-width="6"></circle>
                                                <circle data-summary-ring cx="30" cy="30" r="{{ $ringRadius }}" fill="none" stroke="#EC4899" stroke-width="6"
                                                        stroke-linecap="round" stroke-dasharray="{{ $ringCircumference }}" stroke-dashoffset="{{ $ringOffset }}"></circle>
                                            </svg>
                                            <div>
                                                <p class="text-2xl font-extrabold text-slate-900 leading-none" data-summary-percent>{{ $groupPercent }}%</p>
                                                <p class="text-[12px] text-slate-500 mt-1" data-summary-count>{{ $groupDone }} of {{ $groupTotal }} tasks completed</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                                        <span data-summary-status
                                              class="inline-flex items-center gap-1.5 mt-2 w-fit px-3 py-1.5 rounded-lg text-[12px] font-bold {{ $groupMeta['badge'] }}">
                                            <span class="iconify text-sm" data-icon="{{ $groupMeta['icon'] }}"></span>
                                            {{ $groupMeta['label'] }}
                                        </span>
                                    </div>
                                    <div class="lg:border-l lg:border-pink-100 lg:pl-6 flex flex-col justify-center">
                                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Due Date</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="iconify text-slate-400 text-base" data-icon="mdi:calendar-blank-outline"></span>
                                            @if($groupDue)
                                                <p class="text-[13px] font-semibold text-slate-700">{{ $groupDue->format('M j, Y') }}</p>
                                            @else
                                                <p class="text-[13px] text-slate-400">No due date set</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- A card grid, not a table: one card per task, two up on desktop
                                 and stacked below that. Both the grid and the empty state are
                                 rendered, and one of them is hidden — faculty can close the
                                 concept task while this page is open, and the poller has to be
                                 able to swap them without a reload. --}}
                            <div>
                                {{-- Also the drop target settleConceptTaskRow() appends the
                                     settled concept card to, so it lands in the same grid. --}}
                                <div data-completed-list class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
                                    @foreach($groupRows as $row)
                                        @php
                                            $task = $row->task;
                                            $taskRowIndex++;
                                            $isOverdue   = $task->due_date && $task->due_date->isPast();
                                            $isCompleted = $row->status === 'completed';
                                            /* Cards are per member; the submit button belongs on this
                                               student's own card, on an unclaimed one, and on a card whose
                                               named student has since given the role up — the team was
                                               reshuffled and the work would otherwise be unreachable by
                                               anybody. The submit route asks the same question. */
                                            $isMine        = !$isCompleted && \App\Support\TaskClaim::mayWork($task, auth()->user(), $groupMembership ?? null);
                                            $needsRevision = $row->status === 'revision';
                                            $rowMeta       = $groupStatusMeta[$row->status];
                                            $rowModule     = \App\Support\HotelTemplateBuilder::modulesForRoles([$task->role])[0] ?? null;
                                            $rowDetailId   = 'taskDetail' . $task->task_id;
                                            $rowBar        = $isCompleted ? 'bg-emerald-500' : ($needsRevision ? 'bg-amber-500' : $homeTint($task->role, 'bar'));
                                            // 'FD TASK 1' — the role's initials plus the task's place in
                                            // this group, which is what the card is known by.
                                            $rowCode       = ($roleTaskCodes[$task->role] ?? strtoupper(substr($task->role, 0, 2)))
                                                             . ' TASK ' . $taskRowIndex;
                                        @endphp
                                        {{-- The concept task is not a one-line tick: the whole proposal is
                                             written on it, so its card keeps #conceptPanelCard —
                                             paintTeamHeaderConcept() hides the card by that id once faculty
                                             has chosen, and settleConceptTaskRow() removes it outright. --}}
                                        <div @if($task->is_hotel_concept) id="conceptPanelCard" data-task-title="{{ $task->title }}" @endif
                                             data-task-card
                                             data-task-status="{{ $row->status }}"
                                             data-task-role="{{ $task->role }}"
                                             class="task-card bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                            <div class="flex flex-wrap items-start gap-3 px-4 sm:px-5 pt-4 pb-3">
                                                <div class="min-w-0 flex-1 basis-[140px]">
                                                    <p class="text-[14px] font-bold leading-snug {{ $isCompleted ? 'text-slate-400' : 'text-slate-800' }}">{{ $task->title }}</p>
                                                    <p data-task-code class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-1">{{ $rowCode }}</p>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0 ml-auto">
                                                    <span data-row-status-badge class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap {{ $rowMeta['badge'] }}">
                                                        {{ $rowMeta['label'] }}
                                                    </span>
                                                    {{-- Proceed opens the task itself: a website task opens the
                                                         editor, an operations task opens Simulation, and the
                                                         concept task goes to My Team, where the proposals are
                                                         written. A task with no area of its own opens its own
                                                         details, which is all there is to go to. --}}
                                                    @if($task->is_hotel_concept)
                                                        <button type="button" onclick="showSection('group')"
                                                                title="Write the concepts in My Team"
                                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                                            Proceed
                                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                                        </button>
                                                    @elseif($rowModule)
                                                        <a href="{{ $rowModule['editable'] ? $rowModule['customize_url'] : $rowModule['simulation_url'] }}"
                                                           title="Open {{ $rowModule['label'] }}"
                                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                                            Proceed
                                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                                        </a>
                                                    @else
                                                        {{-- No `this`: the tint toggleTaskDetail() puts on the eye
                                                             button would fight this one's own white text. --}}
                                                        <button type="button" onclick="toggleTaskDetail('{{ $rowDetailId }}')"
                                                                title="Open {{ $task->title }}"
                                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl brand-gradient text-white text-[12px] font-bold shadow-md shadow-brand/20 hover:opacity-90 transition whitespace-nowrap">
                                                            Proceed
                                                            <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- The rest of what the table columns used to carry: due date,
                                                 percent, the details toggle and the submit button. --}}
                                            <div class="flex items-center justify-between gap-3 px-4 sm:px-5 pb-3.5">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    <span class="iconify text-slate-300 text-sm shrink-0" data-icon="mdi:calendar-blank-outline"></span>
                                                    @if($task->due_date)
                                                        <p class="text-[11px] font-semibold truncate {{ $isOverdue && !$isCompleted ? 'text-red-500' : 'text-slate-500' }}">
                                                            {{ $task->due_date->format('M j, Y') }} &middot; {{ $task->due_date->format('g:i A') }}
                                                        </p>
                                                    @else
                                                        <p class="text-[11px] font-semibold text-slate-300">No due date</p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-[11px] font-extrabold text-slate-400 whitespace-nowrap" data-row-percent>{{ $row->percent }}%</span>
                                                    <button type="button" onclick="toggleTaskDetail('{{ $rowDetailId }}', this)"
                                                            title="View details" aria-label="View details for {{ $task->title }}"
                                                            class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-brand hover:border-brand/40 transition-colors">
                                                        <span class="iconify text-base" data-icon="mdi:eye-outline"></span>
                                                    </button>
                                                    @if($isCompleted)
                                                        <span class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                                                            <span class="iconify text-base" data-icon="mdi:check"></span>
                                                        </span>
                                                    @elseif($task->is_hotel_concept)
                                                        {{-- Nothing to tick here: the verdict closes this task, and
                                                             Proceed above is the only way into it. --}}
                                                    @elseif($isMine)
                                                        {{-- The real affordance: marks this task done, or resubmits
                                                             it when faculty has sent it back. --}}
                                                        <form method="POST" action="{{ route('students.tasks.complete', $task) }}" class="shrink-0 leading-none">
                                                            @csrf
                                                            <button type="submit"
                                                                    title="{{ $needsRevision ? 'Resubmit' : 'Mark as done' }}"
                                                                    aria-label="{{ $needsRevision ? 'Resubmit' : 'Mark' }} {{ $task->title }}"
                                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition
                                                                        {{ $needsRevision
                                                                            ? 'text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100'
                                                                            : 'text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10' }}">
                                                                <span class="iconify text-base" data-icon="{{ $needsRevision ? 'mdi:send-outline' : 'mdi:check' }}"></span>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] font-semibold text-slate-300 whitespace-nowrap"
                                                              title="Assigned to {{ $task->assignedTo?->name ?? 'a teammate' }}">{{ $task->assignedTo?->first_name ?? "Teammate's" }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="h-1 bg-slate-100">
                                                <div class="h-full {{ $rowBar }}" data-row-bar style="width: {{ $row->percent }}%"></div>
                                            </div>

                                            {{-- Detail drawer: description, faculty feedback, and for the
                                                 concept task the two proposals themselves. --}}
                                            <div class="task-detail px-4 sm:px-5 py-4 bg-slate-50/60 border-t border-slate-100 {{ $task->is_hotel_concept ? '' : 'hidden' }}" id="{{ $rowDetailId }}">
                                                <div class="space-y-3">
                                                    @if($task->description)
                                                        {{-- pre-line: the description carries the task's four activities as numbered
                                                             lines, and they have to survive as lines. --}}
                                                        <p class="text-[13px] text-slate-500 leading-relaxed whitespace-pre-line">{{ $task->description }}</p>
                                                    @endif
                                                    @php $activities = $task->activityList(); @endphp
                                                    @if($activities)
                                                        {{-- This role's own activity, broken into the four steps it
                                                             is done in. Ticked as the student works, and all four
                                                             have to be ticked before it can be handed in - the
                                                             submit route checks the same thing the button does. --}}
                                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5"
                                                             data-activity-panel data-task="{{ $task->task_id }}">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                                                                    <span class="iconify text-xs" data-icon="mdi:format-list-checks"></span>
                                                                    Steps
                                                                </p>
                                                                <p class="text-[10px] font-bold text-slate-400" data-activity-count>
                                                                    {{ $task->activitiesDoneCount() }} of {{ count($activities) }} done
                                                                </p>
                                                            </div>
                                                            <ul class="mt-2 space-y-1.5">
                                                                @foreach($activities as $i => $activity)
                                                                    <li class="flex items-start gap-2">
                                                                        <input type="checkbox"
                                                                               class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30 disabled:opacity-50"
                                                                               data-activity-check
                                                                               data-index="{{ $i }}"
                                                                               @checked($activity['done'])
                                                                               @disabled($task->status !== 'active')>
                                                                        <span class="text-[13px] leading-relaxed {{ $activity['done'] ? 'text-slate-400 line-through' : 'text-slate-600' }}"
                                                                              data-activity-text>{{ $i + 1 }}. {{ $activity['text'] }}</span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            @if($task->status === 'active')
                                                                <p class="text-[11px] text-slate-400 mt-2" data-activity-hint>
                                                                    {{ $task->activitiesComplete()
                                                                        ? 'All four done - you can submit this task.'
                                                                        : 'Tick each step as you finish it. All four are needed before you can submit.' }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($needsRevision)
                                                        {{-- Sent back by faculty: active again, but carrying feedback. --}}
                                                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                                                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                                                                <span class="iconify text-xs" data-icon="mdi:message-alert-outline"></span>
                                                                Faculty feedback{{ $task->revision_count > 1 ? ' · revision ' . $task->revision_count : '' }}
                                                            </p>
                                                            <p class="text-xs text-amber-800 mt-1 whitespace-pre-line">{{ $task->feedback }}</p>
                                                            @if($task->feedback_at)
                                                                <p class="text-[10px] text-amber-600 mt-1">{{ $task->feedback_at->diffForHumans() }}</p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($task->is_hotel_concept)
                                                        {{-- The panel itself is in My Team, where the concepts are
                                                             written; two containers with the same id would leave
                                                             paintHotelConcepts() painting only whichever the browser
                                                             matched first. --}}
                                                        <button type="button" onclick="showSection('group')"
                                                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-[13px] font-bold text-slate-700 hover:bg-slate-50 hover:text-brand transition-colors">
                                                            <span class="iconify text-base" data-icon="mdi:lightbulb-outline"></span>
                                                            Write the concepts in My Team
                                                        </button>
                                                    @elseif(!$task->description && !$needsRevision)
                                                        <p class="text-[13px] text-slate-400">No extra details on this task.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="text-[13px] text-slate-500 mt-4">
                                    Showing <span data-group-visible>{{ $groupTotal }}</span> of {{ $groupTotal }} tasks under {{ $groupLabel }}
                                </p>
                            </div>
                        </section>
                    @endforeach

                    <div id="taskFilterEmpty" class="bg-white rounded-2xl border border-slate-100 px-6 py-10 text-center hidden">
                        <p class="text-sm font-bold text-slate-500">No tasks match this filter</p>
                        <p class="text-xs text-slate-400 mt-1">Pick a different status.</p>
                    </div>

                    <div id="activeTasksEmpty" class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center{{ $myRoleTasks->count() > 0 ? ' hidden' : '' }}">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="iconify text-emerald-400 text-3xl" data-icon="mdi:check-decagram-outline"></span>
                        </div>
                        <p class="text-base font-bold text-slate-600">All tasks completed!</p>
                        <p class="text-sm text-slate-400 mt-1">Great job — no pending tasks for your role.</p>
                    </div>
                @else
                    <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-question-outline"></span>
                        </div>
                        <p class="text-base font-bold text-slate-500">No role assigned</p>
                        <p class="text-sm text-slate-400 mt-1">Once your faculty assigns a role, your tasks will appear here.</p>
                    </div>
                @endif
            </div>

            {{-- ══════════════ ACTIVITY LOGS SECTION ══════════════ --}}
            <div id="activity-section" class="section-content hidden fade-in space-y-4">
                @php
                    $roleLabels = [
                        'front_desk' => 'Front Desk',
                        'restaurant_management' => 'Restaurant',
                        'room_management' => 'Room Mgmt',
                        'maintenance' => 'Maintenance',
                        'housekeeping' => 'Housekeeping',
                    ];
                @endphp

                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mb-0.5">Activity Logs</h2>
                    <p class="text-sm text-slate-400">Your own recorded activity only — teammates' logs are not shown here.</p>
                </div>

                {{-- Centralized activity_logs: the same table the faculty and dean portals read. --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">My Activity</p>
                        <span class="text-[11px] font-semibold text-slate-400">{{ ($myActivityLogs ?? collect())->count() }} entries</span>
                    </div>
                    <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
                        @forelse(($myActivityLogs ?? collect()) as $log)
                            <div class="px-5 py-3.5 flex items-start gap-3 hover:bg-slate-50/70 transition">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-brand-soft text-brand">
                                    <span class="iconify text-lg" data-icon="mdi:clipboard-text-clock-outline"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800">{{ $log['activity_label'] ?? $log['activity'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $log['description'] ?: '—' }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $log['created_at'] }}{{ $log['created_at_human'] ? ' · ' . $log['created_at_human'] : '' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-400">No activity recorded yet</p>
                                <p class="text-xs text-slate-300 mt-1">Logins, task submissions and saved work will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">My Tasks</p>
                        <span class="text-[11px] font-semibold text-slate-400">{{ ($selfActivityLogs ?? collect())->count() }} entries</span>
                    </div>
                    <div class="divide-y divide-slate-50 max-h-[560px] overflow-y-auto">
                        @forelse(($selfActivityLogs ?? collect()) as $task)
                            <div class="px-5 py-3.5 flex items-start gap-3 hover:bg-slate-50/70 transition">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $task->status === 'archived' ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500' }}">
                                    <span class="iconify text-lg" data-icon="{{ $task->status === 'archived' ? 'mdi:check-circle-outline' : 'mdi:clipboard-plus-outline' }}"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800 truncate">
                                        {{ $task->status === 'archived' ? 'Completed' : 'Assigned' }}: {{ $task->title }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $roleLabels[$task->role] ?? $task->role }}
                                        · {{ optional($task->updated_at)->diffForHumans() }}
                                    </p>
                                </div>
                                @if($task->status === 'active')
                                    <form method="POST" action="{{ route('students.tasks.complete', $task) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-brand-soft text-brand border border-brand/10 hover:bg-brand/10 transition">
                                            Complete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <p class="text-sm font-semibold text-slate-400">No activity yet</p>
                                <p class="text-xs text-slate-300 mt-1">Your assigned and completed tasks will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ══════════════ REPORTS SECTION ══════════════ --}}
            <div id="reports-section" class="section-content hidden fade-in space-y-4">
                @php
                    $roleLabels = [
                        'front_desk' => 'Front Desk',
                        'restaurant_management' => 'Restaurant',
                        'room_management' => 'Room Mgmt',
                        'maintenance' => 'Maintenance',
                        'housekeeping' => 'Housekeeping',
                    ];
                    $selfCompleted = ($selfActivityLogs ?? collect())->where('status', 'archived');
                    $teamCompleted = ($teamActivityLogs ?? collect())->where('status', 'archived');
                    $selfAssigned = ($selfActivityLogs ?? collect())->where('status', 'active');
                    $teamAssigned = ($teamActivityLogs ?? collect())->where('status', 'active');
                    $selfTotal = $selfCompleted->count() + $selfAssigned->count();
                    $teamTotal = $teamCompleted->count() + $teamAssigned->count();
                    $selfRate = $selfTotal > 0 ? round(($selfCompleted->count() / $selfTotal) * 100) : 0;
                    $teamRate = $teamTotal > 0 ? round(($teamCompleted->count() / $teamTotal) * 100) : 0;
                    $selfByRole = $selfCompleted->groupBy('role')->map->count();
                    $teamByRole = $teamCompleted->groupBy('role')->map->count();
                    $maxSelfRole = max(1, (int) ($selfByRole->max() ?: 1));
                    $maxTeamRole = max(1, (int) ($teamByRole->max() ?: 1));
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mb-0.5">Reports</h2>
                        <p class="text-sm text-slate-400">Completed task assignment reports for yourself and your team.</p>
                    </div>
                    <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl p-1">
                        <button type="button" id="report-tab-self" onclick="switchReportTab('self')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-brand text-white">Individual</button>
                        <button type="button" id="report-tab-team" onclick="switchReportTab('team')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 hover:text-slate-800">Team</button>
                    </div>
                </div>

                <div id="report-panel-self" class="space-y-4">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completed</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $selfCompleted->count() }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $selfAssigned->count() }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $selfTotal }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completion</p>
                            <p class="text-2xl font-extrabold text-brand mt-1">{{ $selfRate }}%</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-slate-800 mb-4">My Completed by Role</h3>
                        <div class="space-y-3">
                            @forelse($selfByRole as $role => $count)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-semibold text-slate-600">{{ $roleLabels[$role] ?? $role }}</span>
                                        <span class="font-bold text-slate-800">{{ $count }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-brand" style="width: {{ round(($count / $maxSelfRole) * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">No completed tasks for your roles yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">My Completed Assignments</h3>
                        </div>
                        <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
                            @forelse($selfCompleted as $task)
                                <div class="px-5 py-3.5 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                                        <span class="iconify text-lg" data-icon="mdi:check-circle-outline"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $task->title }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $roleLabels[$task->role] ?? $task->role }} · {{ optional($task->updated_at)->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-sm text-slate-400 font-semibold">No completed assignments yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div id="report-panel-team" class="space-y-4 hidden">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completed</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $teamCompleted->count() }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $teamAssigned->count() }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $teamTotal }}</p>
                        </div>
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completion</p>
                            <p class="text-2xl font-extrabold text-brand mt-1">{{ $teamRate }}%</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-slate-800 mb-4">Team Completed by Role</h3>
                        <div class="space-y-3">
                            @forelse($teamByRole as $role => $count)
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-semibold text-slate-600">{{ $roleLabels[$role] ?? $role }}</span>
                                        <span class="font-bold text-slate-800">{{ $count }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-plum-accent" style="width: {{ round(($count / $maxTeamRole) * 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">No team completions yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">Team Completed Assignments</h3>
                        </div>
                        <div class="divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
                            @forelse($teamCompleted as $task)
                                <div class="px-5 py-3.5 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                                        <span class="iconify text-lg" data-icon="mdi:check-circle-outline"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $task->title }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $roleLabels[$task->role] ?? $task->role }} · {{ optional($task->updated_at)->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-sm text-slate-400 font-semibold">No team completed assignments yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════ MY PROFILE ═══════
                 What the student may change is what describes them. Their student
                 number, block, adviser, class, team and roles are the school's
                 record and their faculty's to set, so they are shown read-only. --}}
            <div id="profile-section" class="section-content hidden fade-in space-y-4">
                @php
                    $pUser = auth()->user();
                    $pAdviser = $student?->faculty?->user;
                @endphp

                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">My Profile</h2>

                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-center gap-2">
                        <span class="iconify text-emerald-600 text-lg" data-icon="mdi:check-circle-outline"></span>
                        <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Editable --}}
                    <form method="POST" action="{{ route('students.profile.update') }}" enctype="multipart/form-data"
                          class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        @csrf
                        @method('PUT')

                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">Account Details</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">This is how your name appears to your team and your faculty.</p>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="flex items-center gap-4">
                                <img src="{{ $pUser?->avatar_url }}" alt="{{ $studentDisplayName }}"
                                     class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shrink-0">
                                <div class="min-w-0">
                                    <label for="studentAvatar" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                        <span class="iconify text-sm" data-icon="mdi:camera-outline"></span> Change photo
                                    </label>
                                    <input id="studentAvatar" type="file" name="avatar" accept="image/*" class="hidden">
                                    <p class="text-[11px] text-slate-400 mt-1">JPG, PNG, WEBP or GIF · up to 2 MB</p>
                                    @if ($pUser?->avatar)
                                        <label class="mt-1.5 inline-flex items-center gap-1.5 text-[11px] font-semibold text-rose-600 cursor-pointer">
                                            <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-300 text-rose-500">
                                            Remove current photo
                                        </label>
                                    @endif
                                    @error('avatar')<p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ([
                                    ['first_name', 'First name', $pUser?->first_name, true],
                                    ['middle_name', 'Middle name', $pUser?->middle_name, false],
                                    ['last_name', 'Last name', $pUser?->last_name, true],
                                ] as [$field, $label, $value, $required])
                                    <div>
                                        <label for="student_{{ $field }}" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                            {{ $label }} @if(!$required)<span class="text-slate-300 normal-case font-semibold">(optional)</span>@endif
                                        </label>
                                        <input id="student_{{ $field }}" type="text" name="{{ $field }}" value="{{ old($field, $value) }}" @if($required) required @endif
                                               class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                                        @error($field)<p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="student_email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                                    <input id="student_email" type="email" name="email" value="{{ old('email', $pUser?->email) }}" required
                                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                                    @error('email')<p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="student_phone" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                        Phone <span class="text-slate-300 normal-case font-semibold">(optional)</span>
                                    </label>
                                    <input id="student_phone" type="text" name="phone_number" value="{{ old('phone_number', $pUser?->phone_number ?? $student?->phone_number) }}"
                                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                                    @error('phone_number')<p class="text-[11px] font-semibold text-rose-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-brand text-white text-xs font-bold hover:opacity-90 transition inline-flex items-center gap-1.5">
                                <span class="iconify text-sm" data-icon="mdi:content-save-outline"></span> Save changes
                            </button>
                        </div>
                    </form>

                    {{-- Read-only school record --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden self-start">
                        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60">
                            <h3 class="text-sm font-bold text-slate-800">School Record</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Set by your faculty — ask them if something is wrong.</p>
                        </div>
                        <dl class="divide-y divide-slate-50">
                            @foreach ([
                                ['Student number', $student?->student_number],
                                // The block is the class row this student sits in.
                                // user_information.block is the faculty's own block
                                // letter and is never set on a student, so reading it
                                // here only ever produced a dash.
                                ['Block', $studentClass?->name],
                                ['Status', $student?->status ? ucfirst($student->status) : null],
                                ['Adviser', $pAdviser?->name],
                                ['Team', $group?->name],
                            ] as [$label, $value])
                                <div class="px-5 py-2.5 flex items-center justify-between gap-3">
                                    <dt class="text-[11px] font-semibold text-slate-400">{{ $label }}</dt>
                                    <dd class="text-xs font-bold text-slate-700 text-right truncate">{{ $value ?: '—' }}</dd>
                                </div>
                            @endforeach
                            <div class="px-5 py-2.5">
                                <dt class="text-[11px] font-semibold text-slate-400 mb-1.5">Roles</dt>
                                <dd class="flex flex-wrap gap-1.5">
                                    @forelse ($studentRoles ?? [] as $role)
                                        <span class="px-2 py-0.5 rounded-full bg-brand-soft text-brand text-[10px] font-bold">
                                            {{ \App\Support\HotelTemplateBuilder::ROLES[$role] ?? $role }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">No role assigned yet</span>
                                    @endforelse
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @if($group)
        {{-- Edit dialog behind each slot's Propose/Edit button. Saves over fetch so the
             task row, the team header and the history all move together, without throwing
             the member back to the top of the dashboard.

             It lives out here rather than inside a section: every .section-content is
             hidden when another section is showing, and a dialog nested in one would be
             hidden with it.

             Rendered for every member of a team rather than gated on the edit right: a
             save or a faculty verdict can open editing up while the page is still open,
             and the button that reveals this dialog is repainted from that response. The
             controller is the real gate. --}}
        <div id="hotelConceptModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeHotelConceptModal()"></div>
            <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl border border-slate-100 w-[92vw] max-w-xl max-h-[90vh] overflow-y-auto">
                <div class="brand-gradient px-5 py-4 flex items-center justify-between gap-3 sticky top-0 z-10">
                    <div class="min-w-0">
                        <p class="text-white/60 text-[9px] font-bold uppercase tracking-[0.15em]">Hotel Concepts · Your Task</p>
                        <h4 id="hotelConceptModalTitle" class="text-base font-extrabold text-white">Hotel Concept</h4>
                    </div>
                    <button type="button" onclick="closeHotelConceptModal()"
                        class="w-8 h-8 rounded-full text-white/70 hover:text-white hover:bg-white/15 transition flex items-center justify-center shrink-0">
                        <span class="iconify text-xl" data-icon="mdi:close"></span>
                    </button>
                </div>

                {{-- One dialog serves both concepts; the slot says which one is
                     open, and openHotelConceptModal() fills the fields from it. --}}
                <form id="hotelConceptForm" method="POST" action="{{ route('students.hotel-concept.store') }}" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="slot" value="">
                    <div id="hotelConceptFormError" class="hidden rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Hotel Concept Title</label>
                        <input name="title" type="text" required maxlength="150"
                            placeholder="e.g. Seaside Serenity Resort"
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>
                    <div>
                        {{-- The line the hotel introduces itself with. It is printed
                             above the headline on the landing page and inside every
                             footer, so it is a line and not a paragraph. --}}
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            Tagline <span class="text-slate-300 font-semibold normal-case tracking-normal">— optional</span>
                        </label>
                        <input name="tagline" type="text" maxlength="120"
                            placeholder="e.g. Where the sea meets stillness"
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        <p class="text-[10px] text-slate-400 mt-1">Shown above the headline on your hotel's landing page and in its footer.</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Hotel Type</label>
                        <select name="hotel_type" required
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                            <option value="">Select hotel type</option>
                            @foreach(\App\Models\HotelConcept::HOTEL_TYPES as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Description</label>
                        <textarea name="description" required rows="5" maxlength="5000"
                            placeholder="What the hotel is, who it serves, what makes it different."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" onclick="closeHotelConceptModal()"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition">Cancel</button>
                        <button type="submit" id="hotelConceptSubmit"
                            class="px-4 py-2 brand-gradient text-white rounded-xl text-xs font-bold shadow-md shadow-brand/20 hover:opacity-90 transition disabled:opacity-60">
                            Save concept
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Member Activity Logs Modal -->
    <div id="memberActivityModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeMemberActivityModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-h-[90vh] flex flex-col" style="max-width: 36rem;">
            <div class="bg-brand-soft px-4 py-3 border-b border-brand/10 flex justify-between items-center rounded-t-2xl flex-shrink-0">
                <div class="min-w-0">
                    <h4 id="memberActivityModalTitle" class="font-bold text-brand text-sm truncate">Activity Logs</h4>
                    <p id="memberActivityModalSubtitle" class="text-[11px] text-slate-500 truncate"></p>
                </div>
                <button type="button" onclick="closeMemberActivityModal()" class="text-slate-400 hover:text-brand hover:bg-white w-7 h-7 rounded-full transition flex items-center justify-center shrink-0" aria-label="Close">
                    <span class="iconify text-lg" data-icon="mdi:close"></span>
                </button>
            </div>
            <div id="memberActivityModalBody" class="overflow-y-auto flex-1 divide-y divide-slate-50"></div>
            <div class="px-4 py-2.5 border-t border-slate-100 flex justify-end rounded-b-2xl flex-shrink-0 bg-slate-50/50">
                <button type="button" onclick="closeMemberActivityModal()" class="px-3.5 py-1.5 rounded-lg bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition font-semibold text-xs">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        // ── Section switching ──
        function showSection(section) {
            document.querySelectorAll('.section-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(section + '-section').classList.remove('hidden');
            // Re-trigger fade animation
            const target = document.getElementById(section + '-section');
            target.classList.remove('fade-in');
            void target.offsetWidth;
            target.classList.add('fade-in');

            // Update sidebar active state
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.classList.remove('active');
            });
            const activeNav = document.getElementById('nav-' + section);
            if (activeNav) {
                activeNav.classList.add('active');
            }

            // Update breadcrumb
            const labels = { home: 'Dashboard', group: 'My Group', tasks: 'Tasks', activity: 'Activity Logs', reports: 'Reports', profile: 'My Profile' };
            document.getElementById('breadcrumb-current').textContent = labels[section] || 'Dashboard';

            try {
                const url = new URL(window.location.href);
                if (section === 'home') {
                    url.searchParams.delete('section');
                } else {
                    url.searchParams.set('section', section);
                }
                history.replaceState(null, '', url);
            } catch (e) { /* ignore */ }
        }

        /* The concept form posts and redirects back here, so the section the
           student was reading has to survive the round trip. */

        /* ── Live greeting ──────────────────────────────────────────────────
           Server-rendered in Manila time on load; from then on this keeps it
           turning over at 5am/12pm/6pm Manila time without a refresh, reading
           the clock through Intl rather than the server's own timezone. */
        const HOME_FIRST_NAME = @json($homeFirstName);

        function homeGreetingWord() {
            const parts = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Manila', hour: 'numeric', hour12: false,
            }).formatToParts(new Date());
            // Some engines report midnight as hour "24" under hour12:false.
            const hour = Number(parts.find((p) => p.type === 'hour')?.value || 0) % 24;

            if (hour >= 5 && hour < 12) return 'Good morning';
            if (hour >= 12 && hour < 18) return 'Good afternoon';
            return 'Good evening';
        }

        function refreshHomeGreeting() {
            const el = document.getElementById('homeGreetingText');
            if (el) el.textContent = homeGreetingWord() + ', ' + HOME_FIRST_NAME + '!';
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshHomeGreeting();
            setInterval(refreshHomeGreeting, 60000);
        });

        document.addEventListener('DOMContentLoaded', () => {
            const logoutForm = document.getElementById('logoutForm');
            if (!logoutForm) return;
            logoutForm.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Log out?',
                    text: 'You will need to sign in again to access your dashboard.',
                    icon: 'warning',
                    iconColor: '#DC2626',
                    showCancelButton: true,
                    confirmButtonText: 'Logout',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        logoutForm.submit();
                    }
                });
            });
        });

        (function restoreSection() {
            let section = null;
            try {
                section = new URL(window.location.href).searchParams.get('section');
            } catch (e) { /* ignore */ }

            @php
                // Which fields failed says which form the student was in, so a
                // rejected profile save reopens Profile rather than the concept
                // panel the group default assumes.
                $profileFields = ['first_name', 'middle_name', 'last_name', 'email', 'phone_number', 'avatar'];
                $profileHasErrors = collect($profileFields)->contains(fn ($f) => $errors->has($f));
            @endphp
            @if ($profileHasErrors)
                section = 'profile';
            @elseif (session('success') || $errors->any())
                // Only when the URL did not already name one — the profile save
                // redirects with ?section=profile and must keep it.
                if (!section) section = 'group';
            @endif

            if (section && document.getElementById(section + '-section')) {
                document.addEventListener('DOMContentLoaded', () => showSection(section));
            }
        })();

        /* ── Hotel concepts: the panel is the source of truth on screen ──────
           A team owes two concepts. Saving one, or handing both in, returns the whole
           team payload — both slots, both histories, and which buttons this member is
           allowed to see — so the panel is rebuilt from what the database actually
           holds rather than from what was typed. The server decides the permissions;
           this script only draws them.

           The initial payload is the same shape the endpoints return, so there is one
           renderer instead of a server copy of this markup and a client copy that
           drift apart. */
        @php
            // Built here rather than inline: @json() cannot parse a multi-line
            // expression, and silently truncates it.
            $conceptPayloadJson = $conceptPayload;
        @endphp
        let conceptState = @json($conceptPayloadJson);

        /** The slot currently open in the dialog. */
        let editingSlot = null;

        function conceptSlot(slot) {
            return (conceptState?.slots || []).find((entry) => Number(entry.slot) === Number(slot)) || null;
        }

        function openHotelConceptModal(slot) {
            const modal = document.getElementById('hotelConceptModal');
            const form = document.getElementById('hotelConceptForm');
            const entry = conceptSlot(slot);
            if (!modal || !form || !entry) return;

            editingSlot = Number(slot);
            const concept = entry.concept;

            // Always open on the stored concept, not on a half-typed abandoned edit.
            form.querySelector('input[name="slot"]').value = editingSlot;
            form.querySelector('input[name="title"]').value = concept?.title ?? '';
            form.querySelector('input[name="tagline"]').value = concept?.tagline ?? '';
            form.querySelector('select[name="hotel_type"]').value = concept?.hotel_type ?? '';
            form.querySelector('textarea[name="description"]').value = concept?.description ?? '';

            const title = document.getElementById('hotelConceptModalTitle');
            if (title) {
                title.textContent = (concept ? 'Edit ' : 'Propose ') + entry.slot_label;
            }
            hideHotelConceptError();

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => form.querySelector('input[name="title"]')?.focus(), 50);
        }

        function closeHotelConceptModal() {
            const modal = document.getElementById('hotelConceptModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function hideHotelConceptError() {
            document.getElementById('hotelConceptFormError')?.classList.add('hidden');
        }

        function showHotelConceptError(message) {
            const box = document.getElementById('hotelConceptFormError');
            if (!box) return;
            box.textContent = message;
            box.classList.remove('hidden');
        }

        function conceptEscape(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        /* Badge colour per workflow state, so a member can read where each concept
           stands at a glance. */
        const CONCEPT_BADGE_CLASSES = {
            draft: 'bg-slate-100 text-slate-600 border-slate-200',
            submitted: 'bg-amber-50 text-amber-700 border-amber-200',
            needs_revision: 'bg-rose-50 text-rose-700 border-rose-200',
            approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            not_selected: 'bg-slate-100 text-slate-500 border-slate-300',
        };

        const CONCEPT_NOTICE_CLASSES = {
            submitted: 'border-amber-200 bg-amber-50 text-amber-700',
            needs_revision: 'border-rose-200 bg-rose-50 text-rose-700',
            approved: 'border-emerald-200 bg-emerald-50 text-emerald-700',
            not_selected: 'border-slate-200 bg-slate-50 text-slate-500',
        };

        const CONCEPT_NOTICE_TEXT = {
            submitted: 'This Hotel Concept has been submitted and is awaiting faculty review. Editing is temporarily disabled.',
            needs_revision: 'Your faculty asked for changes.',
            approved: 'Your faculty approved this concept. It is your official hotel concept and is no longer editable.',
        };

        /* Redraw both slots and the shared Submit button from a server payload. Every
           write returns the whole team, so a save on one concept also refreshes the
           other's lock state and the button that hands the pair in. Also repaints
           the header, since the same payload is what says whether a decision has
           been made. */
        function paintHotelConcepts(data) {
            if (data && Array.isArray(data.slots)) conceptState = data;

            const panel = document.getElementById('conceptPanel');
            if (panel) {
                panel.innerHTML = (conceptState?.slots || []).map(renderConceptSlot).join('');
            }

            const submitBtn = document.getElementById('conceptSubmitAllBtn');
            if (submitBtn) {
                submitBtn.classList.toggle('hidden', !conceptState?.can_submit);
                submitBtn.disabled = false;

                // Resubmit rather than Submit once any slot is answering a revision
                // request — same button, same action, different moment.
                const isResubmit = (conceptState?.slots || [])
                    .some((entry) => entry.concept?.status === 'needs_revision');
                const label = document.getElementById('conceptSubmitAllLabel');
                if (label) label.textContent = isResubmit ? 'Resubmit to Faculty' : 'Submit both to Faculty';
            }

            paintTeamHeaderConcept();
            settleConceptTaskRow();
        }

        /* ── Live concept state ────────────────────────────────────────────────
           The verdict is faculty's action on their own page, so nothing about it
           reaches this one on its own. Polling the same endpoint the page was drawn
           from keeps every part of the concept live — the badges, the faculty's
           words, the lock on the fields, the team heading, and the task row itself.

           Polled rather than pushed because that is what this app already does for
           presence, and the endpoint returns the whole team's payload, which is
           exactly what paintHotelConcepts() takes. */

        /* What the last paint saw, so a change can be told from a repeat. Seeded
           from the payload the page rendered with: the first poll after load should
           announce nothing. */
        let conceptStatusSeen = conceptStatusMap(conceptState);
        let conceptTaskSettled = false;

        function conceptStatusMap(data) {
            const seen = {};
            (data?.slots || []).forEach((entry) => {
                seen[String(entry.slot)] = entry.concept ? (entry.concept.status || 'draft') : null;
            });
            return seen;
        }

        /* The concept task closes on the verdict, not on a tick — so when the
           verdict lands its card has to leave Active Tasks the way a submitted task
           would have. Done in the DOM rather than by reloading: the point of the
           poll is that nobody has to refresh.

           Runs on every paint but acts once, because paintHotelConcepts() is also
           called after every save. */
        function settleConceptTaskRow() {
            const row = document.getElementById('conceptPanelCard');
            if (!row || conceptTaskSettled || !conceptState?.decided) return;

            conceptTaskSettled = true;

            const title = row.dataset.taskTitle || 'Propose Two Hotel Concepts';
            // Read off the card being replaced, so the settled card keeps the same
            // code rather than assuming where the concept task sits.
            const code = row.querySelector('[data-task-code]')?.textContent.trim() || 'FD TASK 1';
            const group = row.closest('[data-task-group]');
            row.remove();

            // Appended, not prepended: completed work sorts last in the grid, the
            // same order the server renders the group in.
            const list = group?.querySelector('[data-completed-list]');
            if (list) {
                const done = document.createElement('div');
                done.className = 'task-card bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden';
                done.dataset.taskCard = '';
                done.dataset.taskStatus = 'completed';
                done.innerHTML =
                    '<div class="flex flex-wrap items-start gap-3 px-4 sm:px-5 pt-4 pb-3">'
                        + '<div class="min-w-0 flex-1 basis-[140px]">'
                            + '<p class="text-[14px] font-bold leading-snug text-slate-400">' + conceptEscape(title) + '</p>'
                            + '<p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-1">' + conceptEscape(code) + '</p>'
                        + '</div>'
                        + '<div class="flex items-center gap-2 shrink-0 ml-auto">'
                            + '<span data-row-status-badge class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap bg-emerald-50 text-emerald-600">Completed</span>'
                        + '</div>'
                    + '</div>'
                    + '<div class="flex items-center justify-between gap-3 px-4 sm:px-5 pb-3.5">'
                        + '<div class="flex items-center gap-1.5 min-w-0">'
                            + '<span class="iconify text-slate-300 text-sm shrink-0" data-icon="mdi:calendar-blank-outline"></span>'
                            + '<p class="text-[11px] font-semibold text-slate-300">No due date</p>'
                        + '</div>'
                        + '<div class="flex items-center gap-2 shrink-0">'
                            + '<span class="text-[11px] font-extrabold text-slate-400 whitespace-nowrap" data-row-percent>100%</span>'
                            + '<span class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">'
                                + '<span class="iconify text-base" data-icon="mdi:check"></span>'
                            + '</span>'
                        + '</div>'
                    + '</div>'
                    + '<div class="h-1 bg-slate-100">'
                        + '<div class="h-full bg-emerald-500" data-row-bar style="width: 100%"></div>'
                    + '</div>';
                list.appendChild(done);
                if (window.Iconify && typeof window.Iconify.scan === 'function') {
                    window.Iconify.scan(done);
                }
            }

            if (group) refreshTaskGroupSummary(group);
            filterTaskRows();
            bumpTaskCounts(-1);
        }

        /* Every place the page prints a task count, moved together. They are marked
           rather than looked up by id because the same number is printed in four
           places — the nav badge, the two KPI tiles, and the pills over the list. */
        function bumpTaskCounts(activeDelta) {
            document.querySelectorAll('[data-task-count="active"]').forEach((el) => {
                el.textContent = String(Math.max(0, (parseInt(el.textContent, 10) || 0) + activeDelta));
            });
            document.querySelectorAll('[data-task-count="completed"]').forEach((el) => {
                el.textContent = String(Math.max(0, (parseInt(el.textContent, 10) || 0) - activeDelta));
            });

            const active = parseInt(
                document.querySelector('[data-task-count="active"]')?.textContent || '0', 10
            ) || 0;

            document.getElementById('navTasksBadge')?.classList.toggle('hidden', active === 0);
            document.getElementById('activeTasksEmpty')?.classList.toggle('hidden', active > 0);
        }

        /* Says what changed, once, when it changes. Without this the cards would
           quietly rearrange themselves under a member who was reading them. */
        function announceConceptChanges(data) {
            const now = conceptStatusMap(data);
            const messages = [];

            Object.keys(now).forEach((slot) => {
                const was = conceptStatusSeen[slot];
                const is = now[slot];
                if (is === was || !is) return;

                const label = 'Concept ' + slot;
                if (is === 'approved') messages.push(label + ' was approved by your faculty.');
                else if (is === 'needs_revision') messages.push(label + ' was sent back for revision.');
                else if (is === 'not_selected') messages.push('Your faculty chose the other concept.');
            });

            conceptStatusSeen = now;

            if (!messages.length) return;

            // A toast, not a dialog: the bell already carries the notification, and
            // the cards below have repainted themselves — this only has to catch the
            // eye of someone who was looking elsewhere on the page.
            const approved = messages.some((m) => m.indexOf('approved') !== -1);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: approved ? 'success' : 'info',
                title: approved ? 'Your concept was approved' : 'Your faculty answered',
                text: messages.join(' '),
                showConfirmButton: false,
                timer: 6000,
                timerProgressBar: true,
            });
        }

        async function syncHotelConcepts() {
            try {
                const res = await fetch(@json(route('students.hotel-concept.history')), {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) return;

                const data = await res.json();
                if (!data || !Array.isArray(data.slots)) return;

                // Announce before painting: the comparison is against what the member
                // is still looking at.
                announceConceptChanges(data);
                paintHotelConcepts(data);
                if (window.Iconify && typeof window.Iconify.scan === 'function') {
                    window.Iconify.scan(document.getElementById('conceptPanel') || document.body);
                }
            } catch (e) { /* a dropped poll is not worth telling anyone about */ }
        }

        /* The concept faculty chose, in the team header. Once one slot's payload
           comes back approved, that is the team's identity now: the heading stops
           being the group's name and becomes the concept's title, with its type on
           the line under it and its description below the whole header row. The
           group's own name drops to the eyebrow above, so the roster underneath is
           still identifiable.

           The concept task row over in Tasks has nothing left to propose either, so
           it hides. (The row usually goes on its own, since a verdict archives the
           task; the toggle covers the page that was already open when it landed.)

           Everything here is reversible on purpose — the heading reverts from its
           data-team-name — because a repaint runs on every save, not only after a
           verdict. */
        function paintTeamHeaderConcept() {
            const container = document.getElementById('teamHeaderConcept');
            const panelCard = document.getElementById('conceptPanelCard');
            const teamCard = document.getElementById('conceptTeamCard');
            const heading = document.getElementById('teamHeaderName');
            const eyebrow = document.getElementById('teamHeaderEyebrow');
            const typeLine = document.getElementById('teamHeaderType');

            const approved = (conceptState?.slots || [])
                .map((entry) => entry.concept)
                .find((concept) => concept && concept.status === 'approved');

            // Two places to put away once a verdict lands: the task row over in Tasks,
            // and the panel in My Team where the proposals were written.
            if (panelCard) panelCard.classList.toggle('hidden', !!approved);
            if (teamCard) teamCard.classList.toggle('hidden', !!approved);

            // The team name and its type line: swapped whether or not the description
            // container exists, so a page without one still renames.
            if (heading) {
                heading.textContent = approved
                    ? (approved.title || heading.dataset.teamName || '')
                    : (heading.dataset.teamName || heading.textContent);
            }

            if (eyebrow) {
                eyebrow.textContent = approved
                    ? (heading?.dataset.teamName || 'Official Hotel Concept')
                    : 'Hotel Management Simulation';
            }

            if (typeLine) {
                typeLine.textContent = approved ? (approved.hotel_type_label || '') : '';
                typeLine.classList.toggle('hidden', !approved || !approved.hotel_type_label);
            }

            if (!container) return;

            if (!approved) {
                container.classList.add('hidden');
                container.innerHTML = '';
                return;
            }

            // Descriptions run up to 5000 characters and the panel that used to hold
            // the full text is gone, so long ones clamp with a way to still read them.
            const description = approved.description || '';
            const isLong = description.length > 180;

            container.innerHTML =
                '<p class="text-slate-400 text-[9px] font-bold uppercase tracking-[0.15em]">Concept Description</p>'
                + '<p id="teamHeaderConceptDesc" class="text-[11px] text-slate-500 leading-relaxed mt-1 whitespace-pre-line' + (isLong ? ' line-clamp-3' : '') + '">'
                    + conceptEscape(description) + '</p>'
                + (isLong
                    ? '<button type="button" onclick="toggleTeamHeaderConceptDesc(this)" class="mt-1.5 text-[10px] font-bold text-brand hover:text-brand-dark underline underline-offset-2">Show more</button>'
                    : '');

            container.classList.remove('hidden');
        }

        function toggleTeamHeaderConceptDesc(button) {
            const desc = document.getElementById('teamHeaderConceptDesc');
            if (!desc) return;
            const stillClamped = desc.classList.toggle('line-clamp-3');
            button.textContent = stillClamped ? 'Show more' : 'Show less';
        }

        /* One concept card: where it stands, what it says, what faculty said about it,
           its own edit history, and — when this member is allowed — its Edit button. */
        function renderConceptSlot(entry) {
            const concept = entry.concept;
            const status = concept ? (concept.status || 'draft') : 'draft';

            const badge = concept
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] font-bold uppercase tracking-wider '
                    + (CONCEPT_BADGE_CLASSES[status] || CONCEPT_BADGE_CLASSES.draft) + '">'
                    + conceptEscape(concept.status_label) + '</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-full border border-dashed border-slate-300 text-slate-400 text-[9px] font-bold uppercase tracking-wider">Empty</span>';

            const rounds = concept ? Number(concept.revision_count || 0) : 0;
            const revisionBadge = rounds > 0
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[9px] font-bold uppercase tracking-wider">Revision '
                    + rounds + '</span>'
                : '';

            const editButton = entry.can_edit
                ? '<button type="button" onclick="openHotelConceptModal(' + Number(entry.slot) + ')"'
                    + ' class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10 transition">'
                    + '<span class="iconify text-[13px]" data-icon="mdi:pencil-outline"></span>'
                    + (concept ? 'Edit' : 'Propose')
                  + '</button>'
                : '';

            const officialRibbon = concept && status === 'approved'
                ? '<p class="inline-flex items-center gap-1 text-[9px] font-bold uppercase tracking-[0.15em] text-emerald-600 mt-1.5">'
                    + '<span class="iconify text-[12px]" data-icon="mdi:star-circle"></span> Official Hotel Concept</p>'
                : '';

            const body = concept
                ? officialRibbon
                    + '<h4 class="text-sm font-extrabold text-slate-800 mt-1">' + conceptEscape(concept.title) + '</h4>'
                    + (concept.tagline
                        ? '<p class="text-[11px] italic text-brand">' + conceptEscape(concept.tagline) + '</p>'
                        : '')
                    + '<p class="text-[11px] font-bold text-slate-500">' + conceptEscape(concept.hotel_type_label) + '</p>'
                    + '<p class="text-xs text-slate-500 leading-relaxed mt-1.5 whitespace-pre-line">' + conceptEscape(concept.description) + '</p>'
                : '<p class="text-sm font-bold text-slate-400 mt-1">Not proposed yet</p>'
                    + '<p class="text-xs text-slate-400 mt-1">'
                    + (entry.can_edit
                        ? 'Write this one to complete the pair.'
                        : 'The Front Desk members of this team propose it.')
                    + '</p>';

            const notice = (concept && CONCEPT_NOTICE_TEXT[status])
                ? '<div class="mt-2.5 rounded-xl border px-3 py-2 text-xs ' + (CONCEPT_NOTICE_CLASSES[status] || '') + '">'
                    + '<p class="font-bold">' + conceptEscape(CONCEPT_NOTICE_TEXT[status]) + '</p>'
                    + (concept.faculty_feedback
                        ? '<p class="mt-1 leading-relaxed whitespace-pre-line">' + conceptEscape(concept.faculty_feedback) + '</p>'
                        : '')
                  + '</div>'
                : '';

            const history = Array.isArray(entry.history) ? entry.history : [];

            return '<div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-3.5 flex flex-col">' +
                '<div class="flex items-start justify-between gap-2">' +
                    '<div class="min-w-0">' +
                        '<div class="flex items-center gap-1.5 flex-wrap">' +
                            '<p class="text-[9px] font-bold uppercase tracking-[0.15em] text-brand">' + conceptEscape(entry.slot_label) + '</p>' +
                            badge + revisionBadge +
                        '</div>' +
                        body +
                    '</div>' +
                    editButton +
                '</div>' +
                notice +
                '<div class="mt-3">' +
                    '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Edit History · '
                        + history.length + ' ' + (history.length === 1 ? 'entry' : 'entries') + '</p>' +
                    '<div class="rounded-xl border border-slate-100 bg-white divide-y divide-slate-100 max-h-56 overflow-y-auto">'
                        + renderConceptHistory(history) +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function renderConceptHistory(history) {
            if (history.length === 0) {
                return '<div class="px-3 py-6 text-center text-xs text-slate-400">No edits recorded yet.</div>';
            }

            return history.map(function (entry) {
                const changes = (entry.changes || []).map(function (change) {
                    return '<li class="text-[11px] text-slate-500">' +
                        '<span class="font-semibold text-slate-600">' + conceptEscape(change.label) + ':</span> ' +
                        '<span class="line-through text-slate-400">' + (conceptEscape(change.from) || '—') + '</span> ' +
                        '<span class="text-slate-400">to</span> ' +
                        '<span class="text-slate-700">' + conceptEscape(change.to) + '</span>' +
                    '</li>';
                }).join('');

                return '<div class="px-3 py-2.5">' +
                    '<div class="flex items-start justify-between gap-2 flex-wrap">' +
                        '<p class="text-xs font-bold text-slate-700">' + conceptEscape(entry.editor) +
                            ' <span class="font-semibold text-slate-400">— ' + conceptEscape(entry.action_label) + '</span></p>' +
                        '<span class="text-[10px] text-slate-400 shrink-0">' + conceptEscape(entry.created_at) +
                            ' · ' + conceptEscape(entry.created_at_human) + '</span>' +
                    '</div>' +
                    (changes
                        ? '<ul class="mt-1.5 space-y-1">' + changes + '</ul>'
                        : '<p class="mt-1.5 text-[11px] text-slate-500"><span class="font-semibold text-slate-600">'
                            + conceptEscape(entry.title) + '</span> · ' + conceptEscape(entry.hotel_type_label) + '</p>') +
                '</div>';
            }).join('');
        }

        /* Hand both concepts to faculty. Submitting locks them for the whole team
           immediately — nobody can edit again until faculty sends one back for
           revision, or approves one and closes the pair for good. */
        async function submitHotelConcepts() {
            const isResubmit = (conceptState?.slots || [])
                .some((entry) => entry.concept?.status === 'needs_revision');

            const confirmed = await Swal.fire({
                icon: 'question',
                title: isResubmit ? 'Resubmit your hotel concepts?' : 'Submit your hotel concepts?',
                text: 'This locks both concepts for your whole team until your faculty reviews them.',
                showCancelButton: true,
                confirmButtonText: isResubmit ? 'Resubmit' : 'Submit',
                confirmButtonColor: '#DB2777',
                cancelButtonText: 'Cancel',
            }).then(result => result.isConfirmed);

            if (!confirmed) {
                return;
            }

            const button = document.getElementById('conceptSubmitAllBtn');
            if (button) button.disabled = true;

            try {
                const response = await fetch(@json(route('students.hotel-concept.submit')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                    },
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Could not submit the hotel concepts.');
                }

                paintHotelConcepts(data);
                Swal.fire({ icon: 'success', title: 'Submitted', text: 'Your hotel concepts were sent to your faculty.', confirmButtonColor: '#DB2777' });
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Could not submit', text: error.message || 'Could not submit the hotel concepts.', confirmButtonColor: '#DB2777' });
                if (button) button.disabled = false;
            }
        }

        (function wireHotelConceptForm() {
            const form = document.getElementById('hotelConceptForm');

            // Draw the panel from the payload the server rendered with the page.
            paintHotelConcepts(null);

            // Faculty approve and send back from their own portal, so this page only
            // learns of a verdict by asking. Every 10s, and only for a member who
            // actually has concepts to watch.
            if ((conceptState?.slots || []).length) {
                setInterval(syncHotelConcepts, 10000);
            }

            if (!form) return;

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                hideHotelConceptError();

                const submit = document.getElementById('hotelConceptSubmit');
                const originalLabel = submit?.textContent;
                if (submit) {
                    submit.disabled = true;
                    submit.textContent = 'Saving…';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: new FormData(form),
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        // 422 carries Laravel's validation bag; 403 carries our own message.
                        const firstError = data.errors
                            ? Object.values(data.errors)[0][0]
                            : (data.message || 'Could not save the hotel concept.');
                        throw new Error(firstError);
                    }

                    paintHotelConcepts(data);
                    closeHotelConceptModal();
                } catch (error) {
                    showHotelConceptError(error.message || 'Could not save the hotel concept.');
                } finally {
                    if (submit) {
                        submit.disabled = false;
                        submit.textContent = originalLabel || 'Save concept';
                    }
                }
            });
        })();

        /* Teammate activity — reads the centralized activity_logs table. The endpoint
           is gated server-side by ActivityLogAccess, which only allows members of the
           same group. The student's own history is rendered in the Activity Logs section. */
        const MEMBER_ACTIVITY_URL = @json(route('students.activity.user', ['user' => '__USER_ID__']));

        function openMemberActivityModal(userId, memberName) {
            loadActivityModal(
                MEMBER_ACTIVITY_URL.replace('__USER_ID__', encodeURIComponent(userId)),
                (memberName || 'Member') + ' — Activity Logs',
                'Recorded system activity for this group member'
            );
        }

        function loadActivityModal(url, title, subtitle) {
            const body = document.getElementById('memberActivityModalBody');
            if (!body) return;

            document.getElementById('memberActivityModalTitle').textContent = title;
            document.getElementById('memberActivityModalSubtitle').textContent = subtitle;
            body.innerHTML = '<div class="px-5 py-12 text-center"><p class="text-sm font-semibold text-slate-400">Loading activity…</p></div>';

            document.getElementById('memberActivityModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then((res) => res.json().then((data) => {
                    if (!res.ok) throw new Error(data.error || 'Could not load this activity.');
                    return data;
                }))
                .then((data) => {
                    body.innerHTML = renderMyActivityRows(data.logs || []);
                    if (window.Iconify && typeof window.Iconify.scan === 'function') {
                        window.Iconify.scan(body);
                    }
                })
                .catch((err) => {
                    body.innerHTML = `<div class="px-5 py-12 text-center">
                            <p class="text-sm font-semibold text-rose-500">${escapeMemberHtml(err.message || 'Could not load this activity.')}</p>
                       </div>`;
                });
        }

        function renderMyActivityRows(logs) {
            if (!logs.length) {
                return `<div class="px-5 py-12 text-center">
                            <p class="text-sm font-semibold text-slate-400">No activity recorded yet</p>
                            <p class="text-xs text-slate-300 mt-1">Logins, task submissions and saved work will show here.</p>
                       </div>`;
            }
            return logs.map((log) => `
                <div class="px-5 py-3.5 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-brand-soft text-brand">
                        <span class="iconify text-lg" data-icon="mdi:clipboard-text-clock-outline"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-800">${escapeMemberHtml(log.activity_label || log.activity || '—')}</p>
                        <p class="text-xs text-slate-500 mt-0.5">${escapeMemberHtml(log.description || '—')}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">${escapeMemberHtml(log.created_at || '')}${log.created_at_human ? ' · ' + escapeMemberHtml(log.created_at_human) : ''}</p>
                    </div>
                </div>
            `).join('');
        }

        function closeMemberActivityModal() {
            const modal = document.getElementById('memberActivityModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function escapeMemberHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMemberActivityModal();
        });

        function switchReportTab(tab) {
            const selfBtn = document.getElementById('report-tab-self');
            const teamBtn = document.getElementById('report-tab-team');
            const selfPanel = document.getElementById('report-panel-self');
            const teamPanel = document.getElementById('report-panel-team');
            if (!selfBtn || !teamBtn) return;

            const isSelf = tab === 'self';
            selfBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold ' + (isSelf ? 'bg-brand text-white' : 'text-slate-500 hover:text-slate-800');
            teamBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold ' + (!isSelf ? 'bg-brand text-white' : 'text-slate-500 hover:text-slate-800');
            selfPanel.classList.toggle('hidden', !isSelf);
            teamPanel.classList.toggle('hidden', isSelf);
        }

        // ── Profile dropdown ──
        function toggleStudentProfileMenu() {
            const dd = document.getElementById('studentProfileDropdown');
            const chevron = document.getElementById('profileChevron');
            dd.classList.toggle('hidden');
            chevron.style.transform = dd.classList.contains('hidden') ? '' : 'rotate(180deg)';
        }
        function closeStudentProfileMenu() {
            document.getElementById('studentProfileDropdown').classList.add('hidden');
            document.getElementById('profileChevron').style.transform = '';
        }
        // Close on outside click
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('studentProfileMenu');
            if (!menu.contains(e.target)) closeStudentProfileMenu();
        });

        // ── Mobile sidebar ──
        function openMobileSidebar() {
            document.getElementById('sidebarOverlay').classList.remove('hidden');
            document.getElementById('sidebarPanel').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeMobileSidebar() {
            document.getElementById('sidebarOverlay').classList.add('hidden');
            document.getElementById('sidebarPanel').classList.remove('open');
            document.body.style.overflow = '';
        }

        /* Task grid filter. Every card is already on the page, grouped into one
           <section data-task-group> per TASK; the Status dropdown only decides
           which cards — and, once a group has none left showing, which whole
           group — stay visible. No round trip per change. */
        function filterTaskRows() {
            const status = document.getElementById('taskStatusFilter')?.value || 'all';
            let anyVisible = false;

            document.querySelectorAll('#tasks-section [data-task-group]').forEach((group) => {
                let visible = 0;

                group.querySelectorAll('[data-task-card]').forEach((row) => {
                    const matches = status === 'all' || row.dataset.taskStatus === status;
                    row.classList.toggle('hidden', !matches);

                    // A hidden card must not reopen with its drawer already down.
                    const detail = row.querySelector('.task-detail');
                    if (detail && !matches) detail.classList.add('hidden');

                    if (matches) visible++;
                });

                const count = group.querySelector('[data-group-visible]');
                if (count) count.textContent = String(visible);

                group.classList.toggle('hidden', visible === 0);
                if (visible > 0) anyVisible = true;
            });

            document.getElementById('taskFilterEmpty')?.classList.toggle('hidden', anyVisible);
        }

        /* Repaints one TASK group's summary card from the current state of its
           own task cards — called after anything changes a card's status without
           a reload (a checklist tick, a settled concept task). */
        function refreshTaskGroupSummary(groupEl) {
            if (!groupEl) return;

            const rows = Array.from(groupEl.querySelectorAll('[data-task-card]'));
            const total = rows.length;
            const done  = rows.filter((r) => r.dataset.taskStatus === 'completed').length;
            const hasRevision   = rows.some((r) => r.dataset.taskStatus === 'revision');
            const allNotStarted = total > 0 && rows.every((r) => r.dataset.taskStatus === 'not_started');
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;

            let statusKey;
            if (total > 0 && done === total) statusKey = 'completed';
            else if (hasRevision) statusKey = 'revision';
            else if (allNotStarted) statusKey = 'not_started';
            else statusKey = 'in_progress';

            const meta = {
                not_started: { label: 'Not Started', badge: 'bg-slate-100 text-slate-500', icon: 'mdi:circle-outline' },
                in_progress: { label: 'In Progress', badge: 'bg-brand-soft text-brand', icon: 'mdi:progress-clock' },
                revision: { label: 'Needs Revision', badge: 'bg-amber-50 text-amber-700', icon: 'mdi:message-alert-outline' },
                completed: { label: 'Completed', badge: 'bg-emerald-50 text-emerald-600', icon: 'mdi:check-decagram-outline' },
            }[statusKey];

            const percentEl = groupEl.querySelector('[data-summary-percent]');
            if (percentEl) percentEl.textContent = percent + '%';

            const countEl = groupEl.querySelector('[data-summary-count]');
            if (countEl) countEl.textContent = done + ' of ' + total + ' tasks completed';

            const ring = groupEl.querySelector('[data-summary-ring]');
            if (ring) {
                const circumference = 2 * Math.PI * 26;
                ring.setAttribute('stroke-dashoffset', String(circumference * (1 - percent / 100)));
            }

            const statusEl = groupEl.querySelector('[data-summary-status]');
            if (statusEl) {
                statusEl.className = 'inline-flex items-center gap-1.5 mt-2 w-fit px-3 py-1.5 rounded-lg text-[12px] font-bold ' + meta.badge;
                statusEl.innerHTML = '<span class="iconify text-sm" data-icon="' + meta.icon + '"></span> ' + meta.label;
                if (window.Iconify && typeof window.Iconify.scan === 'function') window.Iconify.scan(statusEl);
            }

            groupEl.dataset.groupStatus = statusKey;
        }

        /* Repaints one task card's progress bar, percent and status badge in place
           — shared by the checklist-tick handler and settleConceptTaskRow(). */
        function paintRowStatus(row, statusKey, percent) {
            const meta = {
                not_started: { label: 'Not Started', badge: 'bg-slate-100 text-slate-500' },
                in_progress: { label: 'In Progress', badge: 'bg-brand-soft text-brand' },
                revision: { label: 'Needs Revision', badge: 'bg-amber-50 text-amber-700' },
                completed: { label: 'Completed', badge: 'bg-emerald-50 text-emerald-600' },
            }[statusKey];
            if (!meta) return;

            row.dataset.taskStatus = statusKey;

            const badge = row.querySelector('[data-row-status-badge]');
            if (badge) {
                badge.className = 'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap ' + meta.badge;
                badge.textContent = meta.label;
            }

            const percentEl = row.querySelector('[data-row-percent]');
            if (percentEl) percentEl.textContent = percent + '%';

            const bar = row.querySelector('[data-row-bar]');
            if (bar) bar.style.width = percent + '%';
        }

        // ── Task detail drawer ──
        function toggleTaskDetail(id, btn) {
            const row = document.getElementById(id);
            if (!row) return;
            const open = !row.classList.toggle('hidden');
            btn?.classList.toggle('text-brand', open);
            btn?.classList.toggle('text-slate-400', !open);
        }

        // ── Customize / Simulation role menus ──
        // A member with several roles needs a menu on each button; closing one
        // when the other opens (or on an outside click) keeps at most one open.
        function toggleTaskAreaMenu(id) {
            const menu = document.getElementById(id);
            if (!menu) return;
            const willOpen = menu.hidden;
            document.querySelectorAll('[data-task-area-menu] > div[id]').forEach(m => { m.hidden = true; });
            menu.hidden = !willOpen;
        }
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-task-area-menu]')) return;
            document.querySelectorAll('[data-task-area-menu] > div[id]').forEach(m => { m.hidden = true; });
        });

        // ── Live group presence sync ──
        function hmsCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta && meta.content) return meta.content;
            const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
            return match ? decodeURIComponent(match[1]) : '';
        }
        async function syncStudentPresence() {
            try {
                const res = await fetch(@json(route('students.group.presence')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': hmsCsrfToken()
                    },
                    body: '{}'
                });
                if (!res.ok) return;
                const data = await res.json();
                const online = data.online || {};
                const members = data.members || [];
                const onlineById = {};
                members.forEach(function (m) {
                    onlineById[String(m.id)] = !!m.online;
                });
                Object.keys(online).forEach(function (k) {
                    onlineById[String(k)] = !!(online[k] || onlineById[String(k)]);
                });

                document.querySelectorAll('[data-presence-user]').forEach(function (row) {
                    const uid = String(parseInt(row.getAttribute('data-presence-user'), 10) || '');
                    const isOnline = !!onlineById[uid];
                    const dot = row.querySelector('.member-online-dot');
                    const label = row.querySelector('.member-online-label');
                    if (dot) {
                        dot.classList.toggle('bg-emerald-400', isOnline);
                        dot.classList.toggle('bg-green-400', isOnline);
                        dot.classList.toggle('pulse-dot', isOnline);
                        dot.classList.toggle('bg-slate-200', !isOnline);
                        dot.classList.toggle('bg-slate-300', !isOnline);
                    }
                    if (label) {
                        label.textContent = isOnline ? 'Online' : 'Offline';
                        label.classList.toggle('text-emerald-600', isOnline);
                        label.classList.toggle('font-semibold', isOnline);
                        label.classList.toggle('text-slate-400', !isOnline);
                    }
                });
            } catch (e) { /* ignore */ }
        }
        syncStudentPresence();
        setInterval(syncStudentPresence, 5000);

        (function openSectionFromQuery() {
            // Every section id the page defines. A section missing here is simply
            // ignored on load, so it must be added whenever one is added above.
            const allowed = ['home', 'group', 'tasks', 'activity', 'reports', 'profile'];
            let section = null;
            try {
                section = new URLSearchParams(window.location.search).get('section');
            } catch (e) {
                section = null;
            }
            if (!section && window.location.hash) {
                section = String(window.location.hash).replace(/^#/, '');
            }
            if (section && allowed.includes(section)) {
                showSection(section);
            }
        })();

    </script>

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
/* One request per tick, so a student's progress survives leaving the page. The
   box is put back the way it was if the write fails - showing a tick that was
   never stored would be worse than showing none. */
document.addEventListener('change', function (e) {
    const box = e.target.closest('[data-activity-check]');
    if (!box) return;

    const panel = box.closest('[data-activity-panel]');
    if (!panel) return;

    const taskId = panel.dataset.task;
    const index = box.dataset.index;
    const wanted = box.checked;
    box.disabled = true;

    fetch('{{ url('/students/tasks') }}/' + taskId + '/activities/' + index, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ done: wanted }),
    })
        .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
        .then(data => {
            const label = box.parentElement.querySelector('[data-activity-text]');
            if (label) {
                label.classList.toggle('line-through', wanted);
                label.classList.toggle('text-slate-400', wanted);
                label.classList.toggle('text-slate-600', !wanted);
            }
            const count = panel.querySelector('[data-activity-count]');
            if (count) count.textContent = data.done + ' of ' + data.total + ' done';
            const hint = panel.querySelector('[data-activity-hint]');
            if (hint) {
                hint.textContent = data.complete
                    ? 'All four done - you can submit this task.'
                    : 'Tick each step as you finish it. All four are needed before you can submit.';
            }

            // Steps ticked move the card itself between Not Started and In
            // Progress — unless faculty already sent it back or archived it,
            // which own their own status regardless of the checklist.
            const row = panel.closest('[data-task-card]');
            if (row && row.dataset.taskStatus !== 'revision' && row.dataset.taskStatus !== 'completed') {
                const newStatus = data.done > 0 ? 'in_progress' : 'not_started';
                const percent = data.total > 0 ? Math.round((data.done / data.total) * 100) : 0;
                paintRowStatus(row, newStatus, percent);
                refreshTaskGroupSummary(row.closest('[data-task-group]'));
            }
        })
        .catch(err => {
            box.checked = !wanted;
            const message = (err && err.message) ? err.message : 'That could not be saved. Please try again.';
            if (window.Swal) window.Swal.fire({ icon: 'error', title: 'Not saved', text: message });
            else alert(message);
        })
        .finally(() => { box.disabled = false; });
});
</script>

</body>
</html>