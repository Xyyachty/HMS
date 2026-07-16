@extends('faculty.layout.app')

@section('page_title', 'List of Teams Information')
@section('role_active', 'active')

@section('content')

<style>
    /* ── Main tab bar ── */
    .main-tab-btn {
        position:relative; display:inline-flex; align-items:center; gap:.5rem;
        padding:.65rem 1.25rem; border-radius:1rem; font-size:.8rem; font-weight:700;
        color:#64748B; cursor:pointer; transition:all .25s ease; border:1.5px solid #e2e8f0;
        background:#fff; white-space:nowrap; user-select:none;
    }
    .main-tab-btn:hover { border-color:#F9A8D4; color:#DB2777; background:#FDF2F8; }
    .main-tab-btn.active {
        background:linear-gradient(135deg,#F472B6,#DB2777,#9D174D);
        color:#fff; border-color:transparent;
        box-shadow:0 8px 20px -4px rgba(219,39,119,.35);
    }
    .main-tab-btn .tab-badge {
        padding:.1rem .45rem; border-radius:9999px; font-size:.65rem; font-weight:800;
        background:rgba(255,255,255,.25); color:#fff;
    }
    .main-tab-btn:not(.active) .tab-badge { background:#f1f5f9; color:#64748B; }

    /* ── Tab panel animation ── */
    .tab-panel { display:none; animation:fadeInUp .3s ease; }
    .tab-panel.active { display:block; }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Priority badges ── */
    .badge-high   { background:#FEF2F2; color:#B91C1C; border:1px solid #FECACA; }
    .badge-medium { background:#FFFBEB; color:#B45309; border:1px solid #FDE68A; }
    .badge-low    { background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; }

    /* ── Student list in form ── */
    .student-row { transition:all .2s ease; }
    .student-row:hover { background:#FDF2F8 !important; }

    /* ── Role color dots ── */
    .role-dot-front_desk            { background:#FB7185; }
    .role-dot-restaurant_management { background:#FBBF24; }
    .role-dot-room_management       { background:#DB2777; }
    .role-dot-maintenance           { background:#A855F7; }
    .role-dot-housekeeping          { background:#14B8A6; }

    /* ── Teams table layout ── */
    #teamsTable {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
    }
    #teamsTable th,
    #teamsTable td {
        vertical-align: middle;
    }
    #teamsTable .col-team { width: 18%; }
    #teamsTable .col-count { width: 12%; }
    #teamsTable .col-roles { width: 50%; }
    #teamsTable .col-action { width: 20%; }
    #teamsTable .truncate-cell {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
        display: block;
    }
    #teamsTable .member-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        max-width: 100%;
        padding: 2px 8px;
        border-radius: 9999px;
        background: #f1f5f9;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        line-height: 1.35;
    }
    #teamsTable .member-chip .chip-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 7.5rem;
    }
    #teamsTable .role-mini {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        color: #94a3b8;
        font-weight: 500;
        white-space: nowrap;
    }
</style>

{{-- ═══════════════════════════════════════════════
     SUCCESS / ERROR FLASH
═══════════════════════════════════════════════ --}}
@if(session('success'))
<div id="flashBanner" class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-2xl text-sm font-semibold">
    <span class="iconify text-lg" data-icon="mdi:check-circle-outline"></span>
    {{ session('success') }}
    <button onclick="document.getElementById('flashBanner').remove()" class="ml-auto text-green-400 hover:text-green-600">
        <span class="iconify" data-icon="mdi:close"></span>
    </button>
</div>
@endif

{{-- ═══════════════════════════════════════════════
     TAB BAR
═══════════════════════════════════════════════ --}}
@php
    $activeTab = request('tab', 'teams');
    $validTabs = ['teams', 'create_task'];
    if (!in_array($activeTab, $validTabs)) $activeTab = 'teams';

    // If there are validation errors, keep the correct tab open
    if ($errors->has('title') || $errors->has('role') || $errors->has('priority')) {
        $activeTab = 'create_task';
    }
@endphp

{{-- ═══════════════════════════════════════════════
     TAB 1 — TEAMS (View all groups)
═══════════════════════════════════════════════ --}}
<div id="panel-teams" class="tab-panel {{ $activeTab === 'teams' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'activeClass' => $activeClass ?? null,
        ])

        @php
            $roleLabels = [
                'front_desk'            => 'Front Desk',
                'restaurant_management' => 'Restaurant',
                'room_management'       => 'Rooms',
                'maintenance'           => 'Maintenance',
                'housekeeping'          => 'Housekeeping',
            ];
            $roleShort = [
                'front_desk'            => 'FD',
                'restaurant_management' => 'RST',
                'room_management'       => 'RM',
                'maintenance'           => 'MNT',
                'housekeeping'          => 'HK',
            ];
        @endphp

        <div class="overflow-x-auto">
            <table id="teamsTable" class="w-full text-sm text-slate-700 min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="col-team text-left px-3 py-2.5 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Team Name</th>
                        <th class="col-count text-left px-3 py-2.5 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Total Member</th>
                        <th class="col-roles text-left px-3 py-2.5 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Members &amp; Roles</th>
                        <th class="col-action text-center px-3 py-2.5 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groups ?? [] as $groupName => $groupMembers)
                        @php
                            $createdAt  = optional($groupMembers->first()->created_at)->format('M d, Y');
                            $memberData = [];
                            foreach ($groupMembers as $m) {
                                $u = $m->student?->user;
                                $dn = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));
                                $dn = $dn !== '' ? $dn : ($u?->name ?? 'Student');
                                $memberRoles = $m->roles->pluck('role')->toArray();
                                $memberData[] = [
                                    'student_id' => $m->student_id,
                                    'name'       => $dn,
                                    'roles'      => $memberRoles,
                                    'role_labels' => array_map(fn($r) => $roleLabels[$r] ?? $r, $memberRoles),
                                ];
                            }
                            $memberJson = json_encode($memberData);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-3 py-2.5">
                                <span class="font-semibold text-slate-800 text-sm truncate-cell" title="{{ $groupName }}">{{ $groupName }}</span>
                                @if($createdAt)
                                    <span class="text-[11px] text-slate-400 truncate-cell">{{ $createdAt }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-full text-[11px] font-bold whitespace-nowrap">
                                    <span class="iconify text-xs" data-icon="mdi:account-multiple-outline"></span>
                                    {{ $groupMembers->count() }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($groupMembers as $member)
                                        @php
                                            $u  = $member->student?->user;
                                            $last = $u?->last_name ?? '';
                                            $first = $u?->first_name ?? '';
                                            $shortName = trim($last !== '' ? ($last . ($first !== '' ? ', ' . mb_substr($first, 0, 1) . '.' : '')) : ($u?->name ?? 'Student'));
                                            $dn = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));
                                            $dn = $dn !== '' ? $dn : ($u?->name ?? 'Student');
                                            $memberRoles = $member->roles->pluck('role')->toArray();
                                            $roleTitle = implode(', ', array_map(fn($r) => $roleLabels[$r] ?? $r, $memberRoles));
                                        @endphp
                                        <span class="member-chip" title="{{ $dn }}{{ $roleTitle !== '' ? ' — ' . $roleTitle : '' }}">
                                            <span class="chip-name">{{ $shortName }}</span>
                                            @foreach($memberRoles as $mRole)
                                                <span class="role-mini">
                                                    <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $mRole }} shrink-0"></span>
                                                    {{ $roleShort[$mRole] ?? $mRole }}
                                                </span>
                                            @endforeach
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button onclick='openTeamModal({{ json_encode($groupName) }}, {{ $memberJson }}, {{ json_encode($createdAt) }}, {{ json_encode($teamActivityByGroup[$groupName] ?? []) }})'
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[11px] font-bold hover:bg-rose-100 transition whitespace-nowrap">
                                        <span class="iconify text-sm" data-icon="mdi:eye-outline"></span> View
                                    </button>
                                    <button
                                        type="button"
                                        onclick='openUpdateModal(@json($groupName), @json($memberData))'
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-brand-soft text-brand border border-brand/10 rounded-lg text-[11px] font-bold hover:bg-brand/10 transition whitespace-nowrap">
                                        <span class="iconify text-sm" data-icon="mdi:pencil-outline"></span> Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-slate-300 text-2xl" data-icon="mdi:account-group-outline"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">
                                    @if($activeClass)
                                        No teams in {{ $activeClass->name }} yet.
                                    @else
                                        No teams created yet.
                                    @endif
                                </p>
                                <button onclick="openCreateTeamModal()"
                                    class="mt-3 px-4 py-2 bg-brand text-white text-xs font-bold rounded-xl hover:scale-105 transition shadow-md shadow-brand/20">
                                    Add Team{{ $activeClass ? ' for ' . $activeClass->name : '' }}
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Team Info Modal -->
<div id="teamInfoModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTeamModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-h-[90vh] flex flex-col" style="max-width: 52rem;">
        <!-- Modal Header -->
        <div class="bg-rose-50 px-4 py-3 border-b border-rose-100 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h4 class="font-bold text-rose-700 text-sm flex items-center gap-1.5 min-w-0">
                <span class="iconify text-base shrink-0" data-icon="mdi:account-group-outline"></span>
                <span class="truncate">Team Details<span id="modalTeamNameSuffix" class="font-semibold text-rose-500/80"></span></span>
            </h4>
            <button onclick="closeTeamModal()" class="text-slate-400 hover:text-rose-500 hover:bg-white w-7 h-7 rounded-full transition flex items-center justify-center shrink-0">
                <span class="iconify text-lg" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 p-4 space-y-4">
            <!-- Members Table -->
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Team Members & Roles</p>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm" style="table-layout: fixed;">
                        <colgroup>
                            <col style="width: 2.5rem;">
                            <col>
                            <col style="width: 10rem;">
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Member</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody id="teamModalMembersBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Logs -->
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Activity Logs</p>
                    <span id="teamModalActivityMeta" class="text-[10px] font-semibold text-slate-400"></span>
                </div>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm" style="table-layout: fixed;">
                        <colgroup>
                            <col>
                            <col style="width: 7rem;">
                            <col style="width: 6.5rem;">
                            <col style="width: 6rem;">
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Task</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Updated</th>
                            </tr>
                        </thead>
                        <tbody id="teamModalActivityBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                    <div id="teamModalActivityPager" class="hidden px-3 py-2 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                        <button type="button" id="teamModalActivityPrev"
                            class="px-3 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                            Previous
                        </button>
                        <span id="teamModalActivityPageLabel" class="text-[11px] font-semibold text-slate-500"></span>
                        <button type="button" id="teamModalActivityNext"
                            class="px-3 py-1 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-4 py-2.5 border-t border-slate-100 flex justify-end rounded-b-2xl flex-shrink-0 bg-slate-50/50">
            <button onclick="closeTeamModal()" class="px-3.5 py-1.5 rounded-lg bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition font-semibold text-xs">
                Close
            </button>
        </div>
    </div>
</div>


<!-- Add Team Modal -->
<div id="createTeamModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCreateTeamModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-brand-soft px-6 py-4 border-b border-brand/10 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h4 class="font-bold text-brand text-base flex items-center gap-2">
                <span class="iconify text-lg" data-icon="mdi:account-multiple-plus-outline"></span>
                Add Team
            </h4>
            <button onclick="closeCreateTeamModal()" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Tabs -->
        <div class="flex border-b border-slate-200 bg-slate-50 flex-shrink-0">
            <button onclick="switchCreateModalTab('add_team')" id="modal-tab-add_team"
                class="flex-1 py-3 text-sm font-bold text-center transition border-b-2 border-brand text-brand">
                <span class="iconify inline-block mr-1.5" data-icon="mdi:account-plus-outline"></span> Add Team
            </button>
            <button onclick="switchCreateModalTab('insert')" id="modal-tab-insert"
                class="flex-1 py-3 text-sm font-bold text-center transition border-b-2 border-transparent text-slate-400 hover:text-slate-600">
                <span class="iconify inline-block mr-1.5" data-icon="mdi:account-arrow-right-outline"></span> Insert
            </button>
        </div>

        @php
            $teamRoleOptions = [
                'front_desk' => 'Front Desk',
                'restaurant_management' => 'Restaurant',
                'room_management' => 'Room Mgmt',
                'maintenance' => 'Maintenance',
                'housekeeping' => 'Housekeeping',
            ];
        @endphp

        <!-- Tab Panel: Add Team -->
        <div id="modal-panel-add_team" class="flex-1 min-h-0 overflow-y-auto">
            <form method="POST" id="createTeamForm" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="create_team">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                @if($errors->any())
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-6 space-y-5">
                    {{-- Step 1: Team name --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Team Name <span class="text-red-400">*</span></label>
                        <input name="group_name" type="text" placeholder="e.g. Front Desk Team A"
                            value="{{ old('group_name') }}"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>

                    {{-- Tools row --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                            <input type="text" id="createStudentSearch" placeholder="Search students by name or ID..."
                                class="w-full h-10 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                oninput="filterTeamStudentList('create')">
                        </div>
                        <button type="button" id="createRandomizeBtn"
                            class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:border-brand/40 hover:text-brand transition inline-flex items-center justify-center gap-1.5 shrink-0">
                            <span class="iconify" data-icon="mdi:shuffle-variant"></span> Randomize 4
                        </button>
                        <span id="createSelectedCount" class="text-xs font-bold text-slate-500 whitespace-nowrap sm:min-w-[7rem] text-right">0 selected</span>
                    </div>
                    <p id="createRandomizeNote" class="text-[11px] text-slate-400 -mt-3"></p>

                    {{-- Role legend --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Roles</span>
                        @foreach($teamRoleOptions as $rk => $rl)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-600">
                                <span class="w-2 h-2 rounded-full role-dot-{{ $rk }}"></span>{{ $rl }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Members --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Members & Roles <span class="text-red-400">*</span></label>
                            <span class="text-[11px] text-slate-400">Defaults apply if none selected · {{ ($students ?? collect())->count() }} available</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="createStudentList">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                        $selectedMembers = array_map('intval', old('members', []));
                                        $selectedRoles = old('member_roles.' . $sk, []);
                                        if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                        $searchBlob = strtolower($dn . ' ' . $student->student_id);
                                    @endphp
                                    <div class="team-student-card create-student-card rounded-xl bg-white border border-slate-200 p-3"
                                         data-search="{{ $searchBlob }}">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="create-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}
                                                onchange="onTeamMemberToggle(this, 'create')">
                                            <div class="w-9 h-9 rounded-lg bg-brand-soft text-brand text-xs font-bold flex items-center justify-center shrink-0">
                                                {{ strtoupper(mb_substr($dn, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">
                                                    #{{ $student->student_id }}
                                                    @if($student->facultyClass)
                                                        · {{ $student->facultyClass->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                        <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-5 gap-1.5">
                                            @foreach($teamRoleOptions as $rk => $rl)
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="create-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}>
                                                    <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $rk }} shrink-0"></span>
                                                    <span class="truncate">{{ $rl }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                        <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                        <p class="text-sm font-semibold">No unassigned students</p>
                                        <p class="text-xs text-slate-300">Add students first, or they may already be in a team.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab Panel: Insert -->
        <div id="modal-panel-insert" class="flex-1 min-h-0 overflow-y-auto hidden">
            <form method="POST" id="insertStudentForm" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="insert_student">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Existing Team <span class="text-red-400">*</span></label>
                        <select name="group_name"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                            <option value="">Select a team...</option>
                            @foreach($groups ?? [] as $groupName => $members)
                                <option value="{{ $groupName }}" {{ old('group_name') === $groupName ? 'selected' : '' }}>{{ $groupName }} ({{ $members->count() }} {{ Str::plural('member', $members->count()) }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1.5">Choose a team, then pick unassigned students to add with roles.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                            <input type="text" id="insertStudentSearch" placeholder="Search students by name or ID..."
                                class="w-full h-10 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                oninput="filterTeamStudentList('insert')">
                        </div>
                        <span id="insertSelectedCount" class="text-xs font-bold text-slate-500 whitespace-nowrap sm:min-w-[7rem] text-right">0 selected</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Roles</span>
                        @foreach($teamRoleOptions as $rk => $rl)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-600">
                                <span class="w-2 h-2 rounded-full role-dot-{{ $rk }}"></span>{{ $rl }}
                            </span>
                        @endforeach
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Ungrouped Students <span class="text-red-400">*</span></label>
                            <span class="text-[11px] text-slate-400">Defaults apply if none selected · {{ ($students ?? collect())->count() }} available</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="insertStudentList">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                        $selectedMembers = array_map('intval', old('members', []));
                                        $selectedRoles = old('member_roles.' . $sk, []);
                                        if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                        $searchBlob = strtolower($dn . ' ' . $student->student_id);
                                    @endphp
                                    <div class="team-student-card insert-student-card rounded-xl bg-white border border-slate-200 p-3"
                                         data-search="{{ $searchBlob }}">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="insert-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}
                                                onchange="onTeamMemberToggle(this, 'insert')">
                                            <div class="w-9 h-9 rounded-lg bg-brand-soft text-brand text-xs font-bold flex items-center justify-center shrink-0">
                                                {{ strtoupper(mb_substr($dn, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">
                                                    #{{ $student->student_id }}
                                                    @if($student->facultyClass)
                                                        · {{ $student->facultyClass->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                        <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-5 gap-1.5">
                                            @foreach($teamRoleOptions as $rk => $rl)
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="insert-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}>
                                                    <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $rk }} shrink-0"></span>
                                                    <span class="truncate">{{ $rl }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                        <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-check-outline"></span>
                                        <p class="text-sm font-semibold">No ungrouped students</p>
                                        <p class="text-xs text-slate-300">All of your students are already on a team.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 flex-shrink-0">
            <button type="button" onclick="closeCreateTeamModal()" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
            <button type="button" onclick="submitActiveModalTab()" class="px-6 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save-outline"></span> Save
            </button>
        </div>
    </div>
</div>


<!-- Update Team Modal -->
<div id="updateTeamModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeUpdateModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h4 class="font-bold text-amber-700 text-base flex items-center gap-2">
                <span class="iconify text-lg" data-icon="mdi:pencil-box-outline"></span>
                Update Team
            </h4>
            <button onclick="closeUpdateModal()" class="text-slate-400 hover:text-amber-500 hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Content (scrollable area) -->
        <div class="flex-1 min-h-0 overflow-y-auto">
            <form method="POST" id="updateTeamForm" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_source" value="update_team">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                <div class="p-6 space-y-4">
                    {{-- Top row: Team Name + Editing Info --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Team Name <span class="text-red-400">*</span></label>
                            <input id="updateGroupNameInput" name="group_name" type="text" placeholder="Team name"
                                class="w-full h-10 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 flex items-center gap-3">
                            <span class="iconify text-amber-500 text-xl" data-icon="mdi:information-outline"></span>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Editing</p>
                                <p id="updateTeamEditingLabel" class="text-sm font-bold text-amber-700 truncate">—</p>
                            </div>
                        </div>
                    </div>

                    {{-- Role legend --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2">
                        <div class="flex items-center gap-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Legend:</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Restaurant Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-600">
                                        <span class="w-2 h-2 rounded-full role-dot-{{ $rk }} flex-shrink-0"></span>{{ $rl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Template edit permissions (faculty grants) --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Template edit permissions</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Grant a member access to edit another role’s hotel website section.</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 mb-3">
                            <select id="grantStudentSelect" class="flex-1 h-10 px-3 rounded-xl border border-slate-200 bg-slate-50 text-sm">
                                <option value="">Select member…</option>
                            </select>
                            <select id="grantRoleSelect" class="h-10 px-3 rounded-xl border border-slate-200 bg-slate-50 text-sm">
                                @foreach(\App\Support\HotelTemplateBuilder::ROLES as $rk => $rl)
                                    <option value="{{ $rk }}">{{ $rl }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="grantEditBtn"
                                class="h-10 px-4 rounded-xl bg-brand text-white text-xs font-bold hover:opacity-90">Grant</button>
                            <button type="button" id="revokeEditBtn"
                                class="h-10 px-4 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">Revoke</button>
                        </div>
                        <div id="grantStatus" class="text-[11px] text-slate-400"></div>
                    </div>

                    {{-- Members & Roles --}}
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Members & Roles <span class="text-red-400">*</span></label>
                            <span class="text-[11px] text-slate-400 sm:ml-auto">Only students already on this team. Use Add Team → Insert to add more.</span>
                        </div>
                        <div class="relative mb-2">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                            <input type="text" id="updateStudentSearch" placeholder="Search members by name or ID..."
                                class="w-full h-10 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                oninput="filterUpdateStudentList()">
                        </div>
                        <p class="text-xs text-slate-400 mb-2">Uncheck to remove a member, or change their roles. Save to apply.</p>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="updateMemberList">
                                @forelse($allStudents ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                        $searchBlob = strtolower($dn . ' ' . $student->student_id);
                                        $teamNames = collect($studentTeamMap[$sk] ?? [])->values()->all();
                                    @endphp
                                    <div class="student-row update-student-row team-student-card hidden rounded-xl bg-white px-3 py-3 border border-slate-200"
                                         data-student-id="{{ $sk }}"
                                         data-search="{{ $searchBlob }}"
                                         data-teams="{{ e(implode('|', $teamNames)) }}">
                                        {{-- Student info row --}}
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="update-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                onchange="onUpdateMemberToggle(this)">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <div class="text-sm font-semibold text-slate-700 truncate">{{ $dn }}</div>
                                                    <span class="update-on-team-badge hidden text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">On this team</span>
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-mono">
                                                    #{{ $student->student_id }}
                                                    @if($student->facultyClass)
                                                        · {{ $student->facultyClass->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Role checkboxes row --}}
                                        <div class="flex flex-wrap gap-1.5 mt-2 ml-8">
                                            @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Rest. Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                                <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="update-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        data-student-id="{{ $sk }}">
                                                    {{ $rl }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                        <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                        <p class="text-sm font-semibold">No students found</p>
                                        <p class="text-xs text-slate-300">Add students under Manage Students first.</p>
                                    </div>
                                @endforelse
                                <div id="updateNoMembersNote" class="hidden flex flex-col items-center gap-2 py-10 text-slate-400">
                                    <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-group-outline"></span>
                                    <p class="text-sm font-semibold">No members on this team</p>
                                    <p class="text-xs text-slate-300">Use Add Team → Insert to add students.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer (always visible, fixed at bottom) -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 flex-shrink-0">
            <button type="button" onclick="closeUpdateModal()" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
            <button type="button" onclick="document.getElementById('updateTeamForm').submit()" class="px-6 py-2.5 bg-amber-500 text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-amber-400/30 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save-edit-outline"></span> Save Changes
            </button>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════
     TAB 3 — CREATE TASK
═══════════════════════════════════════════════ --}}
<div id="panel-create_task" class="tab-panel {{ $activeTab === 'create_task' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'activeClass' => $activeClass ?? null,
        ])

        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 bg-brand-soft flex items-center gap-3">
            <div class="w-10 h-10 bg-brand/10 rounded-xl flex items-center justify-center">
                <span class="iconify text-brand text-xl" data-icon="mdi:clipboard-plus-outline"></span>
            </div>
            <div>
                <h3 class="font-bold text-brand text-base">Set New Task</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pick a department, select tasks, set a due date, and assign.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('faculty.tasks.store') }}" id="taskAssignForm">
            @csrf

            @if($errors->any() && $activeTab === 'create_task')
                <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                    <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="p-6 space-y-6">
                @php
                    $tasksByRole = [
                        'front_desk' => [
                            ['title' => 'Review check-in procedures', 'description' => 'Review and practice standard check-in procedures for guests', 'priority' => 'high'],
                            ['title' => 'Update guest records system', 'description' => 'Update and maintain accurate guest information in the system', 'priority' => 'medium'],
                            ['title' => 'Handle guest complaints', 'description' => 'Learn proper procedures for handling guest complaints professionally', 'priority' => 'high'],
                            ['title' => 'Manage room reservations', 'description' => 'Process and confirm room reservations accurately', 'priority' => 'medium'],
                            ['title' => 'Process check-out procedures', 'description' => 'Complete guest check-out and billing procedures', 'priority' => 'medium'],
                            ['title' => 'Phone etiquette training', 'description' => 'Practice professional phone communication with guests', 'priority' => 'low'],
                        ],
                        'restaurant_management' => [
                            ['title' => 'Menu planning and review', 'description' => 'Review current menu items and suggest improvements', 'priority' => 'medium'],
                            ['title' => 'Kitchen inventory audit', 'description' => 'Conduct thorough inventory check of kitchen supplies', 'priority' => 'high'],
                            ['title' => 'Food safety compliance', 'description' => 'Ensure all food safety protocols are being followed', 'priority' => 'high'],
                            ['title' => 'Staff scheduling coordination', 'description' => 'Coordinate and manage restaurant staff schedules', 'priority' => 'medium'],
                            ['title' => 'Customer service training', 'description' => 'Train staff on excellent customer service practices', 'priority' => 'medium'],
                            ['title' => 'Table arrangement planning', 'description' => 'Optimize table arrangements for better service flow', 'priority' => 'low'],
                        ],
                        'room_management' => [
                            ['title' => 'Room inspection checklist', 'description' => 'Perform detailed room inspection using standard checklist', 'priority' => 'high'],
                            ['title' => 'Housekeeping schedule review', 'description' => 'Review and optimize housekeeping schedules', 'priority' => 'medium'],
                            ['title' => 'Linen inventory management', 'description' => 'Track and manage linen inventory levels', 'priority' => 'medium'],
                            ['title' => 'Room maintenance reporting', 'description' => 'Report and track room maintenance issues', 'priority' => 'high'],
                            ['title' => 'Cleaning supply inventory', 'description' => 'Monitor and reorder cleaning supplies as needed', 'priority' => 'medium'],
                            ['title' => 'Quality standards audit', 'description' => 'Audit rooms against quality standards checklist', 'priority' => 'low'],
                        ],
                        'maintenance' => [
                            ['title' => 'Facility maintenance inspection', 'description' => 'Conduct comprehensive facility inspection for maintenance needs', 'priority' => 'high'],
                            ['title' => 'Equipment safety check', 'description' => 'Perform safety checks on all hotel equipment', 'priority' => 'high'],
                            ['title' => 'HVAC system maintenance', 'description' => 'Inspect and maintain heating/cooling systems', 'priority' => 'medium'],
                            ['title' => 'Plumbing system inspection', 'description' => 'Check all plumbing fixtures and systems', 'priority' => 'medium'],
                            ['title' => 'Electrical system check', 'description' => 'Inspect electrical systems and fix issues', 'priority' => 'high'],
                            ['title' => 'Preventive maintenance log', 'description' => 'Update and maintain preventive maintenance records', 'priority' => 'low'],
                        ],
                        'housekeeping' => [
                            ['title' => 'Room deep cleaning', 'description' => 'Perform thorough deep cleaning of assigned guest rooms', 'priority' => 'high'],
                            ['title' => 'Linen and towel replacement', 'description' => 'Replace soiled linens and towels with fresh supplies', 'priority' => 'medium'],
                            ['title' => 'Bathroom sanitization', 'description' => 'Sanitize all bathroom fixtures, surfaces, and amenities', 'priority' => 'high'],
                            ['title' => 'Minibar restocking', 'description' => 'Restock minibar items and verify inventory counts', 'priority' => 'medium'],
                            ['title' => 'Carpet and upholstery care', 'description' => 'Vacuum carpets and clean upholstery in guest rooms', 'priority' => 'medium'],
                            ['title' => 'Amenity inventory check', 'description' => 'Verify and replenish guest room amenities and supplies', 'priority' => 'low'],
                        ],
                    ];
                @endphp

                {{-- ═══════ STEP INDICATORS ═══════ --}}
                <div class="flex items-center justify-center gap-2">
                    <div class="step-indicator flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand text-white text-xs font-bold" id="step-ind-1">
                        <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px]">1</span>
                        <span class="hidden sm:inline">Department</span>
                    </div>
                    <div class="w-8 h-0.5 bg-slate-200 rounded" id="step-line-1"></div>
                    <div class="step-indicator flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-slate-400 text-xs font-bold" id="step-ind-2">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">2</span>
                        <span class="hidden sm:inline">Tasks</span>
                    </div>
                    <div class="w-8 h-0.5 bg-slate-200 rounded" id="step-line-2"></div>
                    <div class="step-indicator flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-slate-400 text-xs font-bold" id="step-ind-3">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">3</span>
                        <span class="hidden sm:inline">Due Date</span>
                    </div>
                    <div class="w-8 h-0.5 bg-slate-200 rounded" id="step-line-3"></div>
                    <div class="step-indicator flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 text-slate-400 text-xs font-bold" id="step-ind-4">
                        <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px]">4</span>
                        <span class="hidden sm:inline">Assign</span>
                    </div>
                </div>

                {{-- ═══════ STEP 1: DEPARTMENT ═══════ --}}
                <div id="task-step-1" class="task-step">
                    <div class="text-center mb-4">
                        <h4 class="text-sm font-bold text-slate-700">Which department needs tasks?</h4>
                        <p class="text-xs text-slate-400 mt-1">Choose a department to see available tasks</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3" id="taskDeptSelector">
                        @foreach($rolesMeta ?? [] as $rKey => $rMeta)
                            <button type="button" onclick="selectDepartment('{{ $rKey }}')"
                                data-dept="{{ $rKey }}"
                                class="task-dept-btn group p-4 rounded-xl border-2 border-slate-200 bg-white hover:border-brand/40 hover:shadow-md transition-all text-center">
                                <span class="iconify text-2xl {{ $rMeta['color'] }} group-hover:scale-110 transition-transform" data-icon="{{ $rMeta['icon'] }}"></span>
                                <p class="text-xs font-bold text-slate-700 mt-2">{{ $rMeta['label'] }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ count($tasksByRole[$rKey] ?? []) }} tasks</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- ═══════ STEP 2: TASKS ═══════ --}}
                <div id="task-step-2" class="task-step hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="goToStep(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                                <span class="iconify text-slate-500" data-icon="mdi:arrow-left"></span>
                            </button>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700" id="selectedDeptTitle">Select Tasks</h4>
                                <p class="text-xs text-slate-400">Check the tasks you want to assign</p>
                            </div>
                        </div>
                        <button type="button" onclick="selectAllVisibleTasks()" class="text-xs font-semibold text-brand hover:underline">
                            Select All
                        </button>
                    </div>

                    @foreach($rolesMeta ?? [] as $rKey => $rMeta)
                        <div id="taskPanel-{{ $rKey }}" class="task-checklist-panel hidden">
                            <div class="grid grid-cols-1 gap-2 max-h-[400px] overflow-y-auto pr-1">
                                @foreach($tasksByRole[$rKey] ?? [] as $index => $task)
                                    <label class="task-checkbox-card flex items-start gap-3 p-3.5 bg-slate-50 border-2 border-slate-200 rounded-xl hover:bg-{{ $rMeta['color'] }}-50/50 hover:border-{{ $rMeta['color'] }}-200 transition cursor-pointer has-[:checked]:border-{{ $rMeta['color'] }}-400 has-[:checked]:bg-{{ $rMeta['color'] }}-50">
                                        <input type="checkbox" name="tasks[{{ $rKey }}][]" value="{{ $index }}"
                                            class="task-checkbox-{{ $rKey }} mt-0.5 rounded border-slate-300 text-{{ $rMeta['color'] }}-500 focus:ring-{{ $rMeta['color'] }}-500/30 task-check">
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-slate-800">{{ $task['title'] }}</div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $task['description'] }}</p>
                                        </div>
                                        <span class="shrink-0 mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                            {{ $task['priority'] === 'high' ? 'bg-red-100 text-red-600' : ($task->priority ?? $task['priority'] === 'medium' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500') }}">
                                            {{ $task['priority'] }}
                                        </span>
                                        <input type="hidden" name="task_titles[{{ $rKey }}][{{ $index }}]" value="{{ $task['title'] }}">
                                        <input type="hidden" name="task_descriptions[{{ $rKey }}][{{ $index }}]" value="{{ $task['description'] }}">
                                        <input type="hidden" name="task_priorities[{{ $rKey }}][{{ $index }}]" value="{{ $task['priority'] }}">
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="goToStep(3)" id="btn-to-step3"
                            class="px-5 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100" disabled>
                            <span class="iconify" data-icon="mdi:arrow-right"></span> Continue
                        </button>
                    </div>
                </div>

                {{-- ═══════ STEP 3: DUE DATE ═══════ --}}
                <div id="task-step-3" class="task-step hidden">
                    <div class="flex items-center gap-2 mb-4">
                        <button type="button" onclick="goToStep(2)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                            <span class="iconify text-slate-500" data-icon="mdi:arrow-left"></span>
                        </button>
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Set a due date?</h4>
                            <p class="text-xs text-slate-400">Optional — leave blank for no deadline</p>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 iconify text-slate-400" data-icon="mdi:calendar-outline"></span>
                            <input name="due_date" type="date" value="{{ old('due_date') }}"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" onclick="goToStep(4)"
                            class="px-5 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2">
                            <span class="iconify" data-icon="mdi:arrow-right"></span> Review & Assign
                        </button>
                    </div>
                </div>

                {{-- ═══════ STEP 4: REVIEW ═══════ --}}
                <div id="task-step-4" class="task-step hidden">
                    <div class="flex items-center gap-2 mb-4">
                        <button type="button" onclick="goToStep(3)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                            <span class="iconify text-slate-500" data-icon="mdi:arrow-left"></span>
                        </button>
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Review your assignment</h4>
                            <p class="text-xs text-slate-400">Confirm everything looks good before assigning</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center" id="reviewDeptIcon"></div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Department</p>
                                <p class="text-sm font-bold text-slate-800" id="reviewDeptName">—</p>
                            </div>
                        </div>
                        <div class="h-px bg-slate-200"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                                <span class="iconify text-amber-500" data-icon="mdi:clipboard-text-outline"></span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Tasks Selected</p>
                                <p class="text-sm font-bold text-slate-800" id="reviewTaskCount">0 tasks</p>
                            </div>
                        </div>
                        <div class="h-px bg-slate-200"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                                <span class="iconify text-blue-500" data-icon="mdi:calendar-outline"></span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Due Date</p>
                                <p class="text-sm font-bold text-slate-800" id="reviewDueDate">No deadline</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                            class="px-6 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2">
                            <span class="iconify" data-icon="mdi:check-circle-outline"></span> Set Tasks
                        </button>
                    </div>
                </div>

                {{-- ═══════ ACTIVE TASKS OVERVIEW ═══════ --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Active Tasks</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rolesMeta ?? [] as $rKey => $rMeta)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-100">
                                <span class="iconify text-xs {{ $rMeta['color'] }}" data-icon="{{ $rMeta['icon'] }}"></span>
                                <span class="text-[10px] font-bold text-slate-600">{{ $taskCounts[$rKey] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('faculty.tasks') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-brand transition">
                    <span class="iconify" data-icon="mdi:eye-outline"></span> View all tasks
                </a>
                <button type="button" onclick="resetTaskWizard()" class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition">Reset</button>
            </div>
        </form>
    </div>
</div>


{{-- ═══════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════ --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
// ── Tab switcher ────────────────────────────────
const ALL_TABS = ['teams', 'create_task'];

function switchTab(tabId) {
    ALL_TABS.forEach(id => {
        const panel = document.getElementById('panel-' + id);
        const btn   = document.getElementById('tab-btn-' + id);
        if (!panel || !btn) return;
        if (id === tabId) {
            panel.classList.add('active');
            btn.classList.add('active');
        } else {
            panel.classList.remove('active');
            btn.classList.remove('active');
        }
    });
    // Update browser URL without reloading
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    history.replaceState(null, '', url);
}

// ── Create Team Modal ──────────────────────────
function openCreateTeamModal() {
    document.getElementById('createTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreateTeamModal() {
    document.getElementById('createTeamModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ── Modal Tab Switcher ────────────────────────
let currentModalTab = 'add_team';

function switchCreateModalTab(tabId) {
    currentModalTab = tabId;

    // Toggle panels
    document.getElementById('modal-panel-add_team').classList.toggle('hidden', tabId !== 'add_team');
    document.getElementById('modal-panel-insert').classList.toggle('hidden', tabId !== 'insert');

    // Toggle tab styles
    const addTeamBtn = document.getElementById('modal-tab-add_team');
    const insertBtn = document.getElementById('modal-tab-insert');

    if (tabId === 'add_team') {
        addTeamBtn.classList.add('border-brand', 'text-brand');
        addTeamBtn.classList.remove('border-transparent', 'text-slate-400');
        insertBtn.classList.remove('border-brand', 'text-brand');
        insertBtn.classList.add('border-transparent', 'text-slate-400');
    } else {
        insertBtn.classList.add('border-brand', 'text-brand');
        insertBtn.classList.remove('border-transparent', 'text-slate-400');
        addTeamBtn.classList.remove('border-brand', 'text-brand');
        addTeamBtn.classList.add('border-transparent', 'text-slate-400');
    }
}

function submitActiveModalTab() {
    if (currentModalTab === 'add_team') {
        document.getElementById('createTeamForm').submit();
    } else {
        document.getElementById('insertStudentForm').submit();
    }
}



// ── Default hotel roles (rotate when assigning) ──
const TEAM_DEFAULT_ROLES = [
    'front_desk',
    'restaurant_management',
    'room_management',
    'maintenance',
    'housekeeping',
];

function clearStudentRoles(memberCheckbox) {
    const card = memberCheckbox.closest('.team-student-card');
    if (!card) return;
    card.querySelectorAll('input[type="checkbox"][name^="member_roles"]').forEach(cb => {
        cb.checked = false;
    });
}

function assignDefaultRoleToCard(card, roleKey) {
    if (!card || !roleKey) return;
    const roleCb = card.querySelector('input[type="checkbox"][name^="member_roles"][value="' + roleKey + '"]');
    if (roleCb) roleCb.checked = true;
}

function nextDefaultRoleIndex(mode) {
    const checked = document.querySelectorAll(
        (mode === 'insert' ? '.insert-student-checkbox' : '.create-student-checkbox') + ':checked'
    );
    // Prefer unused roles among currently selected members, else rotate by count
    const used = new Set();
    checked.forEach(cb => {
        const card = cb.closest('.team-student-card');
        if (!card) return;
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked').forEach(r => {
            used.add(r.value);
        });
    });
    for (let i = 0; i < TEAM_DEFAULT_ROLES.length; i++) {
        if (!used.has(TEAM_DEFAULT_ROLES[i])) return i;
    }
    return checked.length % TEAM_DEFAULT_ROLES.length;
}

function onTeamMemberToggle(checkbox, mode) {
    const card = checkbox.closest('.team-student-card');
    if (!checkbox.checked) {
        clearStudentRoles(checkbox);
    } else if (card) {
        const anyRole = card.querySelector('input[type="checkbox"][name^="member_roles"]:checked');
        if (!anyRole) {
            const idx = nextDefaultRoleIndex(mode);
            assignDefaultRoleToCard(card, TEAM_DEFAULT_ROLES[idx]);
        }
    }
    updateTeamSelectedCount(mode);
}

// ── Randomize (Create Team) ─────────────────────
document.getElementById('createRandomizeBtn').addEventListener('click', function () {
    const checkboxes = Array.from(document.querySelectorAll('.create-student-checkbox'));
    const note = document.getElementById('createRandomizeNote');
    if (!checkboxes.length) { if (note) note.textContent = 'No students available.'; return; }

    checkboxes.forEach(c => {
        c.checked = false;
        clearStudentRoles(c);
    });

    const shuffled = [...checkboxes].sort(() => Math.random() - 0.5);
    const picked = shuffled.slice(0, 4);
    picked.forEach((c, i) => {
        c.checked = true;
        assignDefaultRoleToCard(c.closest('.team-student-card'), TEAM_DEFAULT_ROLES[i % TEAM_DEFAULT_ROLES.length]);
    });

    if (note) {
        note.textContent = `Selected ${picked.length} random member${picked.length === 1 ? '' : 's'} with default roles.`;
    }
    updateTeamSelectedCount('create');
});

function filterTeamStudentList(mode) {
    const input = document.getElementById(mode === 'insert' ? 'insertStudentSearch' : 'createStudentSearch');
    const cards = document.querySelectorAll(mode === 'insert' ? '.insert-student-card' : '.create-student-card');
    const q = (input?.value || '').trim().toLowerCase();
    cards.forEach(card => {
        const hay = card.getAttribute('data-search') || '';
        card.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

function updateTeamSelectedCount(mode) {
    const selector = mode === 'insert' ? '.insert-student-checkbox' : '.create-student-checkbox';
    const el = document.getElementById(mode === 'insert' ? 'insertSelectedCount' : 'createSelectedCount');
    const count = document.querySelectorAll(selector + ':checked').length;
    if (el) el.textContent = count + ' selected';
}

document.addEventListener('DOMContentLoaded', function () {
    updateTeamSelectedCount('create');
    updateTeamSelectedCount('insert');
});

// ── Open Update Team modal (members of this team only) ──────────
function openUpdateModal(groupName, memberData) {
    const form = document.getElementById('updateTeamForm');
    form.action = '/faculty/role/groups/' + encodeURIComponent(groupName);

    document.getElementById('updateGroupNameInput').value = groupName;
    document.getElementById('updateTeamEditingLabel').textContent = groupName;

    const searchInput = document.getElementById('updateStudentSearch');
    if (searchInput) searchInput.value = '';

    const rolesMap = {};
    (memberData || []).forEach(m => {
        const id = String(m.student_id);
        rolesMap[id] = m.roles || (m.role ? [m.role] : []);
    });

    document.querySelectorAll('.update-student-checkbox').forEach(cb => { cb.checked = false; });
    document.querySelectorAll('.update-role-checkbox').forEach(cb => { cb.checked = false; });

    const list = document.getElementById('updateMemberList');
    const rows = Array.from(document.querySelectorAll('.update-student-row'));
    let memberCount = 0;

    rows.forEach(row => {
        const sid = String(row.dataset.studentId || '');
        const isMember = Object.prototype.hasOwnProperty.call(rolesMap, sid);
        const cb = row.querySelector('.update-student-checkbox');
        const onBadge = row.querySelector('.update-on-team-badge');

        // Only this team's members appear in Update Team
        row.classList.toggle('hidden', !isMember);
        row.dataset.isTeamMember = isMember ? '1' : '0';

        if (!isMember) {
            if (cb) cb.checked = false;
            if (onBadge) onBadge.classList.add('hidden');
            return;
        }

        memberCount++;
        if (cb) cb.checked = true;
        if (onBadge) onBadge.classList.remove('hidden');

        (rolesMap[sid] || []).forEach(role => {
            const roleCb = row.querySelector('.update-role-checkbox[value="' + role + '"]');
            if (roleCb) roleCb.checked = true;
        });
    });

    // Members at the top of the list
    rows.filter(r => r.dataset.isTeamMember === '1').forEach(row => list.appendChild(row));

    const emptyNote = document.getElementById('updateNoMembersNote');
    if (emptyNote) emptyNote.classList.toggle('hidden', memberCount > 0);

    // Template grant picker
    const grantSelect = document.getElementById('grantStudentSelect');
    if (grantSelect) {
        grantSelect.innerHTML = '<option value="">Select member…</option>';
        (memberData || []).forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.student_id;
            opt.textContent = m.name || ('Student #' + m.student_id);
            grantSelect.appendChild(opt);
        });
    }
    const grantStatus = document.getElementById('grantStatus');
    if (grantStatus) grantStatus.textContent = '';

    document.getElementById('updateTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

async function facultyTemplateGrant(grant) {
    const groupName = document.getElementById('updateGroupNameInput')?.value;
    const studentId = document.getElementById('grantStudentSelect')?.value;
    const role = document.getElementById('grantRoleSelect')?.value;
    const status = document.getElementById('grantStatus');
    if (!groupName || !studentId || !role) {
        if (status) status.textContent = 'Pick a member and a role template first.';
        return;
    }
    try {
        const res = await fetch(@json(route('faculty.templates.grants.store')), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                group_name: groupName,
                student_id: parseInt(studentId, 10),
                role: role,
                grant: !!grant
            })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Request failed');
        if (status) status.textContent = (grant ? 'Granted' : 'Revoked') + ' edit access for ' + role.replace(/_/g, ' ') + '.';
    } catch (e) {
        if (status) status.textContent = e.message || 'Could not update permission.';
    }
}

document.getElementById('grantEditBtn')?.addEventListener('click', () => facultyTemplateGrant(true));
document.getElementById('revokeEditBtn')?.addEventListener('click', () => facultyTemplateGrant(false));

function closeUpdateModal() {
    document.getElementById('updateTeamModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function filterUpdateStudentList() {
    const q = (document.getElementById('updateStudentSearch')?.value || '').trim().toLowerCase();
    document.querySelectorAll('.update-student-row').forEach(row => {
        // Never reveal students who are not on this team
        if (row.dataset.isTeamMember !== '1') {
            row.classList.add('hidden');
            return;
        }
        const hay = row.getAttribute('data-search') || '';
        row.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

function onUpdateMemberToggle(checkbox) {
    const card = checkbox.closest('.update-student-row');
    if (!card) return;
    const onBadge = card.querySelector('.update-on-team-badge');
    if (onBadge) onBadge.classList.toggle('hidden', !checkbox.checked);

    if (!checkbox.checked) {
        card.querySelectorAll('.update-role-checkbox').forEach(cb => { cb.checked = false; });
    }
}

// ── Task Assignment Wizard ─────────────────────
let currentStep = 1;
let selectedDept = null;

const DEPT_META = {
    @foreach($rolesMeta ?? [] as $rKey => $rMeta)
        '{{ $rKey }}': { label: '{{ $rMeta["label"] }}', icon: '{{ $rMeta["icon"] }}', color: '{{ $rMeta["color"] }}' },
    @endforeach
};

function goToStep(step) {
    document.querySelectorAll('.task-step').forEach(s => s.classList.add('hidden'));
    document.getElementById('task-step-' + step).classList.remove('hidden');
    currentStep = step;
    updateStepIndicators();
    if (step === 4) updateReview();
}

function updateStepIndicators() {
    for (let i = 1; i <= 4; i++) {
        const ind = document.getElementById('step-ind-' + i);
        const line = document.getElementById('step-line-' + (i - 1));
        if (!ind) continue;

        if (i <= currentStep) {
            ind.classList.remove('bg-slate-100', 'text-slate-400');
            ind.classList.add('bg-brand', 'text-white');
            ind.querySelector('span:first-child').classList.remove('bg-slate-200');
            ind.querySelector('span:first-child').classList.add('bg-white/20');
        } else {
            ind.classList.add('bg-slate-100', 'text-slate-400');
            ind.classList.remove('bg-brand', 'text-white');
            ind.querySelector('span:first-child').classList.add('bg-slate-200');
            ind.querySelector('span:first-child').classList.remove('bg-white/20');
        }
        if (line) {
            line.classList.toggle('bg-brand', i <= currentStep);
            line.classList.toggle('bg-slate-200', i > currentStep);
        }
    }
}

function selectDepartment(deptId) {
    selectedDept = deptId;

    // Highlight selected button
    document.querySelectorAll('.task-dept-btn').forEach(btn => {
        btn.classList.remove('border-brand', 'bg-brand-soft', 'shadow-md');
        btn.classList.add('border-slate-200', 'bg-white');
    });
    const btn = document.querySelector('.task-dept-btn[data-dept="' + deptId + '"]');
    if (btn) {
        btn.classList.add('border-brand', 'bg-brand-soft', 'shadow-md');
        btn.classList.remove('border-slate-200', 'bg-white');
    }

    // Show task panel for this dept
    document.querySelectorAll('.task-checklist-panel').forEach(p => p.classList.add('hidden'));
    const panel = document.getElementById('taskPanel-' + deptId);
    if (panel) panel.classList.remove('hidden');

    // Update step 2 title
    const meta = DEPT_META[deptId];
    document.getElementById('selectedDeptTitle').textContent = meta ? meta.label + ' Tasks' : 'Select Tasks';

    // Go to step 2
    goToStep(2);
    updateContinueBtn();
}

function selectAllVisibleTasks() {
    if (!selectedDept) return;
    const checkboxes = document.querySelectorAll('.task-checkbox-' + selectedDept);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    updateContinueBtn();
}

function updateContinueBtn() {
    if (!selectedDept) return;
    const checked = document.querySelectorAll('.task-checkbox-' + selectedDept + ':checked').length;
    const btn = document.getElementById('btn-to-step3');
    if (btn) {
        btn.disabled = checked === 0;
    }
}

function updateReview() {
    // Dept
    const meta = DEPT_META[selectedDept];
    if (meta) {
        document.getElementById('reviewDeptName').textContent = meta.label;
        const iconEl = document.getElementById('reviewDeptIcon');
        iconEl.innerHTML = '<span class="iconify ' + meta.color + '" data-icon="' + meta.icon + '"></span>';
    }

    // Task count
    const checked = selectedDept ? document.querySelectorAll('.task-checkbox-' + selectedDept + ':checked').length : 0;
    document.getElementById('reviewTaskCount').textContent = checked + ' task' + (checked !== 1 ? 's' : '');

    // Due date
    const dueDate = document.querySelector('input[name="due_date"]').value;
    document.getElementById('reviewDueDate').textContent = dueDate
        ? new Date(dueDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        : 'No deadline';
}

function resetTaskWizard() {
    selectedDept = null;
    document.querySelectorAll('.task-check').forEach(cb => cb.checked = false);
    document.querySelector('input[name="due_date"]').value = '';
    document.querySelectorAll('.task-dept-btn').forEach(btn => {
        btn.classList.remove('border-brand', 'bg-brand-soft', 'shadow-md');
        btn.classList.add('border-slate-200', 'bg-white');
    });
    document.querySelectorAll('.task-checklist-panel').forEach(p => p.classList.add('hidden'));
    goToStep(1);
}

// Track checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('task-check')) {
        updateContinueBtn();
    }
});

// Initialize on page load
(function() {
    const activeTab = '{{ $activeTab }}';
    if (activeTab === 'create_task') {
        goToStep(1);
    }
    // Auto-open create team modal if there are validation errors from that form
    @if($errors->has('group_name') || $errors->has('members') || $errors->has('member_roles'))
        openCreateTeamModal();
    @endif
    // Open from Activity Logs "Add Team" (or ?create=1)
    @if(request()->boolean('create'))
        openCreateTeamModal();
        const url = new URL(window.location);
        url.searchParams.delete('create');
        history.replaceState(null, '', url);
    @endif
})();

// ── Team Info Modal ────────────────────────────
let teamModalActivityLogs = [];
let teamModalActivityPage = 1;
const TEAM_MODAL_ACTIVITY_PER_PAGE = 5;

function escHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function renderTeamModalActivityPage() {
    const activityBody = document.getElementById('teamModalActivityBody');
    const pager = document.getElementById('teamModalActivityPager');
    const meta = document.getElementById('teamModalActivityMeta');
    const pageLabel = document.getElementById('teamModalActivityPageLabel');
    const prevBtn = document.getElementById('teamModalActivityPrev');
    const nextBtn = document.getElementById('teamModalActivityNext');
    const logs = teamModalActivityLogs;
    const total = logs.length;
    const totalPages = Math.max(1, Math.ceil(total / TEAM_MODAL_ACTIVITY_PER_PAGE));

    if (teamModalActivityPage > totalPages) teamModalActivityPage = totalPages;
    if (teamModalActivityPage < 1) teamModalActivityPage = 1;

    if (total === 0) {
        activityBody.innerHTML = '<tr><td colspan="4" class="px-3 py-6 text-center text-xs text-slate-400">No activity logs for this team yet.</td></tr>';
        if (pager) pager.classList.add('hidden');
        if (meta) meta.textContent = '';
        return;
    }

    const start = (teamModalActivityPage - 1) * TEAM_MODAL_ACTIVITY_PER_PAGE;
    const pageLogs = logs.slice(start, start + TEAM_MODAL_ACTIVITY_PER_PAGE);
    const end = start + pageLogs.length;

    activityBody.innerHTML = pageLogs.map(function(log) {
        const isDone = log.status === 'archived';
        const statusBadge = isDone
            ? '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed</span>'
            : '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Assigned</span>';

        return '<tr class="hover:bg-slate-50 transition-colors">' +
            '<td class="px-3 py-2">' +
                '<p class="text-xs font-semibold text-slate-800 truncate" title="' + escHtml(log.title) + '">' + escHtml(log.title || '—') + '</p>' +
                (log.description ? '<p class="text-[10px] text-slate-400 truncate">' + escHtml(log.description) + '</p>' : '') +
            '</td>' +
            '<td class="px-3 py-2 text-[11px] font-semibold text-slate-600 whitespace-nowrap">' + escHtml(log.role_label || log.role || '—') + '</td>' +
            '<td class="px-3 py-2">' + statusBadge + '</td>' +
            '<td class="px-3 py-2 text-[11px] text-slate-500 whitespace-nowrap">' + escHtml(log.updated_at || '—') + '</td>' +
        '</tr>';
    }).join('');

    if (meta) meta.textContent = 'Showing ' + (start + 1) + '–' + end + ' of ' + total;
    if (pageLabel) pageLabel.textContent = 'Page ' + teamModalActivityPage + ' of ' + totalPages;

    if (pager) {
        if (total > TEAM_MODAL_ACTIVITY_PER_PAGE) {
            pager.classList.remove('hidden');
            pager.classList.add('flex');
        } else {
            pager.classList.add('hidden');
            pager.classList.remove('flex');
        }
    }
    if (prevBtn) prevBtn.disabled = teamModalActivityPage <= 1;
    if (nextBtn) nextBtn.disabled = teamModalActivityPage >= totalPages;
}

function openTeamModal(groupName, members, createdAt, activityLogs) {
    const logs = Array.isArray(activityLogs) ? activityLogs : [];
    const nameSuffix = document.getElementById('modalTeamNameSuffix');
    if (nameSuffix) {
        nameSuffix.textContent = groupName ? ' — ' + groupName : '';
    }

    const tbody = document.getElementById('teamModalMembersBody');
    if (members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-3 py-6 text-center text-xs text-slate-400">No members found.</td></tr>';
    } else {
        tbody.innerHTML = members.map(function(m, i) {
            const roleLabels = m.role_labels || [m.role_label || m.role];
            const roleBadges = roleLabels.map(function(rl) {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">' + rl + '</span>';
            }).join(' ');
            return '<tr class="hover:bg-slate-50 transition-colors">' +
                '<td class="px-3 py-2 text-[11px] text-slate-400 font-medium">' + (i + 1) + '</td>' +
                '<td class="px-3 py-2">' +
                    '<div class="flex items-center gap-2 min-w-0">' +
                        '<div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-[10px] font-bold flex-shrink-0">' +
                            m.name.charAt(0).toUpperCase() +
                        '</div>' +
                        '<span class="text-xs font-semibold text-slate-700 truncate" title="' + escHtml(m.name) + '">' + escHtml(m.name) + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="px-3 py-2">' +
                    '<div class="flex flex-wrap gap-1">' + roleBadges + '</div>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    teamModalActivityLogs = logs;
    teamModalActivityPage = 1;
    renderTeamModalActivityPage();

    document.getElementById('teamInfoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTeamModal() {
    document.getElementById('teamInfoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.addEventListener('DOMContentLoaded', function () {
    const prevBtn = document.getElementById('teamModalActivityPrev');
    const nextBtn = document.getElementById('teamModalActivityNext');
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (teamModalActivityPage > 1) {
                teamModalActivityPage -= 1;
                renderTeamModalActivityPage();
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            const totalPages = Math.max(1, Math.ceil(teamModalActivityLogs.length / TEAM_MODAL_ACTIVITY_PER_PAGE));
            if (teamModalActivityPage < totalPages) {
                teamModalActivityPage += 1;
                renderTeamModalActivityPage();
            }
        });
    }
});
</script>
@endsection