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
                        <p class="text-[10px] text-white">Student</p>
                    </div>
                    <span class="iconify text-white text-sm transition-transform" data-icon="mdi:chevron-up" id="profileChevron"></span>
                </button>
                <div class="absolute bottom-full left-0 mb-2 w-full rounded-xl border border-white/10 bg-sidebar-hover shadow-2xl p-1.5 hidden" id="studentProfileDropdown" role="menu">
                    <a href="#group" onclick="showSection('group'); closeStudentProfileMenu(); closeMobileSidebar();" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-white hover:bg-white/[0.06] transition-colors" role="menuitem">
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
        <main class="flex-1 px-4 sm:px-6 py-3 overflow-y-auto">
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

            </div>


            <!-- ==================== GROUP SECTION ==================== -->
            <div id="group-section" class="section-content hidden fade-in space-y-3">
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900">My Group</h2>

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
                    </div>

                    <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
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
                                    $memberInitials = strtoupper(substr($memberName, 0, 1));
                                    $sp = strpos($memberName, ' ');
                                    if ($sp !== false) $memberInitials .= strtoupper(substr($memberName, $sp + 1, 1));
                                @endphp
                                <div class="px-4 py-2.5 flex items-center gap-3 hover:bg-slate-50/50 transition-colors">
                                    <span class="text-xs font-bold text-slate-300 w-5 text-center shrink-0">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="w-9 h-9 {{ $isCurrentUser ? 'brand-gradient shadow-md shadow-brand/20' : 'bg-slate-100' }} rounded-xl flex items-center justify-center shrink-0">
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
                                    @if(!$isCurrentUser)
                                        <button type="button"
                                            data-user-id="{{ $getMemberValue($member, 'id') }}"
                                            data-student-id="{{ $getMemberValue($member, 'student_id') }}"
                                            data-name="{{ e($memberName) }}"
                                            data-roles="{{ e(json_encode(array_values($mRoles))) }}"
                                            onclick="viewMemberActivityFromBtn(this)"
                                            class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10 transition"
                                            title="View activity logs">
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
            </div>

            <!-- ==================== TASKS SECTION ==================== -->
            <div id="tasks-section" class="section-content hidden fade-in space-y-4">
                @php
                    $templateRouteByRole = [
                        'front_desk' => 'students.frontdesk',
                        'room_management' => 'students.roommanagement',
                        'restaurant_management' => 'students.restaurant',
                        'maintenance' => 'students.maintenance',
                        'housekeeping' => 'students.housekeeping',
                    ];
                    $templateRole = collect($studentRoles ?? [])->first(
                        fn ($role) => isset($templateRouteByRole[$role])
                    );
                    $templateRoute = $templateRole ? $templateRouteByRole[$templateRole] : null;
                @endphp
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 mb-0.5">My Tasks</h2>
                        <p class="text-sm text-slate-400">Tasks assigned to your role{{ count($studentRoles ?? []) !== 1 ? 's' : '' }}</p>
                    </div>
                    @if(!empty($studentRoles))
                        <div class="flex flex-wrap items-center gap-3">
                            @if($templateRoute)
                                <a href="{{ route($templateRoute) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 brand-gradient text-white text-xs font-bold rounded-xl shadow-lg shadow-brand/20 hover:opacity-90 transition-opacity">
                                    <span class="iconify text-base" data-icon="mdi:palette-outline"></span>
                                    Customize
                                </a>
                            @endif
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
                                    @endphp
                                    <div class="task-row px-4 py-3 flex items-start gap-3">
                                        <div class="mt-1.5 w-3 h-3 rounded-full border-2 border-slate-200 shrink-0 hover:border-brand hover:bg-brand-soft cursor-pointer transition-colors"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p class="text-sm font-bold text-slate-800">{{ $task->title }}</p>
                                            </div>
                                            @if($task->description)
                                                <p class="text-xs text-slate-400 mt-1.5 leading-relaxed">{{ $task->description }}</p>
                                            @endif
                                            @if($task->due_date)
                                                <div class="flex items-center gap-3 mt-3">
                                                    <span class="flex items-center gap-1 text-[11px] {{ $isOverdue ? 'text-red-500 font-semibold' : 'text-slate-400' }}">
                                                        <span class="iconify text-xs" data-icon="mdi:calendar-outline"></span>
                                                        {{ $task->due_date->format('M d, Y') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
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
                    <p class="text-sm text-slate-400">Your personal task activity history only.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">My Activity</p>
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

    @php
        $teamActivityLogsPayload = ($teamActivityLogs ?? collect())->map(function ($task) {
            return [
                'title' => $task->title,
                'status' => $task->status,
                'role' => (string) ($task->role ?? ''),
                'assigned_to' => $task->assigned_to ? (string) $task->assigned_to : '',
                'student_user_id' => $task->student?->user_id ? (string) $task->student->user_id : '',
                'updated_at' => optional($task->updated_at)->diffForHumans(),
            ];
        })->values();
    @endphp

    <script>
        const memberActivityRoleLabels = {
            front_desk: 'Front Desk',
            restaurant_management: 'Restaurant',
            room_management: 'Room Mgmt',
            maintenance: 'Maintenance',
            housekeeping: 'Housekeeping',
        };

        const teamActivityLogsData = @json($teamActivityLogsPayload);

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
            const labels = { home: 'Dashboard', group: 'My Group', tasks: 'Tasks', activity: 'Activity Logs', reports: 'Reports' };
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

        function viewMemberActivityFromBtn(btn) {
            if (!btn) return;
            let roles = [];
            try {
                roles = JSON.parse(btn.getAttribute('data-roles') || '[]');
            } catch (e) {
                roles = [];
            }
            openMemberActivityModal({
                user_id: btn.getAttribute('data-user-id') || '',
                student_id: btn.getAttribute('data-student-id') || '',
                name: btn.getAttribute('data-name') || 'Team member',
                roles: Array.isArray(roles) ? roles : [],
            });
        }

        function openMemberActivityModal(member) {
            if (!member) return;

            const roles = Array.isArray(member.roles) ? member.roles.map(String) : [];
            const userId = member.user_id != null ? String(member.user_id) : '';
            const roleLabels = roles.map((r) => memberActivityRoleLabels[r] || r).filter(Boolean);

            document.getElementById('memberActivityModalTitle').textContent = (member.name || 'Team member') + ' · Activity Logs';
            document.getElementById('memberActivityModalSubtitle').textContent = roleLabels.length
                ? roleLabels.join(', ')
                : 'Team member activity';

            const filtered = (teamActivityLogsData || []).filter((task) => {
                const role = String(task.role || '');
                const assignedTo = String(task.assigned_to || '');
                const studentUserId = String(task.student_user_id || '');
                return (userId !== '' && (assignedTo === userId || studentUserId === userId))
                    || (roles.length > 0 && roles.includes(role));
            });

            const body = document.getElementById('memberActivityModalBody');
            body.innerHTML = filtered.length
                ? filtered.map((task) => {
                    const done = task.status === 'archived';
                    const roleLabel = memberActivityRoleLabels[task.role] || task.role || '—';
                    return `
                        <div class="px-5 py-3.5 flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 ${done ? 'bg-emerald-50 text-emerald-500' : 'bg-blue-50 text-blue-500'}">
                                <span class="iconify text-lg" data-icon="${done ? 'mdi:check-circle-outline' : 'mdi:clipboard-plus-outline'}"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 truncate">${escapeMemberHtml((done ? 'Completed' : 'Assigned') + ': ' + (task.title || '—'))}</p>
                                <p class="text-xs text-slate-400 mt-0.5">${escapeMemberHtml(roleLabel)} · ${escapeMemberHtml(task.updated_at || '—')}</p>
                            </div>
                        </div>
                    `;
                }).join('')
                : `<div class="px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-400">No activity for this member</p>
                        <p class="text-xs text-slate-300 mt-1">Assigned or completed tasks for their role will show here.</p>
                   </div>`;

            if (window.Iconify && typeof window.Iconify.scan === 'function') {
                window.Iconify.scan(body);
            }

            document.getElementById('memberActivityModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
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
            const allowed = ['home', 'group', 'tasks', 'activity', 'reports'];
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
</body>
</html>