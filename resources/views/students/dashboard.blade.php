<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS-Learn | Student Dashboard</title>
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
                        surface: '#F8FAFC',
                        'surface-raised': '#FFFFFF',
                        'sidebar': '#0F172A',
                        'sidebar-hover': '#1E293B',
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

        .sidebar-link {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-link:hover {
            background: rgba(255,255,255,0.06);
            transform: translateX(2px);
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, #DB2777, #BE185D);
            box-shadow: 0 4px 15px -3px rgba(219, 39, 119, 0.4);
        }

        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px -12px rgba(0,0,0,0.1);
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

        .task-row {
            transition: all 0.2s ease;
        }
        .task-row:hover {
            background: #F8FAFC;
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
    <aside id="sidebarPanel" class="sidebar-panel fixed lg:sticky top-0 left-0 z-50 w-64 h-screen bg-sidebar flex flex-col overflow-hidden">
        <!-- Logo -->
        <div class="px-6 py-6 flex items-center gap-3 border-b border-white/[0.06]">
            <img src="{{ asset('chtm-logoo.png') }}" alt="HMS-Learn" class="h-10 w-auto object-contain brightness-0 invert">
            <div>
                <h1 class="text-base font-bold text-white tracking-tight">HMS<span class="text-brand-light">-Learn</span></h1>
                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">Student Portal</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 mb-2 text-[10px] font-bold text-slate-600 uppercase tracking-[0.15em]">Main Menu</p>

            <button onclick="showSection('home'); closeMobileSidebar();"
                    id="nav-home"
                    class="sidebar-link active w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-white">
                <span class="iconify text-lg" data-icon="mdi:view-dashboard-outline"></span>
                Dashboard-STUDENT
            </button>
            <button onclick="showSection('group'); closeMobileSidebar();"
                    id="nav-group"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-400">
                <span class="iconify text-lg" data-icon="mdi:account-group-outline"></span>
                My Group
            </button>
            <button onclick="showSection('tasks'); closeMobileSidebar();"
                    id="nav-tasks"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-400">
                <span class="iconify text-lg" data-icon="mdi:clipboard-check-outline"></span>
                Tasks
                @if(!empty($studentRoles) && $myRoleTasks->count() > 0)
                    <span class="ml-auto bg-brand text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ $myRoleTasks->count() }}</span>
                @endif
            </button>

            <div class="pt-4 pb-2">
                <p class="px-3 mb-2 text-[10px] font-bold text-slate-600 uppercase tracking-[0.15em]">Quick Access</p>
            </div>

            @php
                $firstRole = $studentRoles[0] ?? null;
                $deptRoute = match($firstRole) {
                    'front_desk'            => route('students.frontdesk'),
                    'restaurant_management' => route('students.restaurant'),
                    'room_management'       => route('students.roommanagement'),
                    'maintenance'           => route('students.maintenance'),
                    'housekeeping'          => route('students.housekeeping'),
                    default                 => '#',
                };
                $deptLabel = match($firstRole) {
                    'front_desk'            => 'Front Desk',
                    'restaurant_management' => 'Restaurant',
                    'room_management'       => 'Room Mgmt',
                    'maintenance'           => 'Maintenance',
                    'housekeeping'          => 'Housekeeping',
                    default                 => 'Department',
                };
                $deptIcon = match($firstRole) {
                    'front_desk'            => 'mdi:desk',
                    'restaurant_management' => 'mdi:silverware-fork-knife',
                    'room_management'       => 'mdi:bed-outline',
                    'maintenance'           => 'mdi:broom',
                    'housekeeping'          => 'mdi:sparkles',
                    default                 => 'mdi:domain',
                };
            @endphp

            <a href="{{ $deptRoute }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-400">
                <span class="iconify text-lg" data-icon="{{ $deptIcon }}"></span>
                {{ $deptLabel }}
                <span class="iconify text-xs text-slate-600 ml-auto" data-icon="mdi:arrow-top-right"></span>
            </a>
        </nav>

        <!-- User Profile Bottom -->
        @php
            $authUser = auth()->user();
            $profileName = $authUser?->name ?? 'Student';
            $nameParts = preg_split('/\s+/', trim($profileName));
            $firstInitial = $nameParts[0][0] ?? 'S';
            $lastInitial = count($nameParts) > 1 ? substr(end($nameParts), 0, 1) : '';
            $profileInitials = strtoupper($firstInitial . $lastInitial);
        @endphp
        <div class="px-3 pb-4 border-t border-white/[0.06] pt-4">
            <div class="relative" id="studentProfileMenu">
                <button type="button"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/[0.06] transition-colors"
                        aria-haspopup="true" aria-expanded="false"
                        onclick="toggleStudentProfileMenu()">
                    <div class="w-9 h-9 brand-gradient rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-brand/20">{{ $profileInitials }}</div>
                    <div class="flex-1 text-left min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $profileName }}</p>
                        <p class="text-[10px] text-slate-500">Student</p>
                    </div>
                    <span class="iconify text-slate-500 text-sm transition-transform" data-icon="mdi:chevron-up" id="profileChevron"></span>
                </button>
                <div class="absolute bottom-full left-0 mb-2 w-full rounded-xl border border-white/10 bg-sidebar-hover shadow-2xl p-1.5 hidden" id="studentProfileDropdown" role="menu">
                    <a href="#group" onclick="showSection('group'); closeStudentProfileMenu(); closeMobileSidebar();" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-slate-300 hover:bg-white/[0.06] transition-colors" role="menuitem">
                        <span class="iconify text-base" data-icon="mdi:account-outline"></span>
                        My Profile
                    </a>
                    <div class="h-px bg-white/[0.06] my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
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
            <div class="px-4 sm:px-8 h-16 flex items-center justify-between gap-4">
                <!-- Mobile hamburger -->
                <button onclick="openMobileSidebar()" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors -ml-2">
                    <span class="iconify text-xl text-slate-600" data-icon="mdi:menu"></span>
                </button>

                <!-- Breadcrumb / Page Title -->
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-slate-400">HMS-Learn</span>
                    <span class="iconify text-slate-300 text-xs" data-icon="mdi:chevron-right"></span>
                    <span id="breadcrumb-current" class="font-semibold text-slate-700">Dashboard</span>
                </div>

                <div class="flex-1"></div>

                <!-- Right side actions -->
                <div class="flex items-center gap-2">
                    <!-- Notification -->
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-100 transition-colors">
                        <span class="iconify text-xl text-slate-400" data-icon="mdi:bell-outline"></span>
                        @if(!empty($studentRoles) && $myRoleTasks->count() > 0)
                            <span class="absolute top-2 right-2 w-2 h-2 bg-brand rounded-full pulse-dot"></span>
                        @endif
                    </button>

                    <!-- Date -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-xl">
                        <span class="iconify text-sm text-slate-400" data-icon="mdi:calendar-today"></span>
                        <span class="text-xs font-semibold text-slate-500">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 px-4 sm:px-8 py-6 overflow-y-auto">
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
                    'housekeeping'          => 'Housekeeping Services',
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
            <div id="home-section" class="section-content fade-in space-y-6">
                <!-- Welcome -->
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                    <div>
                        <p class="text-sm text-slate-400 font-medium mb-1">{{ now()->format('l, F j') }}</p>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">Welcome back, {{ auth()->user()->name ?? 'John' }}</h2>
                    </div>
                    @if(!empty($studentRoles))
                        <a href="{{ $deptRoute }}" class="inline-flex items-center gap-2 px-5 py-2.5 brand-gradient text-white text-sm font-bold rounded-xl hover:opacity-90 transition-opacity shadow-lg shadow-brand/20 self-start sm:self-auto">
                            <span class="iconify" data-icon="{{ $myRoleIcon }}"></span>
                            Go to {{ $deptLabel }}
                        </a>
                    @endif
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Team -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-violet-50 flex items-center justify-center">
                                <span class="iconify text-violet-500 text-xl" data-icon="mdi:office-building-outline"></span>
                            </div>
                            <span class="iconify text-slate-200 text-lg" data-icon="mdi:dots-horizontal"></span>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900 truncate">{{ $group->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Team Name</p>
                    </div>

                    <!-- Role -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-rose-50 flex items-center justify-center">
                                <span class="iconify text-rose-500 text-xl" data-icon="{{ !empty($studentRoles) ? $myRoleIcon : 'mdi:account-question-outline' }}"></span>
                            </div>
                            <span class="iconify text-slate-200 text-lg" data-icon="mdi:dots-horizontal"></span>
                        </div>
                        <p class="text-lg font-extrabold text-slate-900 truncate leading-tight">{{ $myRoleLabel }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Assigned Role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                    </div>

                    <!-- Active Tasks -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-amber-50 flex items-center justify-center">
                                <span class="iconify text-amber-500 text-xl" data-icon="mdi:clipboard-text-outline"></span>
                            </div>
                            @if(!empty($studentRoles) && $myRoleTasks->count() > 0)
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">Needs Attention</span>
                            @endif
                        </div>
                        <p class="text-3xl font-extrabold text-slate-900">{{ !empty($studentRoles) ? $myRoleTasks->count() : 0 }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Active Tasks</p>
                    </div>

                    <!-- Completed -->
                    <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center">
                                <span class="iconify text-emerald-500 text-xl" data-icon="mdi:check-circle-outline"></span>
                            </div>
                        </div>
                        <p class="text-3xl font-extrabold text-slate-900">{{ $myCompletedTasks->count() ?? 0 }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Completed</p>
                    </div>
                </div>

                <!-- Main Grid: Team + Tasks -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                    <!-- Team Members Card -->
                    <div class="xl:col-span-1 bg-white rounded-2xl border border-slate-100 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Team Members</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ ($groupMembers ?? collect())->count() }} member{{ ($groupMembers ?? collect())->count() !== 1 ? 's' : '' }}</p>
                            </div>
                            <button onclick="showSection('group')" class="text-xs font-semibold text-brand hover:text-brand-dark transition-colors">View All</button>
                        </div>
                        <div class="p-4 space-y-2 max-h-[420px] overflow-y-auto">
                            @if(isset($groupMembers) && $groupMembers->count() > 0)
                                @foreach($groupMembers as $member)
                                    @php
                                        $mName   = $getMemberValue($member, 'name', 'Student');
                                        $mRoles  = is_string($member) ? [] : ($member->roles ?? ($member->role ? [$member->role] : ['room_management']));
                                        $isMe    = $getMemberValue($member, 'id') === (auth()->id() ?? null);
                                        $initials = strtoupper(substr($mName, 0, 1));
                                        $spacePos = strpos($mName, ' ');
                                        if ($spacePos !== false) $initials .= strtoupper(substr($mName, $spacePos + 1, 1));
                                    @endphp
                                    <div class="member-card flex items-center gap-3 p-3 rounded-xl {{ $isMe ? 'bg-brand-soft/60 border border-brand/15' : 'bg-slate-50/50 border border-transparent' }}">
                                        <div class="w-10 h-10 {{ $isMe ? 'brand-gradient shadow-md shadow-brand/20' : 'bg-slate-200' }} rounded-xl flex items-center justify-center shrink-0">
                                            <span class="{{ $isMe ? 'text-white' : 'text-slate-500' }} text-xs font-bold">{{ $initials }}</span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-slate-800 truncate">
                                                {{ $mName }}
                                                @if($isMe)
                                                    <span class="ml-1 px-1.5 py-0.5 bg-brand text-white text-[8px] font-bold rounded-md uppercase leading-none">You</span>
                                                @endif
                                            </p>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($mRoles as $mRole)
                                                    @php
                                                        $mBadge  = $roleBadgeClasses[$mRole] ?? 'role-badge-room';
                                                        $mIcon   = $roleIcons[$mRole]  ?? 'mdi:account-outline';
                                                        $mLabel  = $roleLabels[$mRole] ?? ucfirst(str_replace('_', ' ', $mRole));
                                                    @endphp
                                                    <span class="inline-flex items-center gap-0.5 {{ $mBadge }} px-1.5 py-0.5 rounded-md text-[9px] font-semibold">
                                                        <span class="iconify text-[9px]" data-icon="{{ $mIcon }}"></span>
                                                        {{ $mLabel }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @if($isMe)
                                            <div class="w-2.5 h-2.5 rounded-full bg-green-400 pulse-dot shrink-0"></div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-10">
                                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-slate-300 text-2xl" data-icon="mdi:account-group-outline"></span>
                                    </div>
                                    <p class="text-sm text-slate-400 font-semibold">No members yet</p>
                                    <p class="text-xs text-slate-300 mt-1">Your team will appear here</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Tabbed: Tasks + Activity -->
                    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 overflow-hidden flex flex-col">
                        <!-- Tabs -->
                        <div class="px-5 border-b border-slate-100 flex items-center gap-6">
                            <button onclick="switchHomeTab('tasks')" id="home-tab-tasks"
                                class="tab-btn active py-4 text-sm font-bold text-brand transition-colors">
                                Assigned Tasks
                            </button>
                            <button onclick="switchHomeTab('activity')" id="home-tab-activity"
                                class="tab-btn py-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">
                                Team Activity
                            </button>
                        </div>

                        <!-- Tasks Panel -->
                        <div id="home-panel-tasks" class="flex-1 divide-y divide-slate-50 max-h-[420px] overflow-y-auto">
                            @if(!empty($studentRoles))
                                @forelse($myRoleTasks as $task)
                                    @php
                                        $priorityClass = [
                                            'high'   => 'bg-red-50 text-red-600 border-red-100',
                                            'medium' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'low'    => 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                        ][$task->priority] ?? 'bg-slate-50 text-slate-500 border-slate-100';
                                        $priorityDot = [
                                            'high'   => 'bg-red-400',
                                            'medium' => 'bg-amber-400',
                                            'low'    => 'bg-emerald-400'
                                        ][$task->priority] ?? 'bg-slate-300';
                                    @endphp
                                    <div class="task-row px-5 py-4 flex items-start gap-4">
                                        <div class="w-2 h-2 rounded-full {{ $priorityDot }} mt-2 shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-sm font-bold text-slate-800">{{ $task->title }}</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide border {{ $priorityClass }}">{{ $task->priority }}</span>
                                            </div>
                                            @if($task->description)
                                                <p class="text-xs text-slate-400 mt-1 leading-relaxed line-clamp-2">{{ $task->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400">
                                                @if($task->due_date)
                                                    <span class="flex items-center gap-1">
                                                        <span class="iconify text-xs" data-icon="mdi:calendar-outline"></span>
                                                        {{ $task->due_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-5 py-16 text-center">
                                        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                            <span class="iconify text-emerald-400 text-3xl" data-icon="mdi:check-decagram-outline"></span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-500">All caught up!</p>
                                        <p class="text-xs text-slate-400 mt-1">No active tasks for your role right now.</p>
                                    </div>
                                @endforelse
                            @else
                                <div class="px-5 py-16 text-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-question-outline"></span>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400">No role assigned</p>
                                    <p class="text-xs text-slate-300 mt-1">Tasks will appear once you have a role.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Activity Panel -->
                        <div id="home-panel-activity" class="flex-1 divide-y divide-slate-50 max-h-[420px] overflow-y-auto hidden">
                            @php
                                $allActivityEntries = collect();
                                foreach ($recentTasks as $task) {
                                    $tRoleLabel = $roleLabels[$task->role] ?? ucfirst($task->role);
                                    $allActivityEntries->push((object)[
                                        'type'        => $task->status === 'archived' ? 'task_completed' : 'task_assigned',
                                        'title'       => $task->status === 'archived' ? 'Completed: ' . $task->title : 'Assigned: ' . $task->title,
                                        'subtitle'    => $tRoleLabel,
                                        'icon'        => $task->status === 'archived' ? 'mdi:check-circle-outline' : 'mdi:clipboard-plus-outline',
                                        'icon_bg'     => $task->status === 'archived' ? 'bg-emerald-50' : 'bg-blue-50',
                                        'icon_color'  => $task->status === 'archived' ? 'text-emerald-500' : 'text-blue-500',
                                        'time'        => $task->updated_at,
                                    ]);
                                }
                                $allActivityEntries = $allActivityEntries->sortByDesc('time')->take(10)->values();
                            @endphp
                            @forelse($allActivityEntries as $entry)
                                <div class="task-row px-5 py-4 flex items-start gap-3">
                                    <div class="w-9 h-9 {{ $entry->icon_bg }} rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                                        <span class="iconify {{ $entry->icon_color }} text-base" data-icon="{{ $entry->icon }}"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $entry->title }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-[11px] text-slate-400">{{ $entry->subtitle }}</p>
                                            <span class="text-slate-200">·</span>
                                            <p class="text-[11px] text-slate-400">{{ $entry->time?->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-16 text-center">
                                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <span class="iconify text-slate-300 text-3xl" data-icon="mdi:timeline-clock-outline"></span>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400">No activity yet</p>
                                    <p class="text-xs text-slate-300 mt-1">Team actions will show up here</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Role Info Card -->
                @if(!empty($studentRoles))
                    <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                        <div class="p-6 flex flex-col md:flex-row md:items-center gap-6">
                            @foreach($studentRoles as $sRole)
                                @php
                                    $sRoleLabel = $roleLabels[$sRole] ?? ucfirst(str_replace('_', ' ', $sRole));
                                    $sRoleIcon = $roleIcons[$sRole] ?? 'mdi:account-outline';
                                    $sRoleColor = match($sRole) {
                                        'front_desk'            => 'orange',
                                        'restaurant_management' => 'amber',
                                        'room_management'       => 'pink',
                                        'maintenance'           => 'violet',
                                        'housekeeping'          => 'teal',
                                        default                 => 'slate'
                                    };
                                @endphp
                                <div class="w-16 h-16 bg-{{ $sRoleColor }}-50 rounded-2xl flex items-center justify-center shrink-0">
                                    <span class="iconify text-{{ $sRoleColor }}-500 text-3xl" data-icon="{{ $sRoleIcon }}"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-widest mb-1">Your Department</p>
                                    <h3 class="text-lg font-extrabold text-slate-900 mb-1">{{ $sRoleLabel }}</h3>
                                    <p class="text-sm text-slate-500 leading-relaxed">
                                        @switch($sRole)
                                            @case('front_desk') Manage guest reception, reservations, key cards, and billing inquiries. @break
                                            @case('restaurant_management') Coordinate banquet setups, menu planning, and room service operations. @break
                                            @case('room_management') Handle check-ins, room assignments, VIP setups, and minibar restocking. @break
                                            @case('maintenance') Oversee repairs, cleaning schedules, and facility inspections. @break
                                            @case('housekeeping') Manage room cleaning, linen services, amenity restocking, and sanitation standards. @break
                                            @default Your role description will appear here. @break
                                        @endswitch
                                    </p>
                                </div>
                            @endforeach
                            <div class="flex gap-3 shrink-0">
                                <div class="bg-slate-50 rounded-xl px-5 py-3 text-center min-w-[80px]">
                                    <p class="text-xl font-extrabold text-slate-800">{{ $myRoleTasks->count() }}</p>
                                    <p class="text-[10px] text-slate-400 font-semibold">Active</p>
                                </div>
                                <div class="bg-emerald-50 rounded-xl px-5 py-3 text-center min-w-[80px]">
                                    <p class="text-xl font-extrabold text-emerald-700">{{ $myCompletedTasks->count() }}</p>
                                    <p class="text-[10px] text-emerald-500 font-semibold">Done</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>


            <!-- ==================== GROUP SECTION ==================== -->
            <div id="group-section" class="section-content hidden fade-in space-y-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mb-1">My Group</h2>
                    <p class="text-sm text-slate-400">Team details, members, and department assignments</p>
                </div>

                <!-- Group Hero Card -->
                <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="brand-gradient px-6 sm:px-8 py-8 relative overflow-hidden">
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/[0.07] rounded-full"></div>
                        <div class="absolute bottom-0 left-1/3 w-32 h-32 bg-white/[0.04] rounded-full"></div>
                        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
                            <div class="w-16 h-16 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20 shrink-0">
                                <span class="iconify text-white text-3xl" data-icon="mdi:office-building-outline"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($group)
                                    <p class="text-white/50 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Hotel Management Simulation</p>
                                    <h3 class="text-2xl font-extrabold text-white">{{ $group->name }}</h3>
                                @else
                                    <p class="text-white/50 text-[10px] font-bold uppercase tracking-[0.2em] mb-1">Team</p>
                                    <h3 class="text-xl font-extrabold text-white">Not assigned yet</h3>
                                @endif
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-center px-4">
                                    <p class="text-2xl font-extrabold text-white">{{ ($groupMembers ?? collect())->count() }}</p>
                                    <p class="text-[10px] text-white/50 font-semibold uppercase tracking-wider">Members</p>
                                </div>
                                <div class="w-px h-10 bg-white/20"></div>
                                <div class="text-center px-4">
                                    <p class="text-2xl font-extrabold text-white">5</p>
                                    <p class="text-[10px] text-white/50 font-semibold uppercase tracking-wider">Depts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members List -->
                <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">All Members</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Roles and task progress</p>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-brand-soft rounded-lg">
                            <span class="iconify text-brand text-sm" data-icon="mdi:account-group"></span>
                            <span class="text-xs font-bold text-brand">{{ ($groupMembers ?? collect())->count() }}</span>
                        </div>
                    </div>

                    @if(isset($groupMembers) && $groupMembers->count() > 0)
                        <div class="divide-y divide-slate-50">
                            @foreach($groupMembers as $index => $member)
                                @php
                                    $roleKey = $getMemberValue($member, 'role', 'room_management');
                                    $displayRole = $roleLabels[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                    $roleBadgeClass = $roleBadgeClasses[$roleKey] ?? 'role-badge-room';
                                    $roleIcon = $roleIcons[$roleKey] ?? 'mdi:account-outline';
                                    $isCurrentUser = $getMemberValue($member, 'id') === (auth()->id() ?? null);
                                    $memberName = $getMemberValue($member, 'name', 'Unknown');
                                    $memberInitials = strtoupper(substr($memberName, 0, 1));
                                    $sp = strpos($memberName, ' ');
                                    if ($sp !== false) $memberInitials .= strtoupper(substr($memberName, $sp + 1, 1));
                                @endphp
                                <div class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50/50 transition-colors">
                                    <span class="text-xs font-bold text-slate-300 w-5 text-center shrink-0">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="w-10 h-10 {{ $isCurrentUser ? 'brand-gradient shadow-md shadow-brand/20' : 'bg-slate-100' }} rounded-xl flex items-center justify-center shrink-0">
                                        <span class="{{ $isCurrentUser ? 'text-white' : 'text-slate-500' }} text-xs font-bold">{{ $memberInitials }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">
                                            {{ $memberName }}
                                            @if($isCurrentUser)
                                                <span class="ml-1.5 px-1.5 py-0.5 bg-brand-soft text-brand text-[8px] font-bold rounded-md uppercase">You</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ $getMemberValue($member, 'email', '') }}</p>
                                    </div>
                                    <span class="hidden sm:inline-flex items-center gap-1 {{ $roleBadgeClass }} px-2.5 py-1 rounded-full text-[10px] font-semibold shrink-0">
                                        <span class="iconify text-[10px]" data-icon="{{ $roleIcon }}"></span>
                                        {{ $displayRole }}
                                    </span>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <div class="w-2 h-2 rounded-full {{ $isCurrentUser ? 'bg-emerald-400 pulse-dot' : 'bg-slate-200' }}"></div>
                                        <span class="text-[11px] {{ $isCurrentUser ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">{{ $isCurrentUser ? 'Online' : 'Offline' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-group-outline"></span>
                            </div>
                            <p class="text-sm font-bold text-slate-400">No members yet</p>
                            <p class="text-xs text-slate-300 mt-1">They'll appear once assigned by faculty</p>
                        </div>
                    @endif
                </div>
            </div>


            <!-- ==================== TASKS SECTION ==================== -->
            <div id="tasks-section" class="section-content hidden fade-in space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 mb-1">My Tasks</h2>
                        <p class="text-sm text-slate-400">Tasks assigned to your role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                    </div>
                    @if(!empty($studentRoles))
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 rounded-lg">
                                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                <span class="text-xs font-bold text-emerald-700">{{ $myCompletedTasks->count() }} completed</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 rounded-lg">
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <span class="text-xs font-bold text-amber-700">{{ $myRoleTasks->count() }} active</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($studentRoles))
                    @if($myRoleTasks->count() > 0)
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800">Active Tasks</h3>
                            </div>
                            <div class="divide-y divide-slate-50">
                                @foreach($myRoleTasks as $task)
                                    @php
                                        $priorityClass = [
                                            'high'   => 'bg-red-50 text-red-600 border-red-100',
                                            'medium' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'low'    => 'bg-emerald-50 text-emerald-600 border-emerald-100'
                                        ][$task->priority] ?? 'bg-slate-50 text-slate-500 border-slate-100';
                                        $priorityDot = [
                                            'high'   => 'bg-red-400',
                                            'medium' => 'bg-amber-400',
                                            'low'    => 'bg-emerald-400'
                                        ][$task->priority] ?? 'bg-slate-300';
                                        $isOverdue = $task->due_date && $task->due_date->isPast();
                                    @endphp
                                    <div class="task-row px-6 py-5 flex items-start gap-4">
                                        <div class="mt-1.5 w-3 h-3 rounded-full border-2 border-slate-200 shrink-0 hover:border-brand hover:bg-brand-soft cursor-pointer transition-colors"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-sm font-bold text-slate-800">{{ $task->title }}</p>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide border {{ $priorityClass }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $priorityDot }}"></span>
                                                    {{ $task->priority }}
                                                </span>
                                                @if($isOverdue)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-red-50 text-red-600 border border-red-100">
                                                        <span class="iconify text-[10px]" data-icon="mdi:alert-circle-outline"></span>
                                                        Overdue
                                                    </span>
                                                @endif
                                            </div>
                                            @if($task->description)
                                                <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">{{ $task->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-3 mt-3">
                                                @if($task->due_date)
                                                    <span class="flex items-center gap-1 text-[11px] {{ $isOverdue ? 'text-red-500 font-semibold' : 'text-slate-400' }}">
                                                        <span class="iconify text-xs" data-icon="mdi:calendar-outline"></span>
                                                        {{ $task->due_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 text-blue-600 border border-blue-100">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-slate-100 px-6 py-20 text-center">
                            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <span class="iconify text-emerald-400 text-3xl" data-icon="mdi:check-decagram-outline"></span>
                            </div>
                            <p class="text-base font-bold text-slate-600">All tasks completed!</p>
                            <p class="text-sm text-slate-400 mt-1">Great job — no pending tasks for your role.</p>
                        </div>
                    @endif

                    @if($myCompletedTasks->count() > 0)
                        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                            <button onclick="toggleCompletedTasks()" class="w-full px-6 py-4 border-b border-slate-100 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                <h3 class="text-sm font-bold text-slate-800">Completed Tasks ({{ $myCompletedTasks->count() }})</h3>
                                <span class="iconify text-slate-400 text-lg transition-transform" data-icon="mdi:chevron-down" id="completedChevron"></span>
                            </button>
                            <div id="completedTasksList" class="divide-y divide-slate-50 hidden">
                                @foreach($myCompletedTasks as $task)
                                    <div class="task-row px-6 py-4 flex items-start gap-4 opacity-60">
                                        <div class="mt-0.5 w-3 h-3 rounded-full bg-emerald-400 shrink-0 flex items-center justify-center">
                                            <span class="iconify text-white text-[8px]" data-icon="mdi:check"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-slate-500 line-through">{{ $task->title }}</p>
                                            @if($task->due_date)
                                                <p class="text-[11px] text-slate-300 mt-1">{{ $task->due_date->format('M d, Y') }}</p>
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Done</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-2xl border border-slate-100 px-6 py-20 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-question-outline"></span>
                        </div>
                        <p class="text-base font-bold text-slate-500">No role assigned</p>
                        <p class="text-sm text-slate-400 mt-1">Once your faculty assigns a role, your tasks will appear here.</p>
                    </div>
                @endif
            </div>

        </main>
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
                link.classList.add('text-slate-400');
                link.classList.remove('text-white');
            });
            const activeNav = document.getElementById('nav-' + section);
            if (activeNav) {
                activeNav.classList.add('active');
                activeNav.classList.remove('text-slate-400');
                activeNav.classList.add('text-white');
            }

            // Update breadcrumb
            const labels = { home: 'Dashboard', group: 'My Group', tasks: 'Tasks' };
            document.getElementById('breadcrumb-current').textContent = labels[section] || 'Dashboard';
        }

        // ── Home tab switching ──
        function switchHomeTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active', 'text-brand');
                b.classList.add('text-slate-400');
            });
            document.getElementById('home-tab-' + tab).classList.add('active', 'text-brand');
            document.getElementById('home-tab-' + tab).classList.remove('text-slate-400');

            document.getElementById('home-panel-tasks').classList.toggle('hidden', tab !== 'tasks');
            document.getElementById('home-panel-activity').classList.toggle('hidden', tab !== 'activity');
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

        // ── Completed tasks toggle ──
        function toggleCompletedTasks() {
            const list = document.getElementById('completedTasksList');
            const chevron = document.getElementById('completedChevron');
            list.classList.toggle('hidden');
            chevron.style.transform = list.classList.contains('hidden') ? '' : 'rotate(180deg)';
        }
    </script>
</body>
</html>