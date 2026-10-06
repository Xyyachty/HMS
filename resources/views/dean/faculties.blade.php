@extends('dean.layouts.app')

@section('page_title', 'Teams Overview')
@section('page_subtitle', 'View only: the teams of every faculty and the hotel concept each team is building.')
@section('faculties_active', 'active')

@section('content')
<div class="ink-all bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-rose-50/40">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Teams by Faculty</h3>
            <p class="text-sm text-slate-500 mt-1">Each faculty is listed once, with the teams they created beneath.</p>
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
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Team Name</th>
                        <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Hotel Concept</th>
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
                        {{-- One header row per faculty, so the name is not repeated on
                             every team row beneath it. A faculty with no teams is skipped. --}}
                        @if ($facultyGroups->isNotEmpty())
                            <tr class="faculty-group-row">
                                <td colspan="5" class="px-5 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        @include('partials.user-avatar', [
                                            'user'         => $faculty->user,
                                            'name'         => $displayName,
                                            'size'         => 'w-8 h-8',
                                            'rounded'      => 'rounded-full',
                                            'extraClasses' => 'bg-brand text-white text-xs font-bold',
                                        ])
                                        <span class="font-bold text-slate-800 text-sm">{{ $displayName }}</span>
                                        <span class="text-[11px] font-semibold text-slate-400">&middot; {{ $facultyGroups->count() }} {{ Str::plural('team', $facultyGroups->count()) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
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
                                        'user_id' => $u?->user_id,
                                        'role_labels' => array_map(fn ($r) => $roleLabels[$r] ?? $r, $memberRoles),
                                    ];
                                })->values()->toJson();
                                $activityLogs = $teamActivityByFacultyGroup[$faculty->user_information_id][$groupName] ?? [];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5 pl-10">
                                    <span class="font-semibold text-slate-700">{{ $groupName }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    {{-- A team proposes two, so both are named by slot. --}}
                                    @php $teamConcepts = $conceptsByFacultyGroup[$faculty->user_information_id][$groupName] ?? []; @endphp
                                    @forelse($teamConcepts as $concept)
                                        <div class="{{ !$loop->first ? 'mt-1.5 pt-1.5 border-t border-slate-100' : '' }}">
                                            <p class="font-semibold text-slate-700 text-sm">
                                                {{ \App\Support\HotelConceptDesk::slotLabel($concept->slot) }}: {{ $concept->title }}
                                            </p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ $concept->hotel_type_label }} · {{ \App\Support\HotelConceptDesk::statusLabel($concept) }}
                                            </p>
                                        </div>
                                    @empty
                                        <span class="text-xs text-slate-400">Not proposed yet</span>
                                    @endforelse
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold">
                                        <span class="iconify" data-icon="mdi:account-multiple-outline"></span>
                                        {{ $memberCount }} {{ Str::plural('member', $memberCount) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-500 text-sm">{{ $createdAt }}</td>
                                <td class="px-5 py-3.5">
                                    <button onclick='openTeamModal({{ json_encode($groupName) }}, {{ $membersJson }}, {{ json_encode($createdAt) }}, {{ json_encode($activityLogs) }}, {{ (int) $faculty->user_information_id }})'
                                        class="view-btn inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border rounded-lg text-xs font-bold transition" style="border-color: #111;">
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
                        <th class="px-6 py-4">Student Name</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Faculty</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Date Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($completedTasks as $task)
                        @php
                            $roleLabels = [
                                'front_desk' => 'Front Desk',
                                'restaurant_management' => 'Restaurant',
                                'room_management' => 'Room',
                                'maintenance' => 'Maintenance',
                                'housekeeping' => 'Housekeeping',
                            ];
                            $facultyName = trim(implode(' ', array_filter([
                                $task->faculty?->user?->last_name,
                                $task->faculty?->user?->first_name,
                            ])));
                            $facultyName = $facultyName !== ''
                                ? $facultyName
                                : ($task->faculty?->user?->name ?? '—');

                            $studentUser = $task->student?->user ?? $task->assignedTo;
                            $studentName = trim(implode(' ', array_filter([
                                $studentUser?->last_name,
                                $studentUser?->first_name,
                                $studentUser?->middle_name,
                            ])));
                            if ($studentName === '') {
                                $studentName = $studentUser?->name ?? '—';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-700">{{ $task->title }}</p>
                                @if($task->description)
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ $task->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ $studentName }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">
                                    {{ $roleLabels[$task->role] ?? $task->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ $facultyName }}</td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                @if($task->due_date)
                                    <span class="flex items-center gap-1">
                                        <span class="iconify" data-icon="mdi:calendar-outline"></span>
                                        {{ $task->due_date->format('M d, Y g:i A') }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <span class="iconify text-green-500" data-icon="mdi:check-circle-outline"></span>
                                    {{ optional($task->updated_at)->format('M d, Y') ?? '—' }}
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
{{-- Same layout as the faculty Team Details dialog, read-only: the dean
     oversees the work, the team's own faculty gives the verdict. --}}
<div id="teamInfoModal" class="ink-all fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTeamModal()"></div>
    <div class="tm-box relative bg-white shadow-2xl flex flex-col overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="modalTeamName">
        <div class="tm-head">
            <span class="tm-head-icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></span>
            <div class="min-w-0">
                <p class="tm-kicker">Team details</p>
                <h3 id="modalTeamName" class="tm-title">Team</h3>
                <p id="modalTeamSummary" class="tm-sub"></p>
            </div>
            <button type="button" onclick="closeTeamModal()" class="tm-close">
                <span class="iconify" data-icon="mdi:close"></span> Close
            </button>
        </div>

        <div class="tm-body">
            <nav class="tm-nav" aria-label="Team details sections">
                <button type="button" onclick="switchTeamModalTab('members')" id="team-tab-members" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Team Members &amp; Roles</span>
                        <span class="tm-tab-desc">Who is in the team and what each one does</span>
                        <span id="teamTabCountMembers" class="tm-tab-count"></span>
                    </span>
                </button>
                <button type="button" onclick="switchTeamModalTab('concept')" id="team-tab-concept" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:lightbulb-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Hotel Concept</span>
                        <span class="tm-tab-desc">The hotel ideas the team proposed</span>
                    </span>
                </button>
                <button type="button" onclick="switchTeamModalTab('tasks')" id="team-tab-tasks" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:clipboard-text-clock-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Team Task Activity</span>
                        <span class="tm-tab-desc">Every task given to the team and how far it is</span>
                        <span id="teamTabCountTasks" class="tm-tab-count"></span>
                    </span>
                </button>
            </nav>

            <div class="tm-content">
                <!-- Members -->
                <div id="team-panel-members">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Team Members &amp; Roles</h4>
                        <p class="tm-panel-note">Each card shows a student and the part of the hotel they handle. Choose See activity to read everything that student has done.</p>
                    </div>
                    <div id="teamModalMembersBody" class="tm-member-grid"></div>

                    <!-- Selected member's centralized activity log -->
                    <div id="memberActivityPanel" class="hidden">
                        <div class="tm-card mt-5">
                            <div class="tm-card-head">
                                <p class="tm-card-title" id="memberActivityPanelTitle">Member Activity</p>
                                <button type="button" onclick="closeMemberActivityPanel()" class="tm-btn tm-btn-sm">
                                    <span class="iconify" data-icon="mdi:chevron-up"></span> Hide
                                </button>
                            </div>
                            <div id="memberActivityPanelBody" class="tm-activity-list"></div>
                        </div>
                    </div>
                </div>

                <!-- Front Desk's hotel concepts and their edit history (loaded when the modal opens) -->
                <div id="team-panel-concept" class="hidden">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Hotel Concept</h4>
                        <p class="tm-panel-note">The team proposes two hotel ideas and their faculty approves one. You can read both here, with every change the team made.</p>
                    </div>
                    <div id="teamModalConceptBody" class="tm-concept-grid">
                        <div class="tm-empty tm-span-all">Loading hotel concept…</div>
                    </div>
                </div>

                <!-- Every task the team has been given, at any stage. View only. -->
                <div id="team-panel-tasks" class="hidden">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Team Task Activity</h4>
                        <p class="tm-panel-note">Every task this team has been given, who has it, when it is due and how far it is.</p>
                    </div>
                    <div id="teamModalActivityStats" class="tm-stats"></div>
                    <div class="tm-card">
                        <table class="tm-table">
                            <colgroup>
                                <col>
                                <col style="width: 10.5rem;">
                                <col style="width: 8.5rem;">
                                <col style="width: 7rem;">
                                <col style="width: 9rem;">
                                <col style="width: 8rem;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Student</th>
                                    <th>Department</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Steps done</th>
                                </tr>
                            </thead>
                            <tbody id="teamModalActivityBody"></tbody>
                        </table>
                        <div id="teamModalActivityPager" class="tm-pager hidden flex items-center justify-between gap-2">
                            <button type="button" id="teamModalActivityPrev" class="tm-btn tm-btn-sm">
                                <span class="iconify" data-icon="mdi:chevron-left"></span> Previous
                            </button>
                            <span class="tm-pager-label"><span id="teamModalActivityPageLabel"></span> <span id="teamModalActivityMeta"></span></span>
                            <button type="button" id="teamModalActivityNext" class="tm-btn tm-btn-sm">
                                Next <span class="iconify" data-icon="mdi:chevron-right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Faculty Modal -->
<div id="createFacultyModal" class="ink-all fixed inset-0 z-50 hidden">
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
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600 text-xs font-medium">+63</span>
                        <input name="phone_number" type="text" inputmode="numeric" autocomplete="tel-national" pattern="9[0-9]{9}" title="10 digits after +63, starting with 9 (e.g. 9123456789)" data-ph-phone placeholder="912 345 6789" value="{{ old('phone_number') }}" class="w-full h-10 pl-12 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                    </div>
                </div>
                {{-- No block to choose. It is always the next free class letter, so
                     there was nothing to decide here and picking one already taken
                     stopped the account being created. It is assigned on save and
                     can be changed from User Management afterwards. --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                    {{-- Active, and not a choice: an account created switched off is
                         an account nobody can sign in to, which is a thing to do to an
                         existing account rather than a way to open one. Deactivating
                         is on the update form. --}}
                    <input type="hidden" name="status" value="active">
                    <div class="w-full h-10 px-3 bg-slate-100 border border-slate-200 rounded-xl text-sm flex items-center gap-2 text-slate-500 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </div>
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
@include('partials.team-dialog-styles')
<style>
    /* Black text across Teams Overview and its popups. Beats every text-* colour
       utility, hover ones included; white text on filled buttons and avatars and
       icon colours are left alone. */
    .ink-all [class*="text-"]:not(.text-white):not(.iconify),
    .ink-all [class*="text-"]:not(.text-white):not(.iconify):hover,
    .glass-header h2,
    .glass-header h2 + p { color: #111; }

    #teamsTable {
        border-collapse: collapse;
        width: 100%;
    }
    #teamsTable thead tr {
        background: #FAF6F5;
    }
    #teamsTable tbody tr {
        border-bottom: 1px solid #F2E9E7;
        transition: background 0.15s;
    }
    #teamsTable tbody tr:last-child {
        border-bottom: none;
    }
    #teamsTable tbody tr:hover {
        background: #FAF6F5;
    }
    /* A hovered View button turns light gray, like Update on Manage User. */
    #teamsTable .view-btn:hover { background: #dadada; }
    /* The faculty header above each set of teams. */
    #teamsTable tbody tr.faculty-group-row,
    #teamsTable tbody tr.faculty-group-row:hover {
        background: #F3F2F1;
        border-top: 1px solid #E4E2E0;
    }
</style>
@endpush

@push('scripts')
<script>
    let teamModalActivityLogs = [];
    let teamModalActivityPage = 1;
    const TEAM_MODAL_ACTIVITY_PER_PAGE = 5;
    // Status colour per DeanController::teamTaskRow() status, from the shared
    // status-badge-* / status-fill-* classes. Submitted is the students' Pending.
    const TASK_STATUS_KEYS = {
        not_started: 'not_started',
        in_progress: 'in_progress',
        needs_revision: 'revision',
        submitted: 'pending',
        completed: 'completed',
    };

    function escHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    const STAT_COLOURS = { pending: '#D97706', completed: '#16A34A', in_progress: '#2563EB' };

    /* Three counts over the team's tasks, so the dean sees at a glance how the
       team is doing. */
    function renderTeamModalActivityStats(logs) {
        const box = document.getElementById('teamModalActivityStats');
        const keyOf = (l) => TASK_STATUS_KEYS[l.status] || 'not_started';
        const waiting = logs.filter((l) => keyOf(l) === 'pending').length;
        const done = logs.filter((l) => keyOf(l) === 'completed').length;
        const working = logs.length - waiting - done;

        const tabCount = document.getElementById('teamTabCountTasks');
        if (tabCount) tabCount.textContent = logs.length ? logs.length + (logs.length === 1 ? ' task' : ' tasks') : '';
        if (!box) return;

        const stat = (key, icon, num, label) =>
            '<div class="tm-stat">'
                + '<span class="tm-stat-icon status-badge-' + key + '"><span class="iconify" data-icon="' + icon + '"></span></span>'
                // Inline colour: this page's own black-text rule outranks status-text-*.
                + '<div><p class="tm-stat-num" style="color:' + STAT_COLOURS[key] + '">' + num + '</p><p class="tm-stat-label">' + label + '</p></div>'
            + '</div>';

        box.innerHTML = logs.length
            ? stat('pending', 'mdi:clock-alert-outline', waiting, 'Waiting for faculty review')
                + stat('completed', 'mdi:check-circle-outline', done, 'Completed')
                + stat('in_progress', 'mdi:progress-pencil', working, 'Still being worked on')
            : '';
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

        renderTeamModalActivityStats(logs);

        if (total === 0) {
            activityBody.innerHTML = '<tr><td colspan="6"><div class="tm-empty">'
                + '<span class="iconify" data-icon="mdi:clipboard-outline"></span>'
                + 'No tasks have been given to this team yet.</div></td></tr>';
            if (pager) pager.classList.add('hidden');
            if (meta) meta.textContent = '';
            return;
        }

        const start = (teamModalActivityPage - 1) * TEAM_MODAL_ACTIVITY_PER_PAGE;
        const pageLogs = logs.slice(start, start + TEAM_MODAL_ACTIVITY_PER_PAGE);
        const end = start + pageLogs.length;

        activityBody.innerHTML = pageLogs.map(function (log) {
            const key = TASK_STATUS_KEYS[log.status] || 'not_started';
            const statusBadge = '<span class="tm-badge status-badge-' + key + '">'
                + '<span class="tm-dot status-fill-' + key + '"></span>' + escHtml(log.status_label) + '</span>';
            const progress = log.progress_total > 0
                ? '<p class="tm-muted">' + log.progress_done + ' of ' + log.progress_total + '</p>'
                    + '<div class="rv-bar" style="margin-top:.35rem"><span class="status-fill-' + key + '" style="width:'
                    + Math.round(log.progress_done / log.progress_total * 100) + '%"></span></div>'
                : '<span class="tm-muted">None yet</span>';
            const student = log.student
                ? '<div class="flex items-center gap-2 min-w-0">'
                    + '<span class="tm-avatar tm-avatar-sm">' + escHtml(String(log.student).charAt(0).toUpperCase()) + '</span>'
                    + '<span class="tm-ellipsis" title="' + escHtml(log.student) + '">' + escHtml(log.student) + '</span>'
                  + '</div>'
                : '<span class="tm-muted">Not taken yet</span>';

            return '<tr>' +
                '<td>' +
                    (log.code ? '<p class="tm-muted">' + escHtml(log.code) + '</p>' : '') +
                    '<p class="tm-task-name">' + escHtml(log.title) + '</p>' +
                '</td>' +
                '<td>' + student + '</td>' +
                '<td>' + escHtml(log.role_label || log.role || 'None') + '</td>' +
                '<td' + (log.due_date ? '' : ' class="tm-muted"') + '>' + escHtml(log.due_date || 'No date') + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + progress + '</td>' +
            '</tr>';
        }).join('');

        if (meta) meta.textContent = '(tasks ' + (start + 1) + ' to ' + end + ' of ' + total + ')';
        if (pageLabel) pageLabel.textContent = 'Page ' + teamModalActivityPage + ' of ' + totalPages;
        if (pager) pager.classList.toggle('hidden', total <= TEAM_MODAL_ACTIVITY_PER_PAGE);
        if (prevBtn) prevBtn.disabled = teamModalActivityPage <= 1;
        if (nextBtn) nextBtn.disabled = teamModalActivityPage >= totalPages;
    }

    function openTeamModal(groupName, members, createdAt, activityLogs, facultyId) {
        const logs = Array.isArray(activityLogs) ? activityLogs : [];
        members = Array.isArray(members) ? members : [];
        loadTeamHotelConcept(facultyId, groupName);

        document.getElementById('modalTeamName').textContent = groupName ? 'Team ' + groupName : 'Team';
        document.getElementById('modalTeamSummary').textContent = [
            members.length + (members.length === 1 ? ' member' : ' members'),
            createdAt ? 'Created ' + createdAt : '',
        ].filter(Boolean).join(' · ');
        document.getElementById('teamTabCountMembers').textContent = members.length
            ? members.length + (members.length === 1 ? ' member' : ' members') : '';

        const grid = document.getElementById('teamModalMembersBody');
        if (members.length === 0) {
            grid.innerHTML = '<div class="tm-empty" style="grid-column: 1 / -1;">'
                + '<span class="iconify" data-icon="mdi:account-off-outline"></span>'
                + 'This team has no members yet.</div>';
        } else {
            grid.innerHTML = members.map(function (m, i) {
                const roleLabels = m.role_labels || [m.role_label || m.role];
                const roleChips = roleLabels.filter(Boolean).map(function (rl) {
                    return '<span class="tm-chip"><span class="iconify" data-icon="mdi:briefcase-outline"></span>' + escHtml(rl) + '</span>';
                }).join('') || '<span class="tm-muted">No role yet</span>';
                const activityBtn = m.user_id
                    ? '<button type="button" data-activity-user="' + Number(m.user_id) + '"'
                        + ' data-activity-name="' + escHtml(m.name) + '" class="tm-btn"'
                        + ' title="Show everything this student has done">'
                        + '<span class="iconify" data-icon="mdi:clipboard-text-clock-outline"></span> See activity'
                      + '</button>'
                    : '';
                return '<div class="tm-member" data-member-card="' + Number(m.user_id || 0) + '">'
                    + '<div class="tm-member-top">'
                        + '<span class="tm-avatar">' + escHtml(String(m.name || '?').charAt(0).toUpperCase()) + '</span>'
                        + '<div class="min-w-0">'
                            + '<p class="tm-member-name" title="' + escHtml(m.name) + '">' + escHtml(m.name) + '</p>'
                            + '<p class="tm-member-no">Member ' + (i + 1) + '</p>'
                        + '</div>'
                    + '</div>'
                    + '<div class="tm-chips">' + roleChips + '</div>'
                    + activityBtn
                + '</div>';
            }).join('');
        }

        closeMemberActivityPanel();

        teamModalActivityLogs = logs;
        teamModalActivityPage = 1;
        renderTeamModalActivityPage();

        switchTeamModalTab('members');
        document.getElementById('teamInfoModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /* Team Details sections: Members & Roles / Hotel Concept / Team Task Activity */
    function switchTeamModalTab(tabId) {
        const tabs = ['members', 'concept', 'tasks'];
        const current = tabs.includes(tabId) ? tabId : 'members';

        tabs.forEach(function (tab) {
            document.getElementById('team-panel-' + tab)?.classList.toggle('hidden', tab !== current);

            const btn = document.getElementById('team-tab-' + tab);
            if (!btn) return;
            btn.classList.toggle('is-on', tab === current);
            btn.setAttribute('aria-current', tab === current ? 'true' : 'false');
        });
    }

    /* Front Desk's hotel concept for the open team. Fetched rather than inlined:
       this page lists every faculty's teams, and each carries a full history. */
    const TEAM_CONCEPT_URL = @json(route('dean.teams.hotel-concept'));

    function loadTeamHotelConcept(facultyId, groupName) {
        const body = document.getElementById('teamModalConceptBody');
        if (!body) return;

        body.innerHTML = '<div class="tm-empty tm-span-all">Loading hotel concept…</div>';

        const url = TEAM_CONCEPT_URL
            + '?faculty_id=' + encodeURIComponent(facultyId || '')
            + '&group_name=' + encodeURIComponent(groupName || '');

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json().then(data => {
                if (!res.ok) throw new Error(data.error || 'Could not load the hotel concept.');
                return data;
            }))
            .then(data => { body.innerHTML = renderTeamConceptSlots(data); })
            .catch(err => {
                body.innerHTML = '<div class="tm-empty is-error tm-span-all">'
                    + escHtml(err.message || 'Could not load the hotel concept.') + '</div>';
            });
    }

    /* Where a concept stands in the workflow. Same colours the students and their
       faculty see. */
    const CONCEPT_STATUS_CLASSES = {
        draft: 'bg-slate-100 text-slate-600 border-slate-200',
        submitted: 'status-badge-pending',
        needs_revision: 'status-badge-revision',
        approved: 'status-badge-completed',
        not_selected: 'bg-slate-100 text-slate-500 border-slate-300',
    };

    /* Both of a team's concepts side by side, each with its own history. Read-only:
       the dean oversees the work, the team's own faculty gives the verdict. */
    function renderTeamConceptSlots(data) {
        return (data.slots || []).map(function (entry) {
            if (!entry.concept) {
                return '<div class="tm-concept-empty">'
                    + '<p class="tm-card-title">' + escHtml(entry.slot_label) + '</p>'
                    + '<p class="tm-panel-note mt-1">Not proposed yet.</p>'
                + '</div>';
            }
            return '<div class="tm-card">'
                + '<div class="tm-card-head"><p class="tm-card-title">' + escHtml(entry.slot_label) + '</p></div>'
                + '<div class="tm-concept-body">' + renderTeamHotelConcept(entry) + '</div>'
            + '</div>';
        }).join('') || '<div class="tm-empty tm-span-all"><span class="iconify" data-icon="mdi:lightbulb-off-outline"></span>This team has no hotel concepts yet.</div>';
    }

    /* One concept and its edit history, inside its card. */
    function renderTeamHotelConcept(data) {
        const concept = data.concept;
        const history = Array.isArray(data.history) ? data.history : [];
        const status = concept.status || 'draft';
        const statusBadge = '<span class="tm-badge ' + (CONCEPT_STATUS_CLASSES[status] || CONCEPT_STATUS_CLASSES.draft) + '">'
            + escHtml(concept.status_label) + '</span>';

        const conceptBlock =
            '<div class="tm-chips">' + statusBadge + '<span class="tm-chip">' + escHtml(concept.hotel_type_label) + '</span></div>'
            + '<p class="tm-concept-name mt-2">' + escHtml(concept.title) + '</p>'
            + (concept.tagline ? '<p class="tm-concept-tagline">' + escHtml(concept.tagline) + '</p>' : '')
            + '<p class="tm-concept-desc">' + escHtml(concept.description) + '</p>'
            + ((concept.updated_by || concept.updated_at)
                ? '<p class="tm-concept-meta">Last changed'
                    + (concept.updated_by ? ' by <b>' + escHtml(concept.updated_by) + '</b>' : '')
                    + (concept.updated_at ? ' on ' + escHtml(concept.updated_at) : '') + '</p>'
                : '')
            + (concept.faculty_feedback
                ? '<div class="tm-note"><p class="tm-note-label">Faculty feedback</p>'
                    + '<p class="tm-note-text">' + escHtml(concept.faculty_feedback) + '</p></div>'
                : '');

        const historyRows = history.length
            ? history.map(function (entry) {
                const changes = (entry.changes || []).map(function (change) {
                    return '<li class="mt-1"><b>' + escHtml(change.label) + ':</b> '
                        + '<s class="tm-muted">' + (escHtml(change.from) || 'empty') + '</s> to '
                        + escHtml(change.to) + '</li>';
                }).join('');

                return '<div class="tm-history-row">'
                    + '<p><b>' + escHtml(entry.editor) + '</b> ' + escHtml(entry.action_label) + '</p>'
                    + '<p class="tm-muted">' + escHtml(entry.created_at) + ' (' + escHtml(entry.created_at_human) + ')</p>'
                    + (changes
                        ? '<ul>' + changes + '</ul>'
                        : '<p class="mt-1">' + escHtml(entry.title) + ', ' + escHtml(entry.hotel_type_label) + '</p>')
                + '</div>';
            }).join('')
            : '<div class="tm-history-row tm-muted">No changes recorded yet.</div>';

        return conceptBlock
            + '<div class="tm-history">'
                + '<p class="tm-field-label">Changes made so far</p>'
                + '<div class="tm-history-list">' + historyRows + '</div>'
            + '</div>';
    }

    /* Centralized activity log — same table and endpoint the faculty portal reads. */
    const MEMBER_ACTIVITY_URL = @json(route('dean.activity.user', ['user' => '__ID__']));

    function closeMemberActivityPanel() {
        const panel = document.getElementById('memberActivityPanel');
        if (panel) panel.classList.add('hidden');
        document.querySelectorAll('[data-member-card].is-on').forEach((c) => c.classList.remove('is-on'));
    }

    /* Delegated: the buttons are rebuilt whenever the team modal opens, and an
       inline onclick cannot carry a name containing quotes without breaking the
       attribute it lives in. */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest ? e.target.closest('[data-activity-user]') : null;
        if (!btn) return;
        viewMemberActivity(btn.getAttribute('data-activity-user'), btn.getAttribute('data-activity-name'));
    });

    function viewMemberActivity(userId, memberName) {
        const panel = document.getElementById('memberActivityPanel');
        const title = document.getElementById('memberActivityPanelTitle');
        const body = document.getElementById('memberActivityPanelBody');
        if (!panel || !body) return;

        panel.classList.remove('hidden');
        document.querySelectorAll('[data-member-card]').forEach((c) => {
            c.classList.toggle('is-on', c.getAttribute('data-member-card') === String(userId));
        });
        if (title) title.textContent = 'What ' + (memberName || 'this member') + ' has done';
        body.innerHTML = '<div class="tm-empty">Loading activity…</div>';
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        fetch(MEMBER_ACTIVITY_URL.replace('__ID__', String(userId)), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) throw new Error(data.error || 'Could not load activity logs.');
                    return data;
                });
            })
            .then(function (data) {
                body.innerHTML = renderActivityRows(data.logs || []);
            })
            .catch(function (err) {
                body.innerHTML = '<div class="tm-empty is-error">'
                    + escHtml(err.message || 'Could not load activity logs.') + '</div>';
            });
    }

    function renderActivityRows(logs) {
        if (!logs.length) {
            return '<div class="tm-empty">No recorded activity for this member yet.</div>';
        }
        return logs.map(function (log) {
            return '<div class="tm-activity-row">'
                + '<span class="tm-chip">' + escHtml(log.activity_label || log.activity || 'Activity') + '</span>'
                + '<div class="min-w-0 flex-1">'
                    + '<p class="tm-activity-desc">' + escHtml(log.description || '') + '</p>'
                    + '<p class="tm-activity-time">' + escHtml(log.created_at || '')
                        + (log.created_at_human ? ' (' + escHtml(log.created_at_human) + ')' : '') + '</p>'
                + '</div>'
            + '</div>';
        }).join('');
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
