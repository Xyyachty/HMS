@extends('dean.layouts.app')

@section('page_title', 'List of Teams Information')
@section('faculties_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-rose-50/40">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Teams</h3>
            <p class="text-sm text-slate-500 mt-1">All groups created by faculty members.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mx-6 mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Tab Bar -->
    <div class="px-6 pt-4 flex gap-2 border-b border-slate-100">
        <button onclick="switchFacultyTab('teams')" id="ftab-teams"
            class="ftab-btn px-5 py-2.5 text-sm font-bold rounded-t-xl border-b-2 border-brand text-brand bg-brand-soft transition flex items-center gap-1.5">
            <span class="iconify" data-icon="mdi:account-group-outline"></span> Teams
        </button>
        <button onclick="switchFacultyTab('complied')" id="ftab-complied"
            class="ftab-btn px-5 py-2.5 text-sm font-bold rounded-t-xl border-b-2 border-transparent text-slate-500 hover:text-brand transition flex items-center gap-1.5">
            <span class="iconify" data-icon="mdi:clipboard-check-outline"></span> Complied
        </button>
    </div>

    <!-- Teams Panel -->
    <div id="fpanel-teams">
        <div class="overflow-x-auto">
            <table id="teamsTable" class="w-full text-sm text-slate-700">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Faculty</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Team Name</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Members</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($faculties as $faculty)
                        @php
                            $displayName = trim(implode(' ', array_filter([
                                $faculty->user->first_name ?? null,
                                $faculty->user->middle_name ?? null,
                                $faculty->user->last_name ?? null,
                            ])));
                            $displayName = $displayName !== '' ? $displayName : ($faculty->user->name ?? 'Faculty');
                            $roleLabels = [
                                'front_desk' => 'Front Desk',
                                'restaurant_management' => 'Restaurant',
                                'room_management' => 'Rooms',
                                'maintenance' => 'Maintenance',
                                'housekeeping' => 'Housekeeping',
                            ];
                            $facultyGroups = $faculty->studentGroups
                                ? $faculty->studentGroups->groupBy('group_name')
                                : collect();
                        @endphp
                        @forelse ($facultyGroups as $groupName => $groupMembers)
                            @php
                                $createdAt = optional($groupMembers->first()->created_at)->format('M d, Y');
                                $memberCount = $groupMembers->count();
                                $membersJson = $groupMembers->map(function ($m) use ($roleLabels) {
                                    $u = $m->student?->user;
                                    $n = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));
                                    $n = $n !== '' ? $n : ($u?->name ?? 'Student');
                                    $memberRoles = $m->roles->pluck('role')->filter()->values()->all();
                                    if (empty($memberRoles) && !empty($m->role)) {
                                        $memberRoles = [$m->role];
                                    }
                                    return [
                                        'name' => $n,
                                        'role_labels' => array_map(fn ($r) => $roleLabels[$r] ?? $r, $memberRoles),
                                    ];
                                })->values()->toJson();
                                $activityLogs = $teamActivityByFacultyGroup[$faculty->id][$groupName] ?? [];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold text-slate-800 text-sm">{{ $displayName }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-slate-700">{{ $groupName }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-full text-xs font-bold">
                                        <span class="iconify" data-icon="mdi:account-multiple-outline"></span>
                                        {{ $memberCount }} {{ Str::plural('member', $memberCount) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 text-sm">{{ $createdAt }}</td>
                                <td class="px-5 py-3.5">
                                    <button onclick='openTeamModal({{ json_encode($groupName) }}, {{ $membersJson }}, {{ json_encode($createdAt) }}, {{ json_encode($activityLogs) }})'
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-xs font-bold hover:bg-rose-100 transition">
                                        <span class="iconify" data-icon="mdi:eye-outline"></span> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    @endforeach
                </tbody>
            </table>

            @php
                $totalRows = 0;
                foreach ($faculties as $f) {
                    if ($f->studentGroups) $totalRows += $f->studentGroups->groupBy('group_name')->count();
                }
            @endphp
            @if($totalRows === 0)
                <div class="py-16 text-center">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <span class="iconify text-slate-300 text-3xl" data-icon="mdi:account-group-outline"></span>
                    </div>
                    <p class="text-sm font-semibold text-slate-400">No teams found.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Complied Panel -->
    <div id="fpanel-complied" class="hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-4">Task</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Faculty</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($completedTasks as $task)
                        @php
                            $roleLabels = [
                                'front_desk' => 'Front Desk',
                                'restaurant_management' => 'Restaurant Mgmt',
                                'room_management' => 'Room Mgmt',
                                'maintenance' => 'Maintenance',
                                'housekeeping' => 'Housekeeping',
                            ];
                            $priorityClass = [
                                'high'   => 'bg-red-50 text-red-700 border-red-100',
                                'medium' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'low'    => 'bg-green-50 text-green-700 border-green-100',
                            ][$task->priority] ?? 'bg-slate-50 text-slate-700 border-slate-100';
                            $facultyName = trim(implode(' ', array_filter([
                                $task->faculty?->user?->first_name,
                                $task->faculty?->user?->last_name,
                            ])));
                            $facultyName = $facultyName ?: ($task->faculty?->user?->name ?? '—');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-700 line-through">{{ $task->title }}</p>
                                @if($task->description)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $task->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">
                                    {{ $roleLabels[$task->role] ?? $task->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ $facultyName }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border {{ $priorityClass }}">
                                    {{ $task->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                @if($task->due_date)
                                    <span class="flex items-center gap-1">
                                        <span class="iconify" data-icon="mdi:calendar-outline"></span>
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                    <span class="iconify" data-icon="mdi:check-circle-outline"></span> Complied
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <span class="iconify text-slate-300 text-2xl" data-icon="mdi:clipboard-check-outline"></span>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">No complied tasks yet.</p>
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

<!-- Create Faculty Modal -->
<div id="createFacultyModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('createFacultyModal')"></div>
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100" style="width: 760px; max-width: 92vw; max-height: 90vh; overflow-y: auto;">
        <div class="bg-rose-50 px-6 py-4 border-b border-rose-100 flex justify-between items-center sticky top-0 z-10">
            <h4 class="font-bold text-rose-600 text-lg">Add Faculty Account</h4>
            <button onclick="closeModal('createFacultyModal')" class="text-slate-400 hover:text-rose-500 hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('dean.faculties.store') }}">
            @csrf
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">First Name</label>
                    <input name="first_name" type="text" value="{{ old('first_name') }}" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Middle Name</label>
                    <input name="middle_name" type="text" value="{{ old('middle_name') }}" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Last Name</label>
                    <input name="last_name" type="text" value="{{ old('last_name') }}" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Phone Number</label>
                    <input name="phone_number" type="text" value="{{ old('phone_number') }}" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    <select name="status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition appearance-none">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                    <div class="relative">
                        <input name="password" type="password" required class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input name="password_confirmation" type="password" required class="w-full h-10 px-3 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="iconify text-lg" data-icon="mdi:eye-off-outline"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0">
                <button type="button" onclick="closeModal('createFacultyModal')" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-rose-500 text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-rose-500/20">Save Faculty</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    #teamsTable {
        border-collapse: collapse;
        width: 100%;
    }
    #teamsTable thead tr {
        background: #F8FAFC;
    }
    #teamsTable tbody tr {
        border-bottom: 1px solid #F1F5F9;
        transition: background 0.15s;
    }
    #teamsTable tbody tr:last-child {
        border-bottom: none;
    }
    #teamsTable tbody tr:hover {
        background: #F8FAFC;
    }
</style>
@endpush

@push('scripts')
<script>
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

        activityBody.innerHTML = pageLogs.map(function (log) {
            const isDone = log.status === 'archived';
            const statusBadge = isDone
                ? '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Completed</span>'
                : '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Assigned</span>';

            return '<tr class="hover:bg-slate-50 transition-colors">' +
                '<td class="px-3 py-2">' +
                    '<p class="text-xs font-semibold text-slate-800 truncate" title="' + escHtml(log.title) + '">' + escHtml(log.title) + '</p>' +
                    (log.description
                        ? '<p class="text-[10px] text-slate-400 truncate" title="' + escHtml(log.description) + '">' + escHtml(log.description) + '</p>'
                        : '') +
                '</td>' +
                '<td class="px-3 py-2 text-[11px] font-semibold text-slate-600 whitespace-nowrap">' + escHtml(log.role_label || log.role || '—') + '</td>' +
                '<td class="px-3 py-2">' + statusBadge + '</td>' +
                '<td class="px-3 py-2 text-[11px] text-slate-500 whitespace-nowrap">' + escHtml(log.updated_at || '—') + '</td>' +
            '</tr>';
        }).join('');

        if (meta) meta.textContent = 'Showing ' + (start + 1) + '-' + end + ' of ' + total;
        if (pageLabel) pageLabel.textContent = 'Page ' + teamModalActivityPage + ' of ' + totalPages;
        if (pager) pager.classList.toggle('hidden', total <= TEAM_MODAL_ACTIVITY_PER_PAGE);
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
        if (!members || members.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-3 py-6 text-center text-xs text-slate-400">No members found.</td></tr>';
        } else {
            tbody.innerHTML = members.map(function (m, i) {
                const roleLabels = m.role_labels || [m.role_label || m.role || '—'];
                const roleBadges = roleLabels.map(function (rl) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">' + escHtml(rl) + '</span>';
                }).join(' ');
                return '<tr class="hover:bg-slate-50 transition-colors">' +
                    '<td class="px-3 py-2 text-[11px] text-slate-400 font-medium">' + (i + 1) + '</td>' +
                    '<td class="px-3 py-2">' +
                        '<div class="flex items-center gap-2 min-w-0">' +
                            '<div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 text-[10px] font-bold flex-shrink-0">' +
                                String(m.name || '?').charAt(0).toUpperCase() +
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

    function switchFacultyTab(tab) {
        document.querySelectorAll('.ftab-btn').forEach(btn => {
            btn.classList.remove('border-brand', 'text-brand', 'bg-brand-soft');
            btn.classList.add('border-transparent', 'text-slate-500');
        });
        const active = document.getElementById('ftab-' + tab);
        active.classList.remove('border-transparent', 'text-slate-500');
        active.classList.add('border-brand', 'text-brand', 'bg-brand-soft');
        document.getElementById('fpanel-teams').classList.toggle('hidden', tab !== 'teams');
        document.getElementById('fpanel-complied').classList.toggle('hidden', tab !== 'complied');
    }

    function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.body.style.overflow = 'auto'; }

    function togglePassword(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('.iconify');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-icon', 'mdi:eye-outline');
        } else {
            input.type = 'password';
            icon.setAttribute('data-icon', 'mdi:eye-off-outline');
        }
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
@endpush
@endsection
