@extends('faculty.layout.app')

@section('page_title', 'Teams')
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
</style>

{{-- ═══════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════ --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Teams</h2>
        <p class="text-sm text-slate-400 mt-1">Manage student teams, assign roles, and create tasks — all in one place.</p>
    </div>
    <button onclick="openCreateTeamModal()"
        class="inline-flex items-center gap-2 bg-brand text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:scale-105 transition shadow-md shadow-brand/20">
        <span class="iconify" data-icon="mdi:plus"></span> Add Team
    </button>
</div>

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

<div class="flex gap-2 mb-6 overflow-x-auto pb-1" id="mainTabBar">
    <button onclick="switchTab('teams')" id="tab-btn-teams"
        class="main-tab-btn {{ $activeTab === 'teams' ? 'active' : '' }}">
        <span class="iconify text-base" data-icon="mdi:account-group"></span>
        Teams
        <span class="tab-badge">{{ count($groups ?? []) }}</span>
    </button>
    <button onclick="switchTab('create_task')" id="tab-btn-create_task"
        class="main-tab-btn {{ $activeTab === 'create_task' ? 'active' : '' }}">
        <span class="iconify text-base" data-icon="mdi:clipboard-plus-outline"></span>
        Assign Task
    </button>
</div>


{{-- ═══════════════════════════════════════════════
     TAB 1 — TEAMS (View all groups)
═══════════════════════════════════════════════ --}}
<div id="panel-teams" class="tab-panel {{ $activeTab === 'teams' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Header -->
        <div class="p-5 border-b border-slate-200 bg-rose-50/40">
            <p class="text-sm text-slate-500">Click <strong>View</strong> or <strong>Update</strong> on any team to manage its members and roles.</p>
        </div>

        @php
            $roleLabels = [
                'front_desk'            => 'Front Desk',
                'restaurant_management' => 'Restaurant Mgmt',
                'room_management'       => 'Room Mgmt',
                'maintenance'           => 'Maintenance',
                'housekeeping'          => 'Housekeeping',
            ];
        @endphp

        <div class="overflow-x-auto">
            <table id="teamsTable" class="w-full text-sm text-slate-700">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Team Name</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Members</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Members & Roles</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
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
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-800 text-sm">{{ $groupName }}</span>
                                    @if($createdAt)
                                        <span class="text-xs text-slate-400 mt-0.5">{{ $createdAt }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-full text-xs font-bold">
                                    <span class="iconify" data-icon="mdi:account-multiple-outline"></span>
                                    {{ $groupMembers->count() }} {{ Str::plural('member', $groupMembers->count()) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($groupMembers as $member)
                                        @php
                                            $u  = $member->student?->user;
                                            $dn = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));
                                            $dn = $dn !== '' ? $dn : ($u?->name ?? 'Student');
                                            $memberRoles = $member->roles->pluck('role')->toArray();
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">
                                            {{ $dn }}
                                            @foreach($memberRoles as $mRole)
                                                <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $mRole }}"></span>
                                                <span class="text-slate-400">({{ $roleLabels[$mRole] ?? $mRole }})</span>
                                            @endforeach
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <button onclick='openTeamModal({{ json_encode($groupName) }}, {{ $memberJson }}, {{ json_encode($createdAt) }})'
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                                    </button>
                                    <button
                                        onclick='openUpdateModal(@json($groupName), {{ $memberJson }})'
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-soft text-brand border border-brand/10 rounded-lg text-xs font-bold hover:bg-brand/10 transition">
                                        <span class="iconify text-sm" data-icon="mdi:pencil-outline"></span> Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-group-outline"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No teams created yet.</p>
                                <button onclick="openCreateTeamModal()"
                                    class="mt-3 px-4 py-2 bg-brand text-white text-xs font-bold rounded-xl hover:scale-105 transition shadow-md shadow-brand/20">
                                    Add First Team
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
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg max-h-[85vh] flex flex-col">
        <!-- Modal Header -->
        <div class="bg-rose-50 px-6 py-4 border-b border-rose-100 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h4 class="font-bold text-rose-700 text-base flex items-center gap-2">
                <span class="iconify text-lg" data-icon="mdi:account-group-outline"></span>
                Team Details
            </h4>
            <button onclick="closeTeamModal()" class="text-slate-400 hover:text-rose-500 hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 p-6 space-y-5">
            <!-- Info Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Team</p>
                    <p id="modalTeamName" class="text-sm font-bold text-slate-800">—</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Members</p>
                    <p id="modalMemberCount" class="text-sm font-bold text-slate-800">—</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Created</p>
                    <p id="modalCreatedAt" class="text-sm font-bold text-slate-800">—</p>
                </div>
            </div>

            <!-- Members Table -->
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Team Members & Roles</p>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Member Name</th>
                                <th class="text-left px-4 py-2.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody id="teamModalMembersBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3 border-t border-slate-100 flex justify-end rounded-b-2xl flex-shrink-0 bg-slate-50/50">
            <button onclick="closeTeamModal()" class="px-4 py-2 rounded-xl bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition font-semibold text-sm">
                Close
            </button>
        </div>
    </div>
</div>


<!-- Add Team Modal -->
<div id="createTeamModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCreateTeamModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
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

        <!-- Tab Panel: Add Team -->
        <div id="modal-panel-add_team" class="flex-1 min-h-0 overflow-y-auto">
            <form method="POST" id="createTeamForm" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="create_team">

                @if($errors->any())
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left column --}}
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Team Name <span class="text-red-400">*</span></label>
                            <input name="group_name" type="text" placeholder="e.g. Front Desk Team A"
                                value="{{ old('group_name') }}"
                                class="w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Quick Selection</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Pick members manually or randomize 4 students.</p>
                                </div>
                                <button type="button" id="createRandomizeBtn"
                                    class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:border-brand/40 hover:text-brand transition flex items-center gap-1.5">
                                    <span class="iconify" data-icon="mdi:shuffle-variant"></span> Randomize 4
                                </button>
                            </div>
                            <p id="createRandomizeNote" class="mt-2 text-[11px] text-slate-400"></p>
                        </div>

                        {{-- Role legend --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Role Color Legend</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Restaurant Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-2.5 h-2.5 rounded-full role-dot-{{ $rk }} flex-shrink-0"></span>{{ $rl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right column — member list --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Members & Roles <span class="text-red-400">*</span></label>
                        <div class="max-h-96 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200 bg-slate-100/50">
                                Select members and assign roles
                            </div>
                            <div class="p-3 space-y-2">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                        $selectedMembers = array_map('intval', old('members', []));
                                        $selectedRole = old('member_roles.' . $sk, 'front_desk');
                                    @endphp
                                    <div class="student-row flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5 border border-slate-200">
                                        <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="create-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-700 truncate">{{ $dn }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">#{{ $student->student_id }}</div>
                                            </div>
                                        </label>
                                        <div class="flex flex-wrap gap-1 shrink-0">
                                            @php
                                                $selectedRoles = old('member_roles.' . $sk, []);
                                                if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                            @endphp
                                            @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Rest. Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                                <label class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}>
                                                    {{ $rl }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-8 text-slate-400">
                                        <span class="iconify text-2xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                        <p class="text-sm">No unassigned students available.</p>
                                        <p class="text-xs text-slate-300">All students are already in a team.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab Panel: Insert (assign ungrouped students to existing team) -->
        <div id="modal-panel-insert" class="flex-1 min-h-0 overflow-y-auto hidden">
            <form method="POST" id="insertStudentForm" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="insert_student">

                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left column --}}
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Existing Team <span class="text-red-400">*</span></label>
                            <select name="group_name"
                                class="w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                                <option value="">Select a team...</option>
                                @foreach($groups ?? [] as $groupName => $members)
                                    <option value="{{ $groupName }}" {{ old('group_name') === $groupName ? 'selected' : '' }}>{{ $groupName }} ({{ $members->count() }} {{ Str::plural('member', $members->count()) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <span class="iconify text-blue-500 text-xl" data-icon="mdi:information-outline"></span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">How it works</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Select an existing team from the dropdown, then choose ungrouped students to insert into that team with assigned roles.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Role legend --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Role Color Legend</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Restaurant Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <span class="w-2.5 h-2.5 rounded-full role-dot-{{ $rk }} flex-shrink-0"></span>{{ $rl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right column — ungrouped student list --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Ungrouped Students <span class="text-red-400">*</span></label>
                        <div class="max-h-96 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="px-4 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200 bg-slate-100/50">
                                Select students and assign roles
                            </div>
                            <div class="p-3 space-y-2">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                        $selectedMembers = array_map('intval', old('members', []));
                                    @endphp
                                    <div class="student-row flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5 border border-slate-200">
                                        <label class="flex items-center gap-3 cursor-pointer flex-1 min-w-0">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="insert-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-700 truncate">{{ $dn }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">#{{ $student->student_id }}</div>
                                            </div>
                                        </label>
                                        <div class="flex flex-wrap gap-1 shrink-0">
                                            @php
                                                $selectedRoles = old('member_roles.' . $sk, []);
                                                if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                            @endphp
                                            @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Rest. Mgmt', 'room_management' => 'Room Mgmt', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $rk => $rl)
                                                <label class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}>
                                                    {{ $rl }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-8 text-slate-400">
                                        <span class="iconify text-2xl text-slate-200" data-icon="mdi:account-check-outline"></span>
                                        <p class="text-sm">No ungrouped students available.</p>
                                        <p class="text-xs text-slate-300">All students are already assigned to a team.</p>
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

                    {{-- Members & Roles --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Members & Roles <span class="text-red-400">*</span></label>
                        <p class="text-xs text-slate-400 mb-2">Check members to include and assign one or more roles to each.</p>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="p-3 space-y-2" id="updateMemberList">
                                @foreach($allStudents ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->id;
                                    @endphp
                                    <div class="student-row update-student-row rounded-xl bg-white px-3 py-3 border border-slate-200"
                                         data-student-id="{{ $sk }}">
                                        {{-- Student info row --}}
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="update-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-slate-700 truncate">{{ $dn }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">#{{ $student->student_id }}</div>
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
                                @endforeach
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
        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 bg-brand-soft flex items-center gap-3">
            <div class="w-10 h-10 bg-brand/10 rounded-xl flex items-center justify-center">
                <span class="iconify text-brand text-xl" data-icon="mdi:clipboard-plus-outline"></span>
            </div>
            <div>
                <h3 class="font-bold text-brand text-base">Assign New Task</h3>
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
                            <span class="iconify" data-icon="mdi:check-circle-outline"></span> Assign Tasks
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



// ── Randomize (Create Team) ─────────────────────
document.getElementById('createRandomizeBtn').addEventListener('click', function () {
    const checkboxes = Array.from(document.querySelectorAll('.create-student-checkbox'));
    const note = document.getElementById('createRandomizeNote');
    if (!checkboxes.length) { if (note) note.textContent = 'No students available.'; return; }
    checkboxes.forEach(c => c.checked = false);
    const shuffled = [...checkboxes].sort(() => Math.random() - 0.5);
    const picked = shuffled.slice(0, 4);
    picked.forEach(c => c.checked = true);
    if (note) note.textContent = `Selected ${picked.length} random member${picked.length === 1 ? '' : 's'}.`;
});

// ── Open Update Team modal pre-filled ──────────
function openUpdateModal(groupName, memberData) {
    // Set form action
    const form = document.getElementById('updateTeamForm');
    form.action = '/faculty/role/groups/' + encodeURIComponent(groupName);

    // Set group name input
    document.getElementById('updateGroupNameInput').value = groupName;
    document.getElementById('updateTeamEditingLabel').textContent = groupName;

    // Build a map: student_id -> roles[]
    const rolesMap = {};
    memberData.forEach(m => {
        rolesMap[m.student_id] = m.roles || (m.role ? [m.role] : []);
    });

    // Uncheck all checkboxes first
    document.querySelectorAll('.update-student-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.querySelectorAll('.update-role-checkbox').forEach(cb => {
        cb.checked = false;
    });

    // Check + set roles for members in this group
    document.querySelectorAll('.update-student-row').forEach(row => {
        const sid = parseInt(row.dataset.studentId);
        if (rolesMap.hasOwnProperty(sid)) {
            const cb  = row.querySelector('.update-student-checkbox');
            if (cb)  cb.checked = true;
            // Check role checkboxes
            rolesMap[sid].forEach(role => {
                const roleCb = row.querySelector('.update-role-checkbox[value="' + role + '"]');
                if (roleCb) roleCb.checked = true;
            });
        }
    });

    // Show modal
    document.getElementById('updateTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeUpdateModal() {
    document.getElementById('updateTeamModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
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
})();

// ── Team Info Modal ────────────────────────────
function openTeamModal(groupName, members, createdAt) {
    document.getElementById('modalTeamName').textContent = groupName;
    document.getElementById('modalMemberCount').textContent = members.length + ' member' + (members.length !== 1 ? 's' : '');
    document.getElementById('modalCreatedAt').textContent = createdAt || '—';

    const tbody = document.getElementById('teamModalMembersBody');
    if (members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">No members found.</td></tr>';
    } else {
        tbody.innerHTML = members.map(function(m, i) {
            const roleLabels = m.role_labels || [m.role_label || m.role];
            const roleBadges = roleLabels.map(function(rl) {
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">' + rl + '</span>';
            }).join(' ');
            return '<tr class="hover:bg-slate-50 transition-colors">' +
                '<td class="px-4 py-3 text-xs text-slate-400 font-medium">' + (i + 1) + '</td>' +
                '<td class="px-4 py-3">' +
                    '<div class="flex items-center gap-2">' +
                        '<div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-[10px] font-bold flex-shrink-0">' +
                            m.name.charAt(0).toUpperCase() +
                        '</div>' +
                        '<span class="text-sm font-semibold text-slate-700">' + m.name + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="px-4 py-3">' +
                    '<div class="flex flex-wrap gap-1">' + roleBadges + '</div>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    document.getElementById('teamInfoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTeamModal() {
    document.getElementById('teamInfoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}
</script>
@endsection