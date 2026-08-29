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
    <aside id="sidebarPanel" class="sidebar-panel app-sidebar fixed lg:sticky top-0 left-0 z-50 w-64 h-screen flex flex-col overflow-hidden">
        <!-- Logo -->
        <div class="px-6 py-4 flex items-center gap-3 border-b border-white/[0.06]">
            <img src="{{ asset('chtm-logoo.png') }}" alt="Hotel Management System" class="h-10 w-auto object-contain">
            <div>
                <h1 class="text-sm font-bold text-white tracking-tight leading-tight">Hotel Management System</h1>
                <p class="text-[10px] text-white font-medium uppercase tracking-widest">Student Portal</p>
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
                @if(!empty($studentRoles) && $myRoleTasks->count() > 0)
                    <span class="ml-auto bg-white/25 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ $myRoleTasks->count() }}</span>
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
            <div id="home-section" class="section-content fade-in space-y-4">
                <!-- Welcome -->
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">Welcome back, {{ $studentDisplayName ?? (auth()->user()->name ?? 'Student') }}</h2>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Team -->
                    <div class="stat-card bg-white rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                                <span class="iconify text-violet-500 text-lg" data-icon="mdi:office-building-outline"></span>
                            </div>
                        </div>
                        <p class="text-xl font-extrabold text-slate-900 truncate">{{ $group->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">
                            Team Name
                            @if(!empty($studentClass))
                                · {{ $studentClass->name }}
                            @endif
                        </p>
                    </div>

                    <!-- Role -->
                    <div class="stat-card bg-white rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center">
                                <span class="iconify text-rose-500 text-lg" data-icon="{{ !empty($studentRoles) ? $myRoleIcon : 'mdi:account-question-outline' }}"></span>
                            </div>
                        </div>
                        <p class="text-base font-extrabold text-slate-900 truncate leading-tight">{{ $myRoleLabel }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Assigned Role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                    </div>

                    <!-- Active Tasks -->
                    <div class="stat-card bg-white rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                                <span class="iconify text-amber-500 text-lg" data-icon="mdi:clipboard-text-outline"></span>
                            </div>
                            @if(!empty($studentRoles) && $myRoleTasks->count() > 0)
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100">Needs Attention</span>
                            @endif
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900">{{ !empty($studentRoles) ? $myRoleTasks->count() : 0 }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Active Tasks</p>
                    </div>

                    <!-- Completed -->
                    <div class="stat-card bg-white rounded-2xl p-4 border border-slate-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                                <span class="iconify text-emerald-500 text-lg" data-icon="mdi:check-circle-outline"></span>
                            </div>
                        </div>
                        <p class="text-2xl font-extrabold text-slate-900">{{ $myCompletedTasks->count() ?? 0 }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1">Completed</p>
                    </div>
                </div>

                <!-- Recent Activities -->
                @php
                    $homeRoleLabels = [
                        'front_desk' => 'Front Desk',
                        'restaurant_management' => 'Restaurant',
                        'room_management' => 'Room Mgmt',
                        'maintenance' => 'Maintenance',
                        'housekeeping' => 'Housekeeping',
                    ];
                    $homeRecentActivities = ($selfActivityLogs ?? collect())->take(6);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
                        <span class="iconify text-brand text-lg" data-icon="mdi:history"></span>
                        <p class="text-sm font-bold text-slate-800">Recent Activities</p>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @forelse($homeRecentActivities as $task)
                            <div class="px-5 py-3.5 flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $task->status === 'archived' ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500' }}">
                                    <span class="iconify text-lg" data-icon="{{ $task->status === 'archived' ? 'mdi:check-circle-outline' : 'mdi:clipboard-plus-outline' }}"></span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800 truncate">
                                        {{ $task->status === 'archived' ? 'Completed' : 'Assigned' }}: {{ $task->title }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $homeRoleLabels[$task->role] ?? $task->role }}
                                        · {{ optional($task->updated_at)->diffForHumans() }}
                                    </p>
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


            <!-- ==================== GROUP SECTION ==================== -->
            <div id="group-section" class="section-content hidden fade-in space-y-3">
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">My Team</h2>

                <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="brand-gradient px-4 sm:px-5 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 bg-white/15 backdrop-blur-sm rounded-xl flex items-center justify-center border border-white/20 shrink-0">
                                <span class="iconify text-white text-xl" data-icon="mdi:office-building-outline"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($group)
                                    <p class="text-white/50 text-[9px] font-bold uppercase tracking-[0.15em]">Hotel Management Simulation</p>
                                    <h3 class="text-lg font-extrabold text-white leading-tight">{{ $group->name }}</h3>
                                @else
                                    <p class="text-white/50 text-[9px] font-bold uppercase tracking-[0.15em]">Team</p>
                                    <h3 class="text-base font-extrabold text-white">Not assigned yet</h3>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0 text-center">
                                <div>
                                    <p class="text-lg font-extrabold text-white leading-none">{{ ($groupMembers ?? collect())->count() }}</p>
                                    <p class="text-[9px] text-white/50 font-semibold uppercase">Members</p>
                                </div>
                                <div class="w-px h-8 bg-white/20"></div>
                                <div>
                                    <p class="text-lg font-extrabold text-white leading-none">5</p>
                                    <p class="text-[9px] text-white/50 font-semibold uppercase">Depts</p>
                                </div>
                            </div>
                        </div>

                        {{-- The concept faculty chose. It stops being a proposal at that point
                             and becomes what this team is building, so it sits with the team's
                             name rather than in the panel about proposals below — which is why
                             that panel disappears once this fills in. Painted from the same
                             payload as that panel, by paintTeamHeaderConcept(), so there is one
                             description of "what the team's concept is" rather than a server
                             copy and a client copy that can drift. --}}
                        <div id="teamHeaderConcept" class="hidden mt-4 pt-4 border-t border-white/15"></div>
                    </div>

                    {{-- The concepts used to head this panel. They outgrew it once there
                         were two of them, each with its own state, feedback and history,
                         so they have their own panel below and this one is about members
                         again. --}}

                    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-800">All Members</h3>
                        <span class="text-xs font-bold text-brand">{{ ($groupMembers ?? collect())->count() }}</span>
                    </div>

                    @if(isset($groupMembers) && $groupMembers->count() > 0)
                        <div class="divide-y divide-slate-100">
                            @foreach($groupMembers as $index => $member)
                                @php
                                    $mRoles = is_string($member)
                                        ? []
                                        : (array) ($member->roles ?? []);
                                    if ($mRoles === [] && !empty($member->role ?? null)) {
                                        $mRoles = [$member->role];
                                    }
                                    $isCurrentUser = $getMemberValue($member, 'id') === (auth()->id() ?? null);
                                    $memberName = $getMemberValue($member, 'name', 'Unknown');
                                @endphp
                                <div class="px-4 py-2.5 flex items-center gap-3 hover:bg-slate-50/50 transition-colors" data-member-row data-member-id="{{ $getMemberValue($member, 'id') }}">
                                    <span class="text-xs font-bold text-slate-300 w-5 text-center shrink-0">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    @include('partials.user-avatar', [
                                        'user'        => is_object($member) ? ($member->user ?? null) : null,
                                        'name'        => $memberName,
                                        'size'        => 'w-9 h-9',
                                        'currentUser' => $isCurrentUser,
                                    ])
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">
                                            {{ $memberName }}
                                            @if($isCurrentUser)
                                                <span class="ml-1.5 px-1.5 py-0.5 bg-brand-soft text-brand text-[8px] font-bold rounded-md uppercase">You</span>
                                            @endif
                                        </p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ $getMemberValue($member, 'email', '') }}</p>
                                    </div>
                                    <div class="hidden sm:flex flex-wrap items-center justify-end gap-1 shrink-0 max-w-[14rem]">
                                        @forelse($mRoles as $roleKey)
                                            @php
                                                $displayRole = $roleLabels[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey));
                                                $roleBadgeClass = $roleBadgeClasses[$roleKey] ?? 'role-badge-room';
                                                $roleIcon = $roleIcons[$roleKey] ?? 'mdi:account-outline';
                                            @endphp
                                            <span class="inline-flex items-center gap-1 {{ $roleBadgeClass }} px-2 py-0.5 rounded-full text-[10px] font-semibold">
                                                <span class="iconify text-[10px]" data-icon="{{ $roleIcon }}"></span>
                                                {{ $displayRole }}
                                            </span>
                                        @empty
                                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-400 px-2.5 py-1 rounded-full text-[10px] font-semibold">
                                                No role
                                            </span>
                                        @endforelse
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0" data-presence-user="{{ $getMemberValue($member, 'id') }}">
                                        <div class="w-2 h-2 rounded-full member-online-dot {{ $isCurrentUser ? 'bg-emerald-400 pulse-dot' : 'bg-slate-200' }}"></div>
                                        <span class="text-[11px] member-online-label {{ $isCurrentUser ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">{{ $isCurrentUser ? 'Online' : 'Offline' }}</span>
                                    </div>
                                    {{-- Teammates only; own history lives in the Activity Logs nav section. --}}
                                    @php $memberUserId = $getMemberValue($member, 'id'); @endphp
                                    @if(!$isCurrentUser && $memberUserId)
                                        <button type="button"
                                            onclick="openMemberActivityModal({{ (int) $memberUserId }}, {{ json_encode($memberName) }})"
                                            class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 hover:text-brand transition"
                                            title="View {{ $memberName }}'s activity logs">
                                            <span class="iconify text-sm" data-icon="mdi:clipboard-text-clock-outline"></span>
                                            <span class="hidden md:inline">Activity</span>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-4 py-8 text-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-group-outline"></span>
                            </div>
                            <p class="text-sm font-bold text-slate-400">No members yet</p>
                            <p class="text-xs text-slate-300 mt-1">They'll appear once assigned by faculty</p>
                        </div>
                    @endif
                </div>

                {{-- The team's two hotel concepts, side by side, while faculty is still
                     choosing. Faculty judges each separately, so each card carries its own
                     state, its own feedback and its own edit history. Front Desk proposes
                     each first version; every member may then improve either one; Front
                     Desk hands both in with the single button in this header, because the
                     pair exists so faculty can weigh them against each other.

                     Once faculty decides, this whole card hides — paintTeamHeaderConcept()
                     toggles it — because there is nothing left to propose or compare; the
                     winner has already moved up into the team header above.

                     The whole panel is rendered from #conceptPanel by the same JS that
                     repaints it after a save or a submit, so there is one description of
                     this markup rather than a server copy and a client copy that drift. --}}
                <div id="conceptPanelCard" class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-800">Hotel Concepts</h3>
                            <p class="text-[11px] text-slate-400">Two proposals for your faculty to review.</p>
                        </div>
                        <button type="button" id="conceptSubmitAllBtn" onclick="submitHotelConcepts()"
                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-white brand-gradient shadow-md shadow-brand/20 hover:opacity-90 transition disabled:opacity-60 hidden">
                            <span class="iconify text-[13px]" data-icon="mdi:send-outline"></span>
                            <span>Submit both to Faculty</span>
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="mx-4 mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mx-4 mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div id="conceptPanel" class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                </div>

                @if($group)
                    {{-- Edit dialog behind the header's Edit button. Saves over fetch so the
                         header, the task card and the history all move together, without
                         throwing the member back to the top of the dashboard.

                         Rendered for every member of a team rather than gated on the edit
                         right: a save or a faculty verdict can open editing up while the
                         page is still open, and the button that reveals this dialog is
                         repainted from that response. The controller is the real gate. --}}
                    <div id="hotelConceptModal" class="fixed inset-0 z-50 hidden">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeHotelConceptModal()"></div>
                        <div class="relative top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl border border-slate-100 w-[92vw] max-w-xl max-h-[90vh] overflow-y-auto">
                            <div class="brand-gradient px-5 py-4 flex items-center justify-between gap-3 sticky top-0 z-10">
                                <div class="min-w-0">
                                    <p class="text-white/60 text-[9px] font-bold uppercase tracking-[0.15em]">Task 1 · Your Team</p>
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
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Title</label>
                                    <input name="title" type="text" required maxlength="150"
                                        placeholder="e.g. Seaside Serenity Resort"
                                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
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
            </div>

            <!-- ==================== TASKS SECTION ==================== -->
            <div id="tasks-section" class="section-content hidden fade-in space-y-4">
                @php
                    // One entry per assigned role — a member may hold more than one.
                    $myModules = \App\Support\HotelTemplateBuilder::modulesForRoles($studentRoles ?? []);
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mb-0.5">My Tasks</h2>
                        <p class="text-sm text-slate-400">Tasks assigned to your role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                    </div>
                    @if(!empty($studentRoles))
                        <div class="flex flex-wrap items-center gap-3">
                            @foreach($myModules as $module)
                                <a href="{{ route($module['route']) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 brand-gradient text-white text-xs font-bold rounded-xl shadow-lg shadow-brand/20 hover:opacity-90 transition-opacity">
                                    <span class="iconify text-base" data-icon="mdi:palette-outline"></span>
                                    {{ count($myModules) > 1 ? 'Customize ' . $module['label'] : 'Customize' }}
                                </a>
                            @endforeach
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
                                        $isOverdue = $task->due_date && $task->due_date->isPast();
                                        // Rows are per member; only offer submit on this student's own
                                        // row (or an unclaimed one). Task::booted() keeps assigned_to
                                        // in sync with student_id, so the user id is enough here.
                                        $isMine = !$task->assigned_to || (int) $task->assigned_to === (int) auth()->id();
                                    @endphp
                                    <div class="task-row px-4 py-3 flex items-start gap-3">
                                        @if($isMine)
                                            <form method="POST" action="{{ route('students.tasks.complete', $task) }}" class="shrink-0 mt-1.5 leading-none">
                                                @csrf
                                                <button type="submit"
                                                    class="w-3 h-3 rounded-full border-2 border-slate-200 hover:border-brand hover:bg-brand-soft cursor-pointer transition-colors"
                                                    title="Mark &quot;{{ $task->title }}&quot; as done"
                                                    aria-label="Mark {{ $task->title }} as done"></button>
                                            </form>
                                        @else
                                            <div class="mt-1.5 w-3 h-3 rounded-full border-2 border-slate-100 shrink-0" title="Assigned to a teammate"></div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-sm font-bold text-slate-800">{{ $task->title }}</p>
                                            </div>
                                            @if($task->description)
                                                <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">{{ $task->description }}</p>
                                            @endif
                                            @if($task->needs_revision)
                                                {{-- Sent back by faculty: active again, but carrying feedback. --}}
                                                <div class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
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
                                            @if($task->due_date)
                                                <div class="flex items-center gap-3 mt-3">
                                                    <span class="flex items-center gap-1 text-[11px] {{ $isOverdue ? 'text-red-500 font-semibold' : 'text-slate-400' }}">
                                                        <span class="iconify text-xs" data-icon="mdi:calendar-outline"></span>
                                                        {{ $task->due_date->format('M d, Y g:i A') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        @if($task->is_hotel_concept)
                                            {{-- This one is not ticked off: it closes when Front Desk
                                                 submits the concept itself, over in My Team. --}}
                                            <button type="button" onclick="showSection('group')"
                                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10 transition">
                                                <span class="iconify text-sm" data-icon="mdi:lightbulb-outline"></span>
                                                Open Hotel Concept
                                            </button>
                                        @elseif($isMine)
                                            {{-- The real affordance; the circle above is too small to be the only target. --}}
                                            <form method="POST" action="{{ route('students.tasks.complete', $task) }}" class="shrink-0">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold transition
                                                        {{ $task->needs_revision
                                                            ? 'text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100'
                                                            : 'text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10' }}">
                                                    <span class="iconify text-sm" data-icon="{{ $task->needs_revision ? 'mdi:send-outline' : 'mdi:check-circle-outline' }}"></span>
                                                    {{ $task->needs_revision ? 'Resubmit' : 'Mark as done' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="shrink-0 text-[10px] font-semibold text-slate-300 whitespace-nowrap">Teammate's task</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border border-slate-100 px-6 py-12 text-center">
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
                                                <p class="text-[11px] text-slate-300 mt-1">{{ $task->due_date->format('M d, Y g:i A') }}</p>
                                            @endif
                                        </div>
                                        <span class="text-[9px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Done</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
            submitted: 'Submitted to your faculty. You can still improve it until they choose.',
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
            }

            paintTeamHeaderConcept();
        }

        /* The concept faculty chose, in the team header. Once one slot's payload
           comes back approved, that is the team's identity now — the proposals
           panel below has nothing left to do, so it hides and this fills in. */
        function paintTeamHeaderConcept() {
            const container = document.getElementById('teamHeaderConcept');
            const panelCard = document.getElementById('conceptPanelCard');
            if (!container) return;

            const approved = (conceptState?.slots || [])
                .map((entry) => entry.concept)
                .find((concept) => concept && concept.status === 'approved');

            if (panelCard) panelCard.classList.toggle('hidden', !!approved);

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
                '<div class="flex items-start justify-between gap-3 flex-wrap">'
                    + '<div class="min-w-0">'
                        + '<p class="text-white/50 text-[9px] font-bold uppercase tracking-[0.15em]">Official Hotel Concept</p>'
                        + '<h4 class="text-base font-extrabold text-white leading-tight mt-0.5">' + conceptEscape(approved.title) + '</h4>'
                    + '</div>'
                    + '<span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full bg-white/15 border border-white/20 text-[10px] font-bold text-white">'
                        + conceptEscape(approved.hotel_type_label) + '</span>'
                + '</div>'
                + '<p id="teamHeaderConceptDesc" class="text-[11px] text-white/70 leading-relaxed mt-2 whitespace-pre-line' + (isLong ? ' line-clamp-3' : '') + '">'
                    + conceptEscape(description) + '</p>'
                + (isLong
                    ? '<button type="button" onclick="toggleTeamHeaderConceptDesc(this)" class="mt-1.5 text-[10px] font-bold text-white/80 hover:text-white underline underline-offset-2">Show more</button>'
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

        /* Hand both concepts to faculty. The team can keep improving either one
           afterward — submitting only starts the review, it does not lock anything. */
        async function submitHotelConcepts() {
            const confirmed = await Swal.fire({
                icon: 'question',
                title: 'Submit your hotel concepts?',
                text: 'Submit your hotel concepts to your faculty? Your team can keep improving either concept until your faculty approves one.',
                showCancelButton: true,
                confirmButtonText: 'Submit',
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

        // ── Completed tasks toggle ──
        function toggleCompletedTasks() {
            const list = document.getElementById('completedTasksList');
            const chevron = document.getElementById('completedChevron');
            list.classList.toggle('hidden');
            chevron.style.transform = list.classList.contains('hidden') ? '' : 'rotate(180deg)';
        }

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
</body>
</html>