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

    /* ── Send back (concept revision) ──
       Spelled out rather than composed from utilities: public/css/app.css is a
       frozen build with no teal in it, so the bg-teal-600 this button used to
       carry resolved to nothing and it rendered white on white. Rose rather than
       teal so it reads as the opposite of the emerald Approve beside it, and
       matches the rose "Needs revision" badge the team sees on their side. */
    .btn-sendback {
        background:#E11D48; color:#fff; border:1px solid #BE123C;
        box-shadow:0 4px 12px -2px rgba(225,29,72,.35);
    }
    .btn-sendback:hover { background:#BE123C; }

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
    #teamsTable .col-team { width: 16%; }
    #teamsTable .col-concept { width: 18%; }
    #teamsTable .col-count { width: 10%; }
    #teamsTable .col-roles { width: 38%; }
    #teamsTable .col-action { width: 18%; }
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
                        <th class="col-concept text-left px-3 py-2.5 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Hotel Concept</th>
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
                                    'user_id'    => $u?->user_id,
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
                                {{-- Both concepts, named by slot, so the column says which
                                     of the two each title is. --}}
                                @php $teamConcepts = ($conceptsByGroup ?? collect())->get($groupName, collect()); @endphp
                                @forelse($teamConcepts as $concept)
                                    <div class="{{ !$loop->first ? 'mt-1.5 pt-1.5 border-t border-slate-100' : '' }}">
                                        <span class="font-semibold text-slate-700 text-sm truncate-cell" title="{{ $concept->title }}">
                                            {{ \App\Support\HotelConceptDesk::slotLabel($concept->slot) }}: {{ $concept->title }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 truncate-cell">
                                            {{ $concept->hotel_type_label }} · {{ \App\Support\HotelConceptDesk::statusLabel($concept) }}
                                        </span>
                                    </div>
                                @empty
                                    <span class="text-[11px] text-slate-400">Not proposed yet</span>
                                @endforelse
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
                                        class="inline-flex items-center px-2.5 py-1 bg-brand-soft text-brand border border-brand/10 rounded-lg text-[11px] font-bold hover:bg-brand/10 transition whitespace-nowrap">
                                        Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
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
                                    Add Teams{{ $activeClass ? ' for ' . $activeClass->name : '' }}
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

        <!-- Modal Tabs -->
        <div class="flex border-b border-slate-200 bg-slate-50 flex-shrink-0">
            <button type="button" onclick="switchTeamModalTab('members')" id="team-tab-members"
                class="flex-1 py-2.5 text-xs font-bold text-center transition border-b-2 border-rose-500 text-rose-600">
                <span class="iconify inline-block mr-1.5 align-[-2px]" data-icon="mdi:account-group-outline"></span>Team Members &amp; Roles
            </button>
            <button type="button" onclick="switchTeamModalTab('tasks')" id="team-tab-tasks"
                class="flex-1 py-2.5 text-xs font-bold text-center transition border-b-2 border-transparent text-slate-400 hover:text-slate-600">
                <span class="iconify inline-block mr-1.5 align-[-2px]" data-icon="mdi:clipboard-text-clock-outline"></span>Team Task Activity
            </button>
            <button type="button" onclick="switchTeamModalTab('concept')" id="team-tab-concept"
                class="flex-1 py-2.5 text-xs font-bold text-center transition border-b-2 border-transparent text-slate-400 hover:text-slate-600">
                <span class="iconify inline-block mr-1.5 align-[-2px]" data-icon="mdi:lightbulb-outline"></span>Hotel Concept
            </button>
        </div>

        <!-- Modal Body -->
        <div class="overflow-y-auto flex-1 p-4 space-y-4">
            <!-- Members Table -->
            <div id="team-panel-members">
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm" style="table-layout: fixed;">
                        <colgroup>
                            <col style="width: 2.5rem;">
                            <col>
                            <col style="width: 10rem;">
                            <col style="width: 6.5rem;">
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Member</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="text-center px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Activity</th>
                            </tr>
                        </thead>
                        <tbody id="teamModalMembersBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>

                <!-- Selected member's centralized activity log (expandable section) -->
                <div id="memberActivityPanel" class="hidden mt-4">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand" id="memberActivityPanelTitle">Member Activity</p>
                        <button type="button" onclick="closeMemberActivityPanel()"
                            class="text-[10px] font-bold text-slate-400 hover:text-slate-600 transition">Hide</button>
                    </div>
                    <div class="border border-brand/20 bg-brand-soft/30 rounded-lg overflow-hidden">
                        <div id="memberActivityPanelBody" class="max-h-72 overflow-y-auto divide-y divide-slate-100 bg-white"></div>
                    </div>
                </div>
            </div>

            <!-- Front Desk's hotel concept + its edit history (loaded when the modal opens) -->
            <div id="team-panel-concept" class="hidden">
                <div id="teamConceptError" class="hidden mb-3 px-3 py-2 rounded-lg bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-600"></div>
                <div id="teamModalConceptBody" class="space-y-4">
                    <div class="px-3 py-6 text-center text-xs text-slate-400">Loading hotel concept…</div>
                </div>
            </div>

            <!-- Team task activity (assignment history) -->
            <div id="team-panel-tasks" class="hidden">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-[10px] font-semibold text-slate-400">Use the Review button to open a submitted task and leave feedback.</span>
                    <span id="teamModalActivityMeta" class="text-[10px] font-semibold text-slate-400"></span>
                </div>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm" style="table-layout: fixed;">
                        <colgroup>
                            <col>
                            <col style="width: 8.5rem;">
                            <col style="width: 6rem;">
                            <col style="width: 6.5rem;">
                            <col style="width: 6rem;">
                            <col style="width: 6.5rem;">
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Task</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Student</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Submitted</th>
                                <th class="text-center px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Review</th>
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


<!-- ═══════ REVIEW SUBMISSION MODAL — the student's actual work + feedback ═══════ -->
<div id="taskReviewModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTaskReview()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden">

        <div class="bg-rose-50 px-4 py-3 border-b border-rose-100 flex justify-between items-center flex-shrink-0">
            <div class="min-w-0">
                <h4 id="reviewTaskTitle" class="font-bold text-rose-700 text-sm truncate">Review Submission</h4>
                <p id="reviewTaskMeta" class="text-[11px] text-slate-500 truncate"></p>
            </div>
            <button type="button" onclick="closeTaskReview()"
                class="text-slate-400 hover:text-rose-500 hover:bg-white w-7 h-7 rounded-full transition flex items-center justify-center shrink-0">
                <span class="iconify text-lg" data-icon="mdi:close"></span>
            </button>
        </div>

        <div class="flex-1 min-h-0 flex flex-col lg:flex-row">
            <!-- The work itself: a site to look at for most tasks, the concept text
                 itself for the hotel concept — that submission has no page to render. -->
            <div class="flex-1 min-h-0 bg-slate-100 flex flex-col border-b lg:border-b-0 lg:border-r border-slate-200">
                <div class="px-3 py-2 flex items-center justify-between gap-2 bg-white border-b border-slate-100 flex-shrink-0">
                    <span id="reviewWorkLabel" class="text-[10px] font-bold uppercase tracking-wider text-slate-400">The team's live site</span>
                    <div class="flex items-center gap-3">
                        {{-- Only rendered once this task has a submission to anchor "After" to. --}}
                        <div id="reviewCompareToggle" class="hidden inline-flex rounded-lg bg-slate-100 p-0.5">
                            <button type="button" data-compare="before"
                                class="px-2.5 py-1 rounded-md text-[10px] font-bold text-slate-500 transition">Before</button>
                            <button type="button" data-compare="after"
                                class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-white text-slate-800 shadow-sm transition">After</button>
                            <button type="button" data-compare="changes"
                                class="px-2.5 py-1 rounded-md text-[10px] font-bold text-slate-500 transition">Changes <span id="reviewChangesCount"></span></button>
                        </div>
                        <a id="reviewOpenTab" href="#" target="_blank" rel="noopener"
                           class="text-[10px] font-bold text-brand hover:underline hidden">Open in new tab ↗</a>
                    </div>
                </div>
                <div class="flex-1 min-h-0 relative">
                    <div id="reviewPreviewEmpty" class="absolute inset-0 flex items-center justify-center text-center px-6">
                        <p class="text-xs text-slate-400">No site to preview for this submission.</p>
                    </div>
                    <iframe id="reviewPreviewFrame" src="" title="Team site preview"
                            class="w-full h-full border-0 bg-white hidden" style="min-height: 22rem;"></iframe>
                    <div id="reviewConceptPane" class="absolute inset-0 overflow-y-auto bg-white p-4 hidden" style="min-height: 22rem;"></div>
                    <div id="reviewChangesPane" class="absolute inset-0 overflow-y-auto bg-white p-3 hidden" style="min-height: 22rem;"></div>
                </div>
            </div>

            <!-- Feedback -->
            <div class="w-full lg:w-80 shrink-0 flex flex-col min-h-0 overflow-y-auto">
                <div class="p-4 space-y-3">
                    <div id="reviewStatusRow" class="flex flex-wrap items-center gap-1.5"></div>

                    <div id="reviewPrevFeedbackWrap" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700 mb-1">Previous feedback</p>
                        <p id="reviewPrevFeedback" class="text-xs text-amber-800 whitespace-pre-line"></p>
                        <p id="reviewPrevFeedbackMeta" class="text-[10px] text-amber-600 mt-1"></p>
                    </div>

                    {{-- One verdict for the whole task. The hotel concept is judged one
                         concept at a time instead, so this block hides and each concept
                         card in the left pane carries its own controls. --}}
                    <div id="reviewDecisionBlock" class="space-y-3">
                        {{-- Step 1: pick a verdict. The feedback box only belongs to
                             Revise, so it stays out of the way until that is chosen. --}}
                        <div id="reviewChoiceStep" class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" id="reviewReviseBtn" onclick="showReviseStep()"
                                class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition bg-white text-rose-600 border border-slate-200 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700">
                                <span class="iconify text-sm" data-icon="mdi:pencil-circle-outline"></span> Revise
                            </button>
                            <button type="button" id="reviewApproveBtn" onclick="submitTaskFeedback('approve')"
                                class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                                <span class="iconify text-sm" data-icon="mdi:check-circle-outline"></span> Approve
                            </button>
                        </div>

                        {{-- Step 2: revise only. --}}
                        <div id="reviewReviseStep" class="hidden space-y-3">
                            <div>
                                <label id="reviewFeedbackLabel" for="reviewFeedback" class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                                    Feedback to the student
                                </label>
                                <textarea id="reviewFeedback" rows="6" maxlength="2000"
                                    placeholder="What did they do well? What should change?"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs resize-none focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"></textarea>
                                <p class="text-[10px] text-slate-400 mt-1">Required — this is what the student will see.</p>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="hideReviseStep()"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition bg-slate-100 text-slate-700 border border-slate-300 hover:bg-slate-200 hover:text-slate-900">
                                    Cancel
                                </button>
                                <button type="button" id="reviewSendFeedbackBtn" onclick="submitTaskFeedback('revise')"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                                    <span class="iconify text-sm" data-icon="mdi:send-outline"></span> Send feedback
                                </button>   
                            </div>
                        </div>
                    </div>

                    <p id="reviewConceptHint" class="hidden text-[11px] text-slate-500 leading-relaxed">
                        This team proposed two concepts. Approve or send back each one on its
                        own card — your verdict on one does not touch the other.
                    </p>

                    <p id="reviewError" class="hidden text-[11px] font-semibold text-rose-600"></p>
                </div>
            </div>
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
                Add Teams
            </h4>
            <button onclick="closeCreateTeamModal()" class="text-slate-400 hover:text-brand hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Tabs -->
        <div class="flex border-b border-slate-200 bg-slate-50 flex-shrink-0">
            <button onclick="switchCreateModalTab('add_team')" id="modal-tab-add_team"
                class="flex-1 py-3 text-sm font-bold text-center transition border-b-2 border-brand text-brand">
                <span class="iconify inline-block mr-1.5" data-icon="mdi:account-multiple-plus-outline"></span> Add Teams
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
            // Roles each existing team already has assigned, used by the Insert tab so a
            // role already taken on the target team is disabled before the faculty even
            // picks it for the new member.
            $groupRolesMap = ($groups ?? collect())->mapWithKeys(fn ($members, $groupName) => [
                $groupName => $members->flatMap(fn ($m) => $m->roles->pluck('role'))->unique()->values(),
            ]);
        @endphp
        <script>
            window.EXISTING_TEAM_ROLES = @json($groupRolesMap);
            // Role keys are all JS had (TEAM_DEFAULT_ROLES); the labels live in PHP.
            window.TEAM_ROLE_LABELS = @json($teamRoleOptions);
        </script>

        <!-- Tab Panel: Add Team (single or multiple) -->
        <div id="modal-panel-add_team" class="flex-1 min-h-0 overflow-y-auto">
            @php $unassignedStudentCount = ($students ?? collect())->count(); @endphp
            <div class="px-6 pt-5">
                <div class="rounded-xl border {{ $unassignedStudentCount > 0 ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }} px-4 py-2.5 flex items-center gap-2 text-xs font-bold mb-4">
                    <span class="iconify text-base shrink-0" data-icon="{{ $unassignedStudentCount > 0 ? 'mdi:account-alert-outline' : 'mdi:check-circle-outline' }}"></span>
                    @if($unassignedStudentCount > 0)
                        {{ $unassignedStudentCount }} student{{ $unassignedStudentCount === 1 ? '' : 's' }} not yet assigned to a team{{ $activeClass ? ' in ' . $activeClass->name : '' }}
                    @else
                        All students{{ $activeClass ? ' in ' . $activeClass->name : '' }} are already assigned to a team
                    @endif
                </div>
                <div class="inline-flex w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50 p-1 gap-1">
                    <button type="button" id="createModeSingleBtn" onclick="switchCreateTeamMode('single')"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition bg-white text-brand shadow-sm border border-brand/10">
                        Single Team
                    </button>
                    <button type="button" id="createModeMultiBtn" onclick="switchCreateTeamMode('multiple')"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition text-slate-500 hover:text-slate-700">
                        Multiple Teams
                    </button>
                </div>
            </div>

            <form method="POST" id="createTeamFormSingle" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="create_team">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                @if($errors->any() && old('_form_source') === 'create_team')
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-6 space-y-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                        Create one team. Maximum <strong>4 members</strong> per team.
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Team Name <span class="text-red-400">*</span></label>
                        <input name="group_name" type="text" placeholder="e.g. Front Desk Team A"
                            value="{{ old('_form_source') === 'create_team' ? old('group_name') : '' }}"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                    </div>

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
                        <span id="createSelectedCount" class="text-xs font-bold text-slate-500 whitespace-nowrap sm:min-w-[7rem] text-right">0 / 4 selected</span>
                    </div>
                    <p id="createRandomizeNote" class="text-[11px] text-slate-400 -mt-3"></p>

                    {{-- Who is on the team right now, without scrolling the list below to
                         hunt for ticked boxes. Rendered from the checkboxes themselves, so
                         it follows a hand-made change as closely as it follows Randomize. --}}
                    <div id="createTeamSummary" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Selected members</p>
                        <div id="createTeamSummaryChips" class="flex flex-wrap gap-1.5"></div>
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
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Members & Roles <span class="text-red-400">*</span></label>
                            <span class="text-[11px] text-slate-400">Max 4 · {{ ($students ?? collect())->count() }} available</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="createStudentList">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->user_information_id;
                                        $selectedMembers = old('_form_source') === 'create_team' ? array_map('intval', old('members', [])) : [];
                                        $selectedRoles = old('_form_source') === 'create_team' ? old('member_roles.' . $sk, []) : [];
                                        if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                    @endphp
                                    <div class="team-student-card create-student-card rounded-xl bg-white border border-slate-200 p-3"
                                         data-search="{{ $searchBlob }}"
                                         data-student-name="{{ $dn }}">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="create-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}
                                                onchange="onSingleTeamMemberToggle(this)">
                                            @include('partials.user-avatar', [
                                                'user'         => $u,
                                                'name'         => $dn,
                                                'size'         => 'w-9 h-9',
                                                'rounded'      => 'rounded-lg',
                                                'extraClasses' => 'bg-brand-soft text-brand text-xs font-bold',
                                            ])
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">
                                                    #{{ $student->student_number }}
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
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}
                                                        onchange="refreshRoleAvailability('create')">
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

            <form method="POST" id="createTeamForm" action="{{ route('faculty.role.groups.store') }}" class="hidden">
                @csrf
                <input type="hidden" name="_form_source" value="create_teams_bulk">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">
                <div id="bulkTeamsHiddenInputs"></div>

                @if($errors->any() && old('_form_source') === 'create_teams_bulk')
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-6 space-y-5">
                    <div class="rounded-xl border border-brand/15 bg-brand-soft/40 px-4 py-3 text-xs text-slate-600 leading-relaxed">
                        Create multiple teams in one save. Each team must have <strong>exactly 4 members</strong>.
                    </div>

                    @php $availableStudentCount = ($students ?? collect())->count(); @endphp
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Number of Teams</label>
                            <input type="number" id="bulkTeamCount" min="0" max="40" readonly
                                value="{{ (int) floor($availableStudentCount / 4) }}"
                                class="w-full h-11 px-4 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 focus:outline-none cursor-not-allowed">
                        </div>
                        <button type="button" onclick="autoGroupStudentsIntoTeamsOfFour()"
                            class="h-11 px-4 rounded-xl bg-brand text-white text-xs font-bold hover:opacity-95 transition inline-flex items-center justify-center gap-1.5 shrink-0 shadow-md shadow-brand/20">
                            <span class="iconify" data-icon="mdi:account-multiple-check-outline"></span> Auto-Group (4 each)
                        </button>
                    </div>
                    <p id="bulkTeamHint" class="text-[11px] text-slate-400 -mt-2">
                        {{ $availableStudentCount }} unassigned student{{ $availableStudentCount === 1 ? '' : 's' }} available
                        @if($activeClass)
                            in {{ $activeClass->name }}
                        @endif
                        . Each team = exactly 4 members
                        @if($availableStudentCount > 0 && $availableStudentCount % 4 !== 0)
                            · {{ $availableStudentCount % 4 }} student{{ ($availableStudentCount % 4) === 1 ? '' : 's' }} will remain unassigned
                        @endif
                        .
                    </p>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mr-1">Roles</span>
                        @foreach($teamRoleOptions as $rk => $rl)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[11px] font-semibold text-slate-600">
                                <span class="w-2 h-2 rounded-full role-dot-{{ $rk }}"></span>{{ $rl }}
                            </span>
                        @endforeach
                    </div>

                    <div id="bulkTeamSlots" class="space-y-4"></div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Assign Students to Teams</label>
                            <div class="relative w-full max-w-xs ml-auto">
                                <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                                <input type="text" id="bulkStudentSearch" placeholder="Search students..."
                                    class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                    oninput="filterBulkStudentList()">
                            </div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="bulkStudentAssignList">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->user_information_id;
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                    @endphp
                                    <div class="bulk-student-row rounded-xl bg-white border border-slate-200 p-3"
                                         data-student-id="{{ $sk }}"
                                         data-student-name="{{ $dn }}"
                                         data-search="{{ $searchBlob }}">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                @include('partials.user-avatar', [
                                                    'user'         => $u,
                                                    'name'         => $dn,
                                                    'size'         => 'w-9 h-9',
                                                    'rounded'      => 'rounded-lg',
                                                    'extraClasses' => 'bg-brand-soft text-brand text-xs font-bold',
                                                ])
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                    <p class="text-[11px] text-slate-400 font-mono">
                                                        #{{ $student->student_number }}
                                                        @if($student->facultyClass)
                                                            · {{ $student->facultyClass->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <select class="bulk-team-select h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition sm:w-44"
                                                onchange="onBulkStudentTeamChange(this)">
                                                <option value="">Unassigned</option>
                                            </select>
                                        </div>
                                        <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-5 gap-1.5 bulk-role-grid">
                                            @foreach($teamRoleOptions as $rk => $rl)
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" value="{{ $rk }}"
                                                        class="bulk-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        onchange="refreshBulkTeamPreviews()">
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

                @if($errors->any() && old('_form_source') === 'insert_student')
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Existing Team <span class="text-red-400">*</span></label>
                        <select name="group_name" onchange="refreshRoleAvailability('insert')"
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
                                        $sk = $student->user_information_id;
                                        $selectedMembers = array_map('intval', old('members', []));
                                        $selectedRoles = old('member_roles.' . $sk, []);
                                        if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                    @endphp
                                    <div class="team-student-card insert-student-card rounded-xl bg-white border border-slate-200 p-3"
                                         data-search="{{ $searchBlob }}">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="insert-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}
                                                onchange="onTeamMemberToggle(this, 'insert')">
                                            @include('partials.user-avatar', [
                                                'user'         => $u,
                                                'name'         => $dn,
                                                'size'         => 'w-9 h-9',
                                                'rounded'      => 'rounded-lg',
                                                'extraClasses' => 'bg-brand-soft text-brand text-xs font-bold',
                                            ])
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">
                                                    #{{ $student->student_number }}
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
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}
                                                        onchange="refreshRoleAvailability('insert')">
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
                                        $sk = $student->user_information_id;
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
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
                                                    #{{ $student->student_number }}
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
                                                        data-student-id="{{ $sk }}"
                                                        onchange="refreshRoleAvailability('update')">
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
        {{-- The block tabs matter here: the team picker lists this block's teams, so
             faculty need to switch blocks without leaving the form. --}}
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'classes' => $classes ?? collect(),
            'activeClass' => $activeClass ?? null,
            'classCapacity' => $classCapacity ?? 40,
            'teamCountsByClass' => $teamCountsByClass ?? [],
        ])

        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 bg-brand-soft flex items-center gap-3">
            <div class="w-10 h-10 bg-brand/10 rounded-xl flex items-center justify-center">
                <span class="iconify text-brand text-xl" data-icon="mdi:clipboard-plus-outline"></span>
            </div>
            <div>
                <h3 class="font-bold text-brand text-base">Set New Task</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pick a team, tick the tasks, set a due date, and assign - all on one page.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('faculty.tasks.store') }}" id="taskAssignForm">
            @csrf
            {{-- Rides along so the redirect lands back on the block being worked in. --}}
            <input type="hidden" name="class" value="{{ request('class', $activeClass->letter ?? '') }}">

            @if($errors->any() && $activeTab === 'create_task')
                <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                    <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="p-6 space-y-6">
                {{-- The assignable checklist comes from App\Support\TaskChecklist and is
                     passed in as $taskChecklist. It used to be declared here as
                     $tasksByRole, which shadowed the controller's real task rows for
                     everything below it. --}}

                {{-- ═══════ TEAM ═══════
                     A task belongs to one team. The radio is a real form field, unlike
                     the department, which reaches the server as the tasks[] key. --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-brand text-white text-[11px] font-bold flex items-center justify-center shrink-0">1</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Which team is this task for?</h4>
                            <p class="text-xs text-slate-400">Only the team you pick will see it</p>
                        </div>
                    </div>

                    @if(($groups ?? collect())->isEmpty())
                        <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <span class="iconify text-3xl text-slate-300" data-icon="mdi:account-group-outline"></span>
                            <p class="text-sm font-bold text-slate-500 mt-2">No teams in this block yet</p>
                            <p class="text-xs text-slate-400 mt-1">Build a team first, then come back to set its tasks.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="taskTeamSelector">
                            @foreach($groups as $groupName => $members)
                                <label data-team="{{ $groupName }}"
                                    class="task-team-btn group p-4 rounded-xl border-2 border-slate-200 bg-white hover:border-brand/40 hover:shadow-md transition-all cursor-pointer flex items-start gap-3 text-left has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                                    <input type="radio" name="group_name" value="{{ $groupName }}"
                                        onchange="selectTeam(this.value)"
                                        {{ old('group_name') === $groupName ? 'checked' : '' }}
                                        class="mt-0.5 text-brand focus:ring-brand/30 task-team-radio">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold text-slate-700 truncate">{{ $groupName }}</span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">
                                            {{ $members->count() }} {{ Str::plural('member', $members->count()) }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ═══════ TASKS ═══════
                     One block per numbered step of the simulation. $taskSteps is the
                     checklist pivoted on position, so "Task 1" holds the first task of
                     every department at once and ticking its header hands that whole
                     stage to the team in one submit.

                     The field names are unchanged - tasks[role][] carrying the position
                     as its value - because storeTask() already loops every role in one
                     post. --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-brand text-white text-[11px] font-bold flex items-center justify-center shrink-0">2</span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700">Which tasks are you setting?</h4>
                                <p class="text-xs text-slate-400">Tick a task number to give that step to every department that has work in it</p>
                            </div>
                        </div>
                        <button type="button" id="selectAllTasksBtn" onclick="selectAllVisibleTasks()" class="text-xs font-semibold text-brand hover:underline">
                            Select All
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-2 max-h-[400px] overflow-y-auto pr-1">
                        @foreach($taskSteps ?? [] as $step => $stepTasks)
                            <div id="taskGroup-{{ $step }}" class="rounded-xl border-2 border-slate-200 bg-white overflow-hidden has-[:checked]:border-brand">
                                <div class="flex items-center gap-3 p-3.5 bg-slate-50">
                                    <input type="checkbox" id="taskGroupCheck-{{ $step }}"
                                        onchange="toggleTaskGroupAll({{ $step }})"
                                        class="task-group-check rounded border-slate-300 text-brand focus:ring-brand/30">
                                    <label for="taskGroupCheck-{{ $step }}" class="flex-1 min-w-0 cursor-pointer flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-slate-800">Task {{ $step + 1 }}</span>
                                        <span class="text-xs text-slate-400">{{ count($stepTasks) }} {{ Str::plural('department', count($stepTasks)) }}</span>
                                    </label>
                                    <button type="button" onclick="toggleTaskGroup(this, {{ $step }})"
                                        class="w-8 h-8 rounded-lg hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                                        <span class="iconify text-slate-400" data-icon="mdi:chevron-down"></span>
                                    </button>
                                </div>

                                {{-- Collapsed by default: the point of the group is that you
                                     rarely need to look inside it. --}}
                                <div id="taskGroupBody-{{ $step }}" class="hidden divide-y divide-slate-100">
                                    @foreach($rolesMeta ?? [] as $rKey => $rMeta)
                                        @continue(!isset($stepTasks[$rKey]))
                                        @php $task = $stepTasks[$rKey]; @endphp
                                        <label class="task-checkbox-card flex items-start gap-3 p-3.5 hover:bg-slate-50 transition cursor-pointer has-[:checked]:bg-brand-soft">
                                            <input type="checkbox" name="tasks[{{ $rKey }}][]" value="{{ $step }}"
                                                data-group="{{ $step }}" data-role="{{ $rKey }}"
                                                class="task-check task-step-check mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="iconify {{ $rMeta['color'] }}" data-icon="{{ $rMeta['icon'] }}"></span>
                                                    <span class="text-sm font-bold text-slate-800">{{ $task['title'] }}</span>
                                                    {{-- Website work shows a Before/After in the review; staff-tool work
                                                         changes no page, so it is judged by opening the department. --}}
                                                    @php $isOps = ($task['scope'] ?? 'site') === \App\Support\TaskChecklist::SCOPE_OPS; @endphp
                                                    <span class="shrink-0 px-1.5 py-0.5 rounded-full border border-slate-200 text-[9px] font-bold uppercase tracking-wide {{ $isOps ? 'bg-slate-100 text-slate-500' : 'bg-brand-soft text-brand' }}">
                                                        {{ \App\Support\TaskChecklist::scopeLabel($task['scope'] ?? 'site') }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $task['description'] }}</p>
                                                {{-- The headcount hint the department step used to carry. Filled by
                                                     updateRoleMemberCounts() once a team is picked. --}}
                                                <p class="text-[11px] font-semibold text-slate-400 mt-1">
                                                    {{ $rMeta['label'] }}<span data-role-count="{{ $rKey }}"></span>
                                                </p>
                                            </div>
                                            <input type="hidden" name="task_titles[{{ $rKey }}][{{ $step }}]" value="{{ $task['title'] }}">
                                            <input type="hidden" name="task_descriptions[{{ $rKey }}][{{ $step }}]" value="{{ $task['description'] }}">
                                            <input type="hidden" name="task_priorities[{{ $rKey }}][{{ $step }}]" value="{{ $task['priority'] }}">
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ═══════ DUE DATE ═══════ --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-500 text-[11px] font-bold flex items-center justify-center shrink-0">3</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Due date and time</h4>
                            <p class="text-xs text-slate-400">Optional - leave blank for no deadline</p>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 iconify text-slate-400" data-icon="mdi:calendar-clock"></span>
                            <input name="due_date" type="datetime-local" value="{{ old('due_date') }}"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">Students see this deadline in their own task list.</p>
                    </div>
                </div>

                {{-- ═══════ SUMMARY + ASSIGN ═══════
                     What the review step used to say, kept live at the foot of the form
                     so nothing has to be paged through to check it. --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 min-w-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="iconify text-emerald-500 shrink-0" data-icon="mdi:account-group-outline"></span>
                            <span class="text-xs text-slate-400 font-medium">Team</span>
                            <span class="text-sm font-bold text-slate-800 truncate" id="reviewTeamName">—</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="iconify text-brand shrink-0" data-icon="mdi:clipboard-text-outline"></span>
                            <span class="text-sm font-bold text-slate-800" id="reviewTaskCount">0 tasks</span>
                            <span class="text-xs text-slate-400" id="reviewDeptName">—</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="iconify text-blue-500 shrink-0" data-icon="mdi:calendar-outline"></span>
                            <span class="text-sm font-bold text-slate-800" id="reviewDueDate">No deadline</span>
                        </div>
                    </div>

                    <button type="submit" id="submitTasksBtn"
                        class="px-6 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100" disabled>
                        <span class="iconify" data-icon="mdi:check-circle-outline"></span> Set Tasks
                    </button>
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
const TEAM_MEMBER_MAX = 4;
let currentCreateTeamMode = 'single';

function setTeamsActionHighlight(activeKey) {
    document.querySelectorAll('.teams-action-btn').forEach((btn) => {
        const isActive = activeKey != null && btn.getAttribute('data-action-btn') === activeKey;
        btn.classList.toggle('is-active', isActive);
        btn.classList.toggle('bg-brand', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('shadow-md', isActive);
        btn.classList.toggle('shadow-brand/20', isActive);
        btn.classList.toggle('border-transparent', isActive);
        btn.classList.toggle('bg-white', !isActive);
        btn.classList.toggle('text-slate-600', !isActive);
        btn.classList.toggle('border-slate-200', !isActive);
        btn.classList.toggle('hover:border-brand/40', !isActive);
        btn.classList.toggle('hover:text-brand', !isActive);
        btn.classList.add('border');
    });
}

function openCreateTeamModal() {
    setTeamsActionHighlight('add_team');
    document.getElementById('createTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    switchCreateTeamMode(currentCreateTeamMode);
    if (currentCreateTeamMode === 'multiple') {
        autoGroupStudentsIntoTeamsOfFour();
    } else {
        updateTeamSelectedCount('create');
    }
}

function closeCreateTeamModal() {
    document.getElementById('createTeamModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Restore highlight: Set Task stays active on create_task tab; otherwise clear Add Team highlight
    const onSetTask = document.getElementById('panel-create_task')?.classList.contains('active');
    setTeamsActionHighlight(onSetTask ? 'set_task' : null);
}

function switchCreateTeamMode(mode) {
    currentCreateTeamMode = mode === 'multiple' ? 'multiple' : 'single';
    const singleForm = document.getElementById('createTeamFormSingle');
    const multiForm = document.getElementById('createTeamForm');
    const singleBtn = document.getElementById('createModeSingleBtn');
    const multiBtn = document.getElementById('createModeMultiBtn');

    if (singleForm) singleForm.classList.toggle('hidden', currentCreateTeamMode !== 'single');
    if (multiForm) multiForm.classList.toggle('hidden', currentCreateTeamMode !== 'multiple');

    const activeCls = ['bg-white', 'text-brand', 'shadow-sm', 'border', 'border-brand/10'];
    const idleCls = ['text-slate-500', 'hover:text-slate-700'];

    function styleModeBtn(btn, active) {
        if (!btn) return;
        activeCls.forEach((c) => btn.classList.toggle(c, active));
        idleCls.forEach((c) => btn.classList.toggle(c, !active));
        if (!active) btn.classList.remove('border', 'border-brand/10');
    }

    styleModeBtn(singleBtn, currentCreateTeamMode === 'single');
    styleModeBtn(multiBtn, currentCreateTeamMode === 'multiple');

    if (currentCreateTeamMode === 'multiple') {
        rebuildBulkTeamSlots(maxCompleteBulkTeams());
    } else {
        updateTeamSelectedCount('create');
    }
}

// ── Modal Tab Switcher ────────────────────────
let currentModalTab = 'add_team';

/* ── Review a submission: see the work, then give feedback ───────────────── */
const TASK_REVIEW_URL = @json(route('faculty.tasks.review', ['task' => '__ID__']));
const TASK_FEEDBACK_URL = @json(route('faculty.tasks.feedback', ['task' => '__ID__']));
let reviewTaskId = null;
// The two sides of the Before/After comparison for the open task.
let reviewPreviewUrls = { before: null, after: null };
// The Changes list for the open task — rendered into #reviewChangesPane.
let reviewChanges = [];

const REVIEW_CHANGE_STYLES = {
    added: { border: 'border-emerald-400', chip: 'bg-emerald-50 text-emerald-700 border-emerald-200', label: 'Added' },
    modified: { border: 'border-amber-400', chip: 'bg-amber-50 text-amber-700 border-amber-200', label: 'Changed' },
    removed: { border: 'border-rose-400', chip: 'bg-rose-50 text-rose-700 border-rose-200', label: 'Removed' },
};

/* One row per change, grouped by page. Clicking a row jumps the After preview
   to that element and pulses it — see the postMessage listener below and the
   'hms-diff-focus' handler in hms-review-highlight.js. */
function renderReviewChanges(changes) {
    const pane = document.getElementById('reviewChangesPane');
    if (!changes.length) {
        pane.innerHTML = '<p class="text-xs text-slate-400 text-center py-8">No changes to show for this submission.</p>';
        return;
    }

    const byPage = {};
    changes.forEach(function (c) {
        const page = c.page || 'home';
        (byPage[page] = byPage[page] || []).push(c);
    });

    let html = '';
    Object.keys(byPage).forEach(function (page) {
        html += '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-3 mb-1.5 first:mt-0">' + escHtml(page) + '</p>';
        byPage[page].forEach(function (c) {
            const style = REVIEW_CHANGE_STYLES[c.type] || REVIEW_CHANGE_STYLES.modified;
            const clickableAttr = c.key ? ' data-review-change-key="' + escHtml(c.key) + '"' : '';
            const clickableClass = c.key ? ' cursor-pointer hover:bg-slate-50' : '';
            html += '<div class="border-l-2 ' + style.border + ' rounded-r-lg bg-white px-2.5 py-2 mb-1.5' + clickableClass + '"' + clickableAttr + '>'
                + '<div class="flex items-center justify-between gap-2">'
                + '<span class="text-xs font-semibold text-slate-700 truncate">' + escHtml(c.label || '') + '</span>'
                + '<span class="shrink-0 px-1.5 py-0.5 rounded-full border text-[9px] font-bold ' + style.chip + '">' + style.label + '</span>'
                + '</div>';
            (c.fields || []).forEach(function (f) {
                html += '<p class="text-[11px] text-slate-500 mt-1"><span class="font-semibold text-slate-600">' + escHtml(f.label) + ':</span> ';
                if (f.from != null && f.to != null) {
                    html += '<span class="line-through text-slate-400">' + escHtml(f.from) + '</span> <span class="text-slate-400">to</span> <span class="text-slate-700">' + escHtml(f.to) + '</span>';
                } else if (f.to != null) {
                    html += '<span class="text-slate-700">' + escHtml(f.to) + '</span>';
                } else if (f.from != null) {
                    html += '<span class="line-through text-slate-400">' + escHtml(f.from) + '</span>';
                }
                html += '</p>';
            });
            html += '</div>';
        });
    });
    pane.innerHTML = html;
}

document.addEventListener('click', function (e) {
    const row = e.target.closest ? e.target.closest('[data-review-change-key]') : null;
    if (!row) return;
    const key = row.getAttribute('data-review-change-key');
    setReviewCompareSide('after');
    const frame = document.getElementById('reviewPreviewFrame');
    try {
        frame.contentWindow.postMessage({ type: 'hms-diff-focus', key: key }, '*');
    } catch (err) { /* ignore */ }
});

/* The feedback box belongs to Revise alone, so it is revealed only on demand. */
function showReviseStep() {
    document.getElementById('reviewChoiceStep').classList.add('hidden');
    document.getElementById('reviewReviseStep').classList.remove('hidden');
    document.getElementById('reviewError').classList.add('hidden');
    document.getElementById('reviewFeedback').focus();
}

function hideReviseStep() {
    document.getElementById('reviewChoiceStep').classList.remove('hidden');
    document.getElementById('reviewReviseStep').classList.add('hidden');
    document.getElementById('reviewFeedback').value = '';
    document.getElementById('reviewError').classList.add('hidden');
}

/* Before/After share one iframe: a full hotel site needs the width. Changes
   swaps to the list pane instead — there is nothing to render for it. */
function setReviewCompareSide(side) {
    document.querySelectorAll('#reviewCompareToggle [data-compare]').forEach(function (btn) {
        const on = btn.getAttribute('data-compare') === side;
        btn.classList.toggle('bg-white', on);
        btn.classList.toggle('text-slate-800', on);
        btn.classList.toggle('shadow-sm', on);
        btn.classList.toggle('text-slate-500', !on);
    });

    const frame = document.getElementById('reviewPreviewFrame');
    const changesPane = document.getElementById('reviewChangesPane');

    if (side === 'changes') {
        frame.classList.add('hidden');
        changesPane.classList.remove('hidden');
        document.getElementById('reviewWorkLabel').textContent = 'Highlighted changes';
        return;
    }

    const url = reviewPreviewUrls[side];
    if (!url) return;
    changesPane.classList.add('hidden');
    frame.classList.remove('hidden');
    frame.src = url;
    document.getElementById('reviewOpenTab').href = url;
    document.getElementById('reviewWorkLabel').textContent = side === 'before'
        ? 'Before — when this task was assigned'
        : 'After — what they submitted';
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest ? e.target.closest('#reviewCompareToggle [data-compare]') : null;
    if (!btn) return;
    setReviewCompareSide(btn.getAttribute('data-compare'));
});

// Rows are rebuilt every time the modal opens, so delegate.
document.addEventListener('click', function (e) {
    const row = e.target.closest ? e.target.closest('[data-review-task]') : null;
    if (!row) return;
    openTaskReview(row.getAttribute('data-review-task'));
});

function openTaskReview(taskId) {
    reviewTaskId = taskId;
    const modal = document.getElementById('taskReviewModal');
    if (!modal) return;

    document.getElementById('reviewTaskTitle').textContent = 'Loading…';
    document.getElementById('reviewTaskMeta').textContent = '';
    document.getElementById('reviewStatusRow').innerHTML = '';
    document.getElementById('reviewFeedback').value = '';
    document.getElementById('reviewError').classList.add('hidden');
    document.getElementById('reviewPrevFeedbackWrap').classList.add('hidden');

    const frame = document.getElementById('reviewPreviewFrame');
    const empty = document.getElementById('reviewPreviewEmpty');
    const openTab = document.getElementById('reviewOpenTab');
    const conceptPane = document.getElementById('reviewConceptPane');
    const changesPane = document.getElementById('reviewChangesPane');
    frame.classList.add('hidden');
    frame.src = '';
    empty.classList.remove('hidden');
    openTab.classList.add('hidden');
    conceptPane.classList.add('hidden');
    conceptPane.innerHTML = '';
    changesPane.classList.add('hidden');
    changesPane.innerHTML = '';
    document.getElementById('reviewChangesCount').textContent = '';
    document.getElementById('reviewWorkLabel').textContent = "The team's live site";
    document.getElementById('reviewDecisionBlock').classList.remove('hidden');
    document.getElementById('reviewConceptHint').classList.add('hidden');
    hideReviseStep();
    reviewPreviewUrls = { before: null, after: null };
    reviewChanges = [];
    document.getElementById('reviewCompareToggle').classList.add('hidden');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    fetch(TASK_REVIEW_URL.replace('__ID__', encodeURIComponent(taskId)), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then((res) => res.json().then((d) => { if (!res.ok) throw new Error(d.error || 'Could not load this submission.'); return d; }))
        .then((d) => {
            document.getElementById('reviewTaskTitle').textContent = d.title || 'Submission';
            document.getElementById('reviewTaskMeta').textContent = [
                d.student_name, d.group_name, d.role_label
            ].filter(Boolean).join(' · ');

            const badges = [];
            if (d.status === 'archived') {
                badges.push('<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold">Submitted' + (d.submitted_at ? ' · ' + escHtml(d.submitted_at) : '') + '</span>');
            } else {
                badges.push('<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold">Not submitted</span>');
            }
            if (d.revision_count > 0) {
                badges.push('<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-bold">Revision ' + d.revision_count + '</span>');
            }
            document.getElementById('reviewStatusRow').innerHTML = badges.join(' ');

            if (d.feedback) {
                document.getElementById('reviewPrevFeedback').textContent = d.feedback;
                document.getElementById('reviewPrevFeedbackMeta').textContent =
                    [d.feedback_by, d.feedback_at].filter(Boolean).join(' · ');
                document.getElementById('reviewPrevFeedbackWrap').classList.remove('hidden');
            }

            if (d.is_hotel_concept) {
                // Concepts are text, so they are read here rather than previewed, and
                // each carries its own verdict controls — the shared ones would only
                // let one decision cover both.
                document.getElementById('reviewWorkLabel').textContent = 'The two proposed hotel concepts';
                document.getElementById('reviewDecisionBlock').classList.add('hidden');
                document.getElementById('reviewConceptHint').classList.remove('hidden');
                paintReviewConcepts(d);
                conceptPane.classList.remove('hidden');
                empty.classList.add('hidden');
                return;
            }

            if (d.preview_url) {
                frame.src = d.preview_url;
                frame.classList.remove('hidden');
                empty.classList.add('hidden');
                openTab.href = d.preview_url;
                openTab.classList.remove('hidden');

                reviewPreviewUrls = { before: d.before_preview_url || null, after: d.preview_url };
                reviewChanges = Array.isArray(d.changes) ? d.changes : [];
                renderReviewChanges(reviewChanges);
                document.getElementById('reviewChangesCount').textContent = reviewChanges.length ? '(' + reviewChanges.length + ')' : '';

                if (d.before_preview_url) {
                    document.getElementById('reviewCompareToggle').classList.remove('hidden');
                    setReviewCompareSide('after');
                }
            }

            // Only a submitted task can be acted on.
            const canAct = d.status === 'archived';
            document.getElementById('reviewApproveBtn').disabled = !canAct;
            document.getElementById('reviewReviseBtn').disabled = !canAct;
            [document.getElementById('reviewApproveBtn'), document.getElementById('reviewReviseBtn')]
                .forEach((b) => b.classList.toggle('opacity-40', !canAct));
        })
        .catch((err) => {
            document.getElementById('reviewTaskTitle').textContent = 'Could not load';
            showReviewError(err.message || 'Could not load this submission.');
        });
}

function closeTaskReview() {
    const modal = document.getElementById('taskReviewModal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.getElementById('reviewPreviewFrame').src = '';
    reviewTaskId = null;
    document.body.style.overflow = 'hidden'; // the team modal underneath is still open
}

function showReviewError(msg) {
    const el = document.getElementById('reviewError');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function submitTaskFeedback(decision) {
    if (!reviewTaskId) return;
    const feedback = document.getElementById('reviewFeedback').value.trim();
    if (decision === 'revise' && !feedback) {
        showReviewError('Tell the student what to change before sending it back.');
        return;
    }
    document.getElementById('reviewError').classList.add('hidden');

    const buttons = [
        document.getElementById('reviewApproveBtn'),
        document.getElementById('reviewReviseBtn'),
        document.getElementById('reviewSendFeedbackBtn'),
    ].filter(Boolean);
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback({ decision: decision, feedback: feedback }, decision, 'Task approved')
        .catch(() => { buttons.forEach((b) => { b.disabled = false; }); });
}

/* Draw both concept cards into the review pane, each with its own verdict
   controls. Rebuilt from the payload after every verdict, so approving one concept
   leaves the other's buttons exactly where they were. */
function paintReviewConcepts(data) {
    const pane = document.getElementById('reviewConceptPane');
    if (!pane) return;

    pane.innerHTML = '<div class="space-y-3">'
        + (data.slots || []).map(renderReviewConceptCard).join('')
        + '</div>';
}

function renderReviewConceptCard(entry) {
    const concept = entry.concept;
    const slot = Number(entry.slot);

    if (!concept) {
        return '<div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-5 text-center">'
            + '<p class="text-xs font-bold text-slate-400">' + escHtml(entry.slot_label) + ' has not been proposed yet.</p>'
            + '</div>';
    }

    // Both concepts have to exist and nobody chosen yet — the same rule the Team
    // Details tab uses, so both surfaces show the same buttons at the same time.
    const awaiting = entry.can_review === true;

    const controls = awaiting
        ? '<div class="mt-2.5 pt-2.5 border-t border-slate-200">'
            + '<label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="reviewFeedback' + slot + '">'
                + 'Feedback on ' + escHtml(entry.slot_label) + '</label>'
            + '<textarea id="reviewFeedback' + slot + '" rows="3" maxlength="2000"'
                + ' placeholder="What works here? What should change?"'
                + ' class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs resize-none focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"></textarea>'
            + '<p class="text-[10px] text-slate-400 mt-1">Required when sending back for revision.</p>'
            + '<div class="flex flex-wrap gap-2 mt-2">'
                + '<button type="button" data-concept-action="' + slot + '" onclick="submitConceptFeedback(' + slot + ', \'approve\')"'
                    + ' class="flex-1 min-w-[9rem] px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:opacity-90 transition inline-flex items-center justify-center gap-1.5">'
                    + '<span class="iconify text-sm" data-icon="mdi:check-circle-outline"></span> Approve ' + escHtml(entry.slot_label)
                + '</button>'
                + '<button type="button" data-concept-action="' + slot + '" onclick="submitConceptFeedback(' + slot + ', \'revise\')"'
                    + ' class="flex-1 min-w-[9rem] px-3 py-2 rounded-xl bg-white text-amber-700 border border-amber-300 text-xs font-bold hover:bg-amber-50 transition inline-flex items-center justify-center gap-1.5">'
                    + '<span class="iconify text-sm" data-icon="mdi:undo-variant"></span> Send back'
                + '</button>'
            + '</div>'
          + '</div>'
        : '<p class="mt-2.5 pt-2.5 border-t border-slate-200 text-[11px] font-semibold text-slate-400">'
            + escHtml(concept.status === 'approved'
                ? 'Official hotel concept.'
                : 'Waiting for the other concept to be proposed before you can review either one.')
          + '</p>';

    return '<div class="rounded-xl border border-slate-200 bg-white p-3">'
        + '<p class="text-[9px] font-bold uppercase tracking-[0.15em] text-rose-500">' + escHtml(entry.slot_label) + '</p>'
        + renderTeamHotelConcept({ concept: concept, history: entry.history, embedded: true })
        + controls
        + '</div>';
}

/* The verdict on one concept. The other one is untouched, so the dialog is redrawn
   from the response instead of reloading the page. */
function submitConceptFeedback(slot, decision) {
    if (!reviewTaskId) return;

    const box = document.getElementById('reviewFeedback' + slot);
    const feedback = box ? box.value.trim() : '';
    if (decision === 'revise' && !feedback) {
        showReviewError('Tell the team what to change before sending it back.');
        return;
    }
    document.getElementById('reviewError').classList.add('hidden');

    const buttons = Array.from(document.querySelectorAll('[data-concept-action="' + slot + '"]'));
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback(
        { decision: decision, feedback: feedback, slot: slot },
        decision,
        'Concept approved',
        // Redraw in place: the second concept may still need a verdict, and a page
        // reload would close the dialog the faculty is working in.
        (d) => { if (Array.isArray(d.slots)) paintReviewConcepts(d); }
    ).catch(() => buttons.forEach((b) => { b.disabled = false; }));
}

/* Shared POST for every verdict path — the review dialog's task and concept
   verdicts, and the Team Details tab's concept verdicts. Resolves on success,
   rejects on failure so the caller can put its own buttons back. taskId and
   onError default to the review dialog's globals so its two callers need no
   change; the Team Details tab passes its own task id and error box instead. */
function postTaskFeedback(body, decision, approvedTitle, onSuccess, taskId, onError) {
    taskId = taskId || reviewTaskId;
    onError = onError || showReviewError;

    return fetch(TASK_FEEDBACK_URL.replace('__ID__', encodeURIComponent(taskId)), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
        },
        body: JSON.stringify(body)
    })
        .then((res) => res.json().then((d) => {
            if (!res.ok) {
                // 422 carries our own message; Laravel's validation bag carries errors.
                const first = d.errors ? Object.values(d.errors)[0][0] : null;
                throw new Error(d.error || first || d.message || 'Could not save feedback.');
            }
            return d;
        }))
        .then((d) => {
            if (!onSuccess) closeTaskReview();

            Swal.fire({
                icon: 'success',
                title: decision === 'revise' ? 'Sent back for revision' : approvedTitle,
                html: `<p class="text-sm text-slate-500">${d.message || 'The student has been notified.'}</p>`,
                timer: 2200,
                showConfirmButton: false,
                iconColor: decision === 'revise' ? '#D97706' : '#059669',
                customClass: { popup: 'rounded-2xl p-6 bg-white shadow-2xl', title: 'text-lg font-bold text-slate-800' },
                buttonsStyling: false,
            }).then(() => {
                if (onSuccess) {
                    onSuccess(d);
                } else {
                    window.location.reload();
                }
            });
        })
        .catch((err) => {
            onError(err.message || 'Could not save feedback.');
            throw err;
        });
}

/* Team Details modal: Members & Roles / Team Task Activity */
function switchTeamModalTab(tabId) {
    const tabs = ['members', 'tasks', 'concept'];
    const current = tabs.includes(tabId) ? tabId : 'members';
    const active = ['border-rose-500', 'text-rose-600'];
    const idle = ['border-transparent', 'text-slate-400'];

    tabs.forEach(function (tab) {
        document.getElementById('team-panel-' + tab)?.classList.toggle('hidden', tab !== current);

        const btn = document.getElementById('team-tab-' + tab);
        if (!btn) return;
        const isOn = tab === current;
        btn.classList.toggle('border-rose-500', isOn);
        btn.classList.toggle('text-rose-600', isOn);
        btn.classList.toggle('border-transparent', !isOn);
        btn.classList.toggle('text-slate-400', !isOn);
    });
}

/* Front Desk's hotel concept for the open team — the same rows the students read,
   fetched by group name so this giant page does not carry every team's history. */
const TEAM_CONCEPT_URL = @json(route('faculty.teams.hotel-concept', ['groupName' => '__GROUP__']));

// The concept task backing whichever team's tab is open — the Approve / Send back
// buttons here post through the same task-feedback route the review dialog uses.
let teamConceptTaskId = null;

function loadTeamHotelConcept(groupName) {
    const body = document.getElementById('teamModalConceptBody');
    if (!body) return;

    document.getElementById('teamConceptError')?.classList.add('hidden');
    body.innerHTML = '<div class="px-3 py-6 text-center text-xs text-slate-400">Loading hotel concept…</div>';

    fetch(TEAM_CONCEPT_URL.replace('__GROUP__', encodeURIComponent(groupName || '')), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.json().then(data => {
            if (!res.ok) throw new Error(data.error || 'Could not load the hotel concept.');
            return data;
        }))
        .then(data => {
            teamConceptTaskId = data.task_id || null;
            body.innerHTML = renderTeamConceptSlots(data);
        })
        .catch(err => {
            body.innerHTML = '<div class="px-3 py-6 text-center text-xs text-rose-500 font-semibold">'
                + escHtml(err.message || 'Could not load the hotel concept.') + '</div>';
        });
}

function showTeamConceptError(msg) {
    const el = document.getElementById('teamConceptError');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('hidden');
}

/* Both of a team's concepts for the Team Details modal — each with its own verdict
   controls when faculty can still choose, or a note on why not. One self-contained
   card per concept, no edit history: this tab is for deciding, not auditing —
   the trail of who-changed-what stays in the review dialog. */
function renderTeamConceptSlots(data) {
    const rows = (data.slots || []).map((entry) => renderTeamConceptCard(entry, data)).join('')
        || '<div class="px-3 py-6 text-center text-xs text-slate-400">This team has no hotel concepts yet.</div>';

    if (!data.all_slots_filled) {
        return rows + '<p class="text-xs font-semibold text-slate-400 text-center">'
            + 'Waiting for the team to propose both concepts before you can choose one.</p>';
    }

    return rows;
}

/* One concept, as a single card: slot + status up top, the content in the
   middle, the decision (or its outcome) as a footer — everything about this
   concept in one place instead of stacked separate blocks. */
function renderTeamConceptCard(entry, data) {
    const concept = entry.concept;

    if (!concept) {
        return '<div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3.5 py-6 text-center mb-3">'
            + '<p class="text-[9px] font-bold uppercase tracking-[0.15em] text-slate-400 mb-1">' + escHtml(entry.slot_label) + '</p>'
            + '<p class="text-xs font-bold text-slate-400">Not proposed yet.</p>'
        + '</div>';
    }

    const status = concept.status || 'draft';
    const statusBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold '
        + (CONCEPT_STATUS_CLASSES[status] || CONCEPT_STATUS_CLASSES.draft) + '">' + escHtml(concept.status_label) + '</span>';

    // Who touched it last and when, as one line instead of a stack of paragraphs.
    const metaParts = [];
    if (concept.updated_by || concept.updated_at) {
        metaParts.push('Updated'
            + (concept.updated_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.updated_by) + '</span>' : '')
            + (concept.updated_at ? ' on ' + escHtml(concept.updated_at) : ''));
    }
    if (concept.submitted_at) {
        metaParts.push('Submitted'
            + (concept.submitted_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.submitted_by) + '</span>' : '')
            + ' on ' + escHtml(concept.submitted_at));
    }
    if (concept.reviewed_at) {
        metaParts.push('Reviewed'
            + (concept.reviewed_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.reviewed_by) + '</span>' : '')
            + ' on ' + escHtml(concept.reviewed_at));
    }
    const meta = metaParts.length
        ? '<p class="text-[10px] text-slate-400 mt-2.5 leading-relaxed">' + metaParts.join(' <span class="text-slate-300">·</span> ') + '</p>'
        : '';

    const feedback = concept.faculty_feedback
        ? '<div class="mt-2.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-2">'
            + '<p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Your feedback</p>'
            + '<p class="text-[11px] text-amber-800 mt-0.5 whitespace-pre-line">' + escHtml(concept.faculty_feedback) + '</p>'
          + '</div>'
        : '';

    return '<div class="rounded-xl border border-slate-200 bg-white overflow-hidden mb-3">'
        + '<div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-2 flex-wrap">'
            + '<p class="text-[9px] font-bold uppercase tracking-[0.15em] text-rose-500">' + escHtml(entry.slot_label) + '</p>'
            + '<div class="flex items-center gap-1.5 flex-wrap">' + statusBadge
                + '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">'
                    + escHtml(concept.hotel_type_label) + '</span>'
            + '</div>'
        + '</div>'
        + '<div class="p-3.5">'
            + '<p class="text-sm font-bold text-slate-800">' + escHtml(concept.title) + '</p>'
            + '<p class="text-xs text-slate-600 mt-1.5 leading-relaxed whitespace-pre-line">' + escHtml(concept.description) + '</p>'
            + meta
            + feedback
            + renderTeamConceptControls(entry, data)
        + '</div>'
    + '</div>';
}

/* Approve / Send back for one concept, or the reason those are not shown — decided
   already, or this concept simply is not the one that lost.
   Approve fires immediately — there is nothing more to say. Send back needs
   feedback first, so that field only appears once the faculty asks for it, kept
   in a hidden panel rather than shown up front. */
function renderTeamConceptControls(entry, data) {
    if (entry.can_review) {
        const slot = Number(entry.slot);
        return '<div class="mt-3 pt-3 border-t border-slate-200">'
            + '<div class="flex flex-wrap gap-2" id="teamConceptButtons' + slot + '">'
                + '<button type="button" data-team-concept-action="' + slot + '" onclick="submitTeamConceptFeedback(' + slot + ', \'approve\')"'
                    + ' class="flex-1 min-w-[9rem] px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:opacity-90 transition inline-flex items-center justify-center gap-1.5">'
                    + '<span class="iconify text-sm" data-icon="mdi:check-circle-outline"></span> Approve ' + escHtml(entry.slot_label)
                + '</button>'
                + '<button type="button" data-team-concept-action="' + slot + '" onclick="showTeamConceptRevisionForm(' + slot + ')"'
                    + ' class="btn-sendback flex-1 min-w-[9rem] px-3 py-2 rounded-xl text-xs font-bold transition inline-flex items-center justify-center gap-1.5">'
                    + '<span class="iconify text-sm" data-icon="mdi:undo-variant"></span> Send back'
                + '</button>'
            + '</div>'
            + '<div id="teamConceptRevisionForm' + slot + '" class="hidden mt-3 pt-3 border-t border-dashed border-slate-200">'
                + '<label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5" for="teamConceptFeedback' + slot + '">'
                    + 'Feedback on ' + escHtml(entry.slot_label) + '</label>'
                + '<textarea id="teamConceptFeedback' + slot + '" rows="3" maxlength="2000"'
                    + ' placeholder="What works here? What should change?"'
                    + ' class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs resize-none focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"></textarea>'
                + '<p class="text-[10px] text-slate-400 mt-1">Required before sending back.</p>'
                + '<div class="flex flex-wrap gap-2 mt-2">'
                    + '<button type="button" data-team-concept-action="' + slot + '" onclick="submitTeamConceptFeedback(' + slot + ', \'revise\')"'
                        + ' class="btn-sendback flex-1 min-w-[9rem] px-3 py-2 rounded-xl text-xs font-bold transition inline-flex items-center justify-center gap-1.5">'
                        + '<span class="iconify text-sm" data-icon="mdi:undo-variant"></span> Confirm send back'
                    + '</button>'
                    + '<button type="button" onclick="hideTeamConceptRevisionForm(' + slot + ')"'
                        + ' class="flex-1 min-w-[9rem] px-3 py-2 rounded-xl bg-white text-slate-500 border border-slate-200 text-xs font-bold hover:bg-slate-50 transition">'
                        + 'Cancel'
                    + '</button>'
                + '</div>'
            + '</div>'
        + '</div>';
    }

    // Decided, and this entry made it into the payload at all — the losing
    // concept never does once a decision exists, so this is always the winner.
    if (data.decided) {
        return '<div class="mt-3 pt-3 border-t border-slate-200">'
            + '<p class="text-[11px] font-bold text-emerald-600 inline-flex items-center gap-1">'
                + '<span class="iconify text-sm" data-icon="mdi:check-decagram"></span> Official hotel concept</p>'
        + '</div>';
    }

    return '';
}

/* Reveal the feedback field only once the faculty has actually chosen to send
   the concept back — Approve stays a single click with nothing to fill in. */
function showTeamConceptRevisionForm(slot) {
    document.getElementById('teamConceptButtons' + slot)?.classList.add('hidden');
    const form = document.getElementById('teamConceptRevisionForm' + slot);
    form?.classList.remove('hidden');
    document.getElementById('teamConceptFeedback' + slot)?.focus();
}

function hideTeamConceptRevisionForm(slot) {
    document.getElementById('teamConceptRevisionForm' + slot)?.classList.add('hidden');
    document.getElementById('teamConceptButtons' + slot)?.classList.remove('hidden');
}

/* The verdict on one concept, from the Team Details tab. Mirrors
   submitConceptFeedback below, but targets this team's task id and this tab's
   error box instead of the review dialog's globals. */
function submitTeamConceptFeedback(slot, decision) {
    if (!teamConceptTaskId) return;

    const box = document.getElementById('teamConceptFeedback' + slot);
    const feedback = box ? box.value.trim() : '';
    if (decision === 'revise' && !feedback) {
        showTeamConceptError('Tell the team what to change before sending it back.');
        return;
    }
    document.getElementById('teamConceptError')?.classList.add('hidden');

    const buttons = Array.from(document.querySelectorAll('[data-team-concept-action="' + slot + '"]'));
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback(
        { decision: decision, feedback: feedback, slot: slot },
        decision,
        'Concept approved',
        (d) => { document.getElementById('teamModalConceptBody').innerHTML = renderTeamConceptSlots(d); },
        teamConceptTaskId,
        showTeamConceptError
    ).catch(() => buttons.forEach((b) => { b.disabled = false; }));
}

/* Where a concept stands in the workflow. Same colours the students see. */
const CONCEPT_STATUS_CLASSES = {
    draft: 'bg-slate-100 text-slate-600 border-slate-200',
    submitted: 'bg-amber-50 text-amber-700 border-amber-200',
    needs_revision: 'bg-rose-50 text-rose-700 border-rose-200',
    approved: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    not_selected: 'bg-slate-100 text-slate-500 border-slate-300',
};

/* Renders ONE concept and its history. Shared by the Team Details modal and the
   review dialog — a submitted concept is read, not previewed, so the review pane
   needs exactly this. Callers pass a single slot's { concept, history }; pass
   embedded: true to drop the outer card when it already sits inside one. */
function renderTeamHotelConcept(data) {
    const concept = data.concept;
    const history = Array.isArray(data.history) ? data.history : [];
    const status = concept ? (concept.status || 'draft') : 'draft';

    const statusBadge = concept
        ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-bold '
            + (CONCEPT_STATUS_CLASSES[status] || CONCEPT_STATUS_CLASSES.draft) + '">'
            + escHtml(concept.status_label) + '</span>'
        : '';

    // Who handed it in, and what the last verdict on it was.
    const trail = concept
        ? (concept.submitted_at
                ? '<p class="text-[10px] text-slate-400 mt-1">Submitted'
                    + (concept.submitted_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.submitted_by) + '</span>' : '')
                    + ' on ' + escHtml(concept.submitted_at) + '</p>'
                : '')
            + (concept.reviewed_at
                ? '<p class="text-[10px] text-slate-400">Reviewed'
                    + (concept.reviewed_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.reviewed_by) + '</span>' : '')
                    + ' on ' + escHtml(concept.reviewed_at) + '</p>'
                : '')
            + (concept.faculty_feedback
                ? '<div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-2">'
                    + '<p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Your feedback</p>'
                    + '<p class="text-[11px] text-amber-800 mt-0.5 whitespace-pre-line">' + escHtml(concept.faculty_feedback) + '</p>'
                  + '</div>'
                : '')
        : '';

    // Inside the review dialog this already sits in a card, so the frame comes off.
    const frame = data.embedded ? '' : 'rounded-lg border border-slate-200 bg-slate-50/70 p-3';

    const conceptBlock = concept
        ? '<div class="' + frame + '">' +
            '<div class="flex items-start justify-between gap-2 flex-wrap">' +
                '<p class="text-sm font-bold text-slate-800">' + escHtml(concept.title) + '</p>' +
                '<div class="flex items-center gap-1.5 flex-wrap">' + statusBadge +
                    '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">'
                        + escHtml(concept.hotel_type_label) + '</span>' +
                '</div>' +
            '</div>' +
            '<p class="text-xs text-slate-600 mt-2 whitespace-pre-line">' + escHtml(concept.description) + '</p>' +
            '<p class="text-[10px] text-slate-400 mt-2">Last updated'
                + (concept.updated_by ? ' by <span class="font-semibold text-slate-500">' + escHtml(concept.updated_by) + '</span>' : '')
                + (concept.updated_at ? ' on ' + escHtml(concept.updated_at) : '') + '</p>' +
            trail +
          '</div>'
        : '<div class="rounded-lg border border-dashed border-slate-200 px-3 py-6 text-center">' +
            '<p class="text-xs font-bold text-slate-400">Not proposed yet.</p>' +
          '</div>';

    const historyRows = history.length
        ? history.map(function (entry) {
            const changes = (entry.changes || []).map(function (change) {
                return '<li class="text-[11px] text-slate-500">' +
                    '<span class="font-semibold text-slate-600">' + escHtml(change.label) + ':</span> ' +
                    '<span class="line-through text-slate-400">' + (escHtml(change.from) || '—') + '</span> ' +
                    '<span class="text-slate-400">to</span> ' +
                    '<span class="text-slate-700">' + escHtml(change.to) + '</span>' +
                '</li>';
            }).join('');

            return '<div class="px-3 py-2.5">' +
                '<div class="flex items-start justify-between gap-2 flex-wrap">' +
                    '<p class="text-xs font-bold text-slate-700">' + escHtml(entry.editor) +
                        ' <span class="font-semibold text-slate-400">— ' + escHtml(entry.action_label) + '</span></p>' +
                    '<span class="text-[10px] text-slate-400">' + escHtml(entry.created_at) + ' · ' + escHtml(entry.created_at_human) + '</span>' +
                '</div>' +
                (changes
                    ? '<ul class="mt-1.5 space-y-1">' + changes + '</ul>'
                    : '<p class="mt-1.5 text-[11px] text-slate-500"><span class="font-semibold text-slate-600">'
                        + escHtml(entry.title) + '</span> · ' + escHtml(entry.hotel_type_label) + '</p>') +
            '</div>';
        }).join('')
        : '<div class="px-3 py-6 text-center text-xs text-slate-400">No edits recorded yet.</div>';

    return conceptBlock +
        '<div>' +
            '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Edit History</p>' +
            '<div class="border border-slate-200 rounded-lg divide-y divide-slate-100 max-h-72 overflow-y-auto">' + historyRows + '</div>' +
        '</div>';
}

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
        if (currentCreateTeamMode === 'multiple') {
            if (!buildBulkTeamsHiddenInputs()) {
                return;
            }
            document.getElementById('createTeamForm').submit();
            return;
        }

        const checked = document.querySelectorAll('.create-student-checkbox:checked').length;
        if (checked < 1) {
            alert('Select at least 1 member for the team.');
            return;
        }
        if (checked > TEAM_MEMBER_MAX) {
            alert('A team cannot have more than ' + TEAM_MEMBER_MAX + ' members.');
            return;
        }
        document.getElementById('createTeamFormSingle').submit();
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

// ── Bulk multi-team creation (exactly 4 members per team) ──
const BULK_TEAM_SIZE = 4;

function availableBulkStudentCount() {
    return document.querySelectorAll('.bulk-student-row').length;
}

function maxCompleteBulkTeams() {
    return Math.floor(availableBulkStudentCount() / BULK_TEAM_SIZE);
}

function rebuildBulkTeamSlots(forcedCount = null) {
    const countInput = document.getElementById('bulkTeamCount');
    const slots = document.getElementById('bulkTeamSlots');
    if (!countInput || !slots) return;

    const maxTeams = maxCompleteBulkTeams();
    let count = forcedCount != null ? parseInt(forcedCount, 10) : parseInt(countInput.value, 10);
    if (!Number.isFinite(count) || count < 0) count = 0;
    count = Math.min(count, maxTeams, 40);
    countInput.value = String(count);

    const previousNames = Array.from(document.querySelectorAll('.bulk-team-name-input'))
        .map((input) => input.value.trim());

    const names = [];
    for (let i = 0; i < count; i++) {
        names.push(previousNames[i] || `Team ${i + 1}`);
    }

    if (count === 0) {
        slots.innerHTML = `
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                Need at least ${BULK_TEAM_SIZE} unassigned students to create a team. Each team must have exactly ${BULK_TEAM_SIZE} members.
            </div>
        `;
        syncBulkTeamSelectOptions();
        refreshBulkTeamPreviews();
        return;
    }

    slots.innerHTML = names.map((name, index) => `
        <div class="rounded-xl border border-slate-200 bg-white p-4 bulk-team-slot" data-team-index="${index}">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Team ${index + 1} Name</label>
                    <input type="text" value="${escapeBulkAttr(name)}"
                        class="bulk-team-name-input w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                        oninput="syncBulkTeamSelectOptions()">
                </div>
                <div class="sm:w-40">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Members</p>
                    <p class="bulk-team-count h-10 px-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-600 flex items-center">0 / ${BULK_TEAM_SIZE}</p>
                </div>
            </div>
            <div class="bulk-team-preview mt-3 flex flex-wrap gap-1.5 text-[11px] text-slate-400">No students assigned yet.</div>
        </div>
    `).join('');

    syncBulkTeamSelectOptions();
    refreshBulkTeamPreviews();
}

function escapeBulkAttr(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function escapeBulkHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getBulkTeamNames() {
    return Array.from(document.querySelectorAll('.bulk-team-name-input'))
        .map((input, index) => {
            const value = input.value.trim();
            return value !== '' ? value : `Team ${index + 1}`;
        });
}

function syncBulkTeamSelectOptions() {
    const names = getBulkTeamNames();
    document.querySelectorAll('.bulk-team-select').forEach((select) => {
        const current = select.value;
        select.innerHTML = '<option value="">Unassigned</option>' + names.map((name, index) => {
            const selected = String(current) === String(index) ? ' selected' : '';
            return `<option value="${index}"${selected}>${escapeBulkHtml(name)}</option>`;
        }).join('');
        if (current !== '' && Number(current) >= names.length) {
            select.value = '';
        }
    });
    refreshBulkTeamPreviews();
}

function onBulkStudentTeamChange(selectEl) {
    const row = selectEl?.closest?.('.bulk-student-row');
    const teamIndex = selectEl?.value === '' ? -1 : parseInt(selectEl.value, 10);

    if (teamIndex >= 0) {
        const counts = getBulkTeamMemberCounts();
        // Exclude this select's previous contribution by temporarily clearing then recounting isn't needed —
        // value already changed. Count other rows assigned to this team.
        let others = 0;
        document.querySelectorAll('.bulk-student-row').forEach((r) => {
            if (r === row) return;
            const s = r.querySelector('.bulk-team-select');
            if (s && parseInt(s.value, 10) === teamIndex) others++;
        });
        if (others >= BULK_TEAM_SIZE) {
            alert(`Each team can only have ${BULK_TEAM_SIZE} members.`);
            selectEl.value = '';
        }
    }

    if (row) {
        const hasRole = row.querySelector('.bulk-role-checkbox:checked');
        if (!hasRole && selectEl.value !== '') {
            const assignedCount = Array.from(document.querySelectorAll('.bulk-team-select'))
                .filter((select) => select.value !== '').length;
            const role = TEAM_DEFAULT_ROLES[(assignedCount - 1 + TEAM_DEFAULT_ROLES.length) % TEAM_DEFAULT_ROLES.length];
            const roleCb = row.querySelector(`.bulk-role-checkbox[value="${role}"]`);
            if (roleCb) roleCb.checked = true;
        }
        if (selectEl.value === '') {
            row.querySelectorAll('.bulk-role-checkbox').forEach((cb) => { cb.checked = false; });
        }
    }
    refreshBulkTeamPreviews();
}

function getBulkTeamMemberCounts() {
    const names = getBulkTeamNames();
    const counts = names.map(() => 0);
    document.querySelectorAll('.bulk-team-select').forEach((select) => {
        if (select.value === '') return;
        const idx = parseInt(select.value, 10);
        if (Number.isFinite(idx) && counts[idx] != null) counts[idx]++;
    });
    return counts;
}

function refreshBulkTeamPreviews() {
    const names = getBulkTeamNames();
    const buckets = names.map(() => []);

    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        if (teamIndex < 0 || !buckets[teamIndex]) return;

        const roles = Array.from(row.querySelectorAll('.bulk-role-checkbox:checked')).map((cb) => cb.value);
        buckets[teamIndex].push({
            id: row.getAttribute('data-student-id'),
            name: row.getAttribute('data-student-name') || 'Student',
            roles,
        });
    });

    // A role may be held by at most one member of the SAME team (bucket index) — a
    // student assigned to a different team can freely reuse it.
    const usedRolesByTeam = buckets.map((members) => new Set(members.flatMap((m) => m.roles)));
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        const used = teamIndex >= 0 ? (usedRolesByTeam[teamIndex] || new Set()) : new Set();
        row.querySelectorAll('.bulk-role-checkbox').forEach((roleCb) => {
            roleCb.disabled = !roleCb.checked && used.has(roleCb.value);
        });
    });

    document.querySelectorAll('.bulk-team-slot').forEach((slot, index) => {
        const countEl = slot.querySelector('.bulk-team-count');
        const previewEl = slot.querySelector('.bulk-team-preview');
        const members = buckets[index] || [];
        const count = members.length;
        if (countEl) {
            countEl.textContent = `${count} / ${BULK_TEAM_SIZE}`;
            countEl.classList.toggle('text-green-700', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('border-green-200', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('bg-green-50', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('text-amber-700', count > 0 && count !== BULK_TEAM_SIZE);
            countEl.classList.toggle('border-amber-200', count > 0 && count !== BULK_TEAM_SIZE);
            countEl.classList.toggle('bg-amber-50', count > 0 && count !== BULK_TEAM_SIZE);
        }
        if (!previewEl) return;
        if (!members.length) {
            previewEl.innerHTML = '<span class="text-slate-400">No students assigned yet.</span>';
            return;
        }
        previewEl.innerHTML = members.map((member) => `
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-600">
                <span class="font-semibold">${escapeBulkHtml(member.name)}</span>
                ${member.roles.length ? `<span class="text-slate-400">· ${escapeBulkHtml(member.roles.length)} role${member.roles.length === 1 ? '' : 's'}</span>` : ''}
            </span>
        `).join('');
    });

    const hint = document.getElementById('bulkTeamHint');
    if (hint) {
        const totalStudents = availableBulkStudentCount();
        const assigned = Array.from(document.querySelectorAll('.bulk-team-select')).filter((s) => s.value !== '').length;
        const leftover = totalStudents - assigned;
        const completeTeams = (getBulkTeamMemberCounts() || []).filter((c) => c === BULK_TEAM_SIZE).length;
        hint.textContent = `${assigned} of ${totalStudents} students assigned · ${completeTeams} complete team${completeTeams === 1 ? '' : 's'} of ${BULK_TEAM_SIZE}`
            + (leftover > 0 ? ` · ${leftover} unassigned` : '')
            + '.';
    }

    // Disable full teams in dropdowns for unassigned students
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        if (!select) return;
        const current = select.value;
        const counts = getBulkTeamMemberCounts();
        Array.from(select.options).forEach((opt) => {
            if (opt.value === '') return;
            const idx = parseInt(opt.value, 10);
            const isCurrent = String(current) === String(idx);
            const full = (counts[idx] || 0) >= BULK_TEAM_SIZE && !isCurrent;
            opt.disabled = full;
            const base = opt.textContent.replace(/\s*\(full\)$/, '');
            opt.textContent = full ? `${base} (full)` : base;
        });
    });
}

function autoGroupStudentsIntoTeamsOfFour() {
    const rows = Array.from(document.querySelectorAll('.bulk-student-row'));
    const teamCount = maxCompleteBulkTeams();

    if (teamCount < 1) {
        alert(`You need at least ${BULK_TEAM_SIZE} unassigned students to create a team.`);
        rebuildBulkTeamSlots(0);
        return;
    }

    rebuildBulkTeamSlots(teamCount);

    // Clear all assignments first
    rows.forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        if (select) select.value = '';
        row.querySelectorAll('.bulk-role-checkbox').forEach((cb) => { cb.checked = false; });
    });

    const shuffled = [...rows].sort(() => Math.random() - 0.5);
    const toAssign = shuffled.slice(0, teamCount * BULK_TEAM_SIZE);

    toAssign.forEach((row, index) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = Math.floor(index / BULK_TEAM_SIZE);
        if (select) select.value = String(teamIndex);

        const role = TEAM_DEFAULT_ROLES[index % TEAM_DEFAULT_ROLES.length];
        const roleCb = row.querySelector(`.bulk-role-checkbox[value="${role}"]`);
        if (roleCb) roleCb.checked = true;
    });

    refreshBulkTeamPreviews();

    const leftover = rows.length - toAssign.length;
    if (leftover > 0) {
        const hint = document.getElementById('bulkTeamHint');
        if (hint) {
            hint.textContent = `Grouped ${toAssign.length} students into ${teamCount} team${teamCount === 1 ? '' : 's'} of ${BULK_TEAM_SIZE}. ${leftover} student${leftover === 1 ? '' : 's'} left unassigned (not enough for another full team).`;
        }
    }
}

function distributeAllStudentsToTeams() {
    autoGroupStudentsIntoTeamsOfFour();
}

function filterBulkStudentList() {
    const q = (document.getElementById('bulkStudentSearch')?.value || '').trim().toLowerCase();
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const hay = row.getAttribute('data-search') || '';
        row.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

function buildBulkTeamsHiddenInputs() {
    const container = document.getElementById('bulkTeamsHiddenInputs');
    if (!container) return false;
    container.innerHTML = '';

    const names = getBulkTeamNames();
    if (!names.length) {
        alert(`You need at least ${BULK_TEAM_SIZE} unassigned students to create a team.`);
        return false;
    }

    const uniqueNames = names.map((n) => n.trim().toLowerCase());
    if (new Set(uniqueNames).size !== uniqueNames.length) {
        alert('Each team needs a unique name.');
        return false;
    }

    const buckets = names.map(() => ({ members: [], roles: {} }));
    let assignedCount = 0;

    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        if (teamIndex < 0 || !buckets[teamIndex]) return;

        const studentId = row.getAttribute('data-student-id');
        if (!studentId) return;

        let roles = Array.from(row.querySelectorAll('.bulk-role-checkbox:checked')).map((cb) => cb.value);
        if (!roles.length) {
            roles = [TEAM_DEFAULT_ROLES[assignedCount % TEAM_DEFAULT_ROLES.length]];
        }

        buckets[teamIndex].members.push(studentId);
        buckets[teamIndex].roles[studentId] = roles;
        assignedCount++;
    });

    for (let i = 0; i < buckets.length; i++) {
        const count = buckets[i].members.length;
        if (count !== BULK_TEAM_SIZE) {
            alert(`"${names[i]}" must have exactly ${BULK_TEAM_SIZE} members (currently ${count}). Use Auto-Group (4 each).`);
            return false;
        }
    }

    if (assignedCount === 0) {
        alert('Assign students before saving.');
        return false;
    }

    names.forEach((name, index) => {
        const bucket = buckets[index];
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = `teams[${index}][group_name]`;
        nameInput.value = name;
        container.appendChild(nameInput);

        bucket.members.forEach((studentId) => {
            const memberInput = document.createElement('input');
            memberInput.type = 'hidden';
            memberInput.name = `teams[${index}][members][]`;
            memberInput.value = studentId;
            container.appendChild(memberInput);

            (bucket.roles[studentId] || []).forEach((role) => {
                const roleInput = document.createElement('input');
                roleInput.type = 'hidden';
                roleInput.name = `teams[${index}][member_roles][${studentId}][]`;
                roleInput.value = role;
                container.appendChild(roleInput);
            });
        });
    });

    return true;
}

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
    const used = new Set();
    if (mode === 'insert') {
        const select = document.querySelector('#insertStudentForm select[name="group_name"]');
        const existing = (window.EXISTING_TEAM_ROLES || {})[select ? select.value : ''] || [];
        existing.forEach(r => used.add(r));
    }
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

// A role may be held by at most one member. Scans whichever list is active (create,
// insert, or update) and disables any role checkbox already checked on another member
// of the same team, so the faculty sees it grey out instead of discovering the clash
// on submit. Checked boxes stay enabled so their own owner can still uncheck them.
function refreshRoleAvailability(mode) {
    const config = {
        create: { checkbox: '.create-student-checkbox', card: '.create-student-card' },
        insert: { checkbox: '.insert-student-checkbox', card: '.insert-student-card' },
        update: { checkbox: '.update-student-checkbox', card: '.update-student-row' },
    }[mode];
    if (!config) return;

    const used = new Set();

    if (mode === 'insert') {
        const select = document.querySelector('#insertStudentForm select[name="group_name"]');
        const existing = (window.EXISTING_TEAM_ROLES || {})[select ? select.value : ''] || [];
        existing.forEach(r => used.add(r));
    }

    document.querySelectorAll(config.checkbox + ':checked').forEach(memberCb => {
        const card = memberCb.closest(config.card);
        if (!card) return;
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked').forEach(r => used.add(r.value));
    });

    document.querySelectorAll(config.card).forEach(card => {
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]').forEach(roleCb => {
            roleCb.disabled = !roleCb.checked && used.has(roleCb.value);
        });
    });

    // Every member tick and every role tick on the create list reaches here, so the
    // summary is refreshed from one place rather than from each handler.
    if (mode === 'create') {
        renderCreateTeamSummary();
    }
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
    refreshRoleAvailability(mode);
}

function onSingleTeamMemberToggle(checkbox) {
    if (checkbox.checked) {
        const checked = document.querySelectorAll('.create-student-checkbox:checked').length;
        if (checked > TEAM_MEMBER_MAX) {
            checkbox.checked = false;
            alert('A team cannot have more than ' + TEAM_MEMBER_MAX + ' members.');
            return;
        }
    }
    onTeamMemberToggle(checkbox, 'create');
}

/*
   The picked members and their roles, shown above the list instead of only inside
   it. Randomize ticks four boxes somewhere in a scrolling column of every
   unassigned student, so without this the only way to read the result is to scroll
   and hunt for ticks.

   Built from the checkboxes rather than from what Randomize chose, so a member
   unticked or a role changed by hand is reflected too and the panel cannot claim
   someone who is no longer on the team.
*/
function renderCreateTeamSummary() {
    const panel = document.getElementById('createTeamSummary');
    const chips = document.getElementById('createTeamSummaryChips');
    if (!panel || !chips) return;

    const labels = window.TEAM_ROLE_LABELS || {};
    const picked = Array.from(document.querySelectorAll('.create-student-checkbox:checked'));

    // Nothing selected is the modal's opening state, not a result worth a panel.
    panel.classList.toggle('hidden', picked.length === 0);
    if (picked.length === 0) {
        chips.innerHTML = '';
        return;
    }

    chips.innerHTML = picked.map((cb) => {
        const card = cb.closest('.team-student-card');
        const name = card?.getAttribute('data-student-name') || 'Student';
        const roles = Array.from(
            card?.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked') || []
        ).map(r => r.value);

        const roleMarkup = roles.length
            ? roles.map(key => `
                <span class="inline-flex items-center gap-1 text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full role-dot-${escapeBulkAttr(key)} shrink-0"></span>
                    ${escapeBulkHtml(labels[key] || key)}
                </span>
            `).join('')
            : '<span class="text-amber-700">No role yet</span>';

        return `
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white border border-slate-200 text-[11px]">
                <span class="font-semibold text-slate-700">${escapeBulkHtml(name)}</span>
                <span class="text-slate-300">·</span>
                ${roleMarkup}
            </span>
        `;
    }).join('');
}

function randomizeSingleTeamMembers() {
    const checkboxes = Array.from(document.querySelectorAll('.create-student-checkbox'));
    const note = document.getElementById('createRandomizeNote');
    if (checkboxes.length === 0) {
        if (note) note.textContent = 'No unassigned students available.';
        return;
    }

    checkboxes.forEach((cb) => {
        cb.checked = false;
        clearStudentRoles(cb);
    });

    const shuffled = [...checkboxes].sort(() => Math.random() - 0.5);
    const pick = shuffled.slice(0, Math.min(TEAM_MEMBER_MAX, shuffled.length));
    pick.forEach((cb, index) => {
        cb.checked = true;
        const card = cb.closest('.team-student-card');
        assignDefaultRoleToCard(card, TEAM_DEFAULT_ROLES[index % TEAM_DEFAULT_ROLES.length]);
    });

    updateTeamSelectedCount('create');
    renderCreateTeamSummary();
    if (note) {
        note.textContent = pick.length < TEAM_MEMBER_MAX
            ? `Selected ${pick.length} available student${pick.length === 1 ? '' : 's'} (need ${TEAM_MEMBER_MAX} for a full team).`
            : `Randomly selected ${TEAM_MEMBER_MAX} members.`;
    }
}

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
    if (!el) return;
    el.textContent = mode === 'create'
        ? (count + ' / ' + TEAM_MEMBER_MAX + ' selected')
        : (count + ' selected');
}

document.addEventListener('DOMContentLoaded', function () {
    updateTeamSelectedCount('insert');
    updateTeamSelectedCount('create');
    renderCreateTeamSummary();
    rebuildBulkTeamSlots(maxCompleteBulkTeams());
    const randomizeBtn = document.getElementById('createRandomizeBtn');
    if (randomizeBtn) randomizeBtn.addEventListener('click', randomizeSingleTeamMembers);
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

    refreshRoleAvailability('update');

    document.getElementById('updateTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

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
    refreshRoleAvailability('update');
}

// ── Task Assignment Form ───────────────────────
// One page, no steps: team, tasks and due date are all on screen at once, and the
// summary at the foot updates as they are filled in. It used to be a five-screen
// wizard, which cost a click per screen to reach the assign button.
let selectedTeam = null;

// How many members of each team hold each role, so a task row can say who is
// actually there to receive it.
const TEAM_ROLE_COUNTS = @json($teamRoleCounts ?? []);

function selectTeam(groupName) {
    selectedTeam = groupName;
    updateRoleMemberCounts();
    updateSubmitState();
}

// The headcount hint the department step used to carry on its cards. A task row
// names its own department now, so the hint rides along with the row. A role
// nobody fills is still assignable — the task waits for whoever takes it — so
// this informs rather than blocks.
function updateRoleMemberCounts() {
    const counts = (selectedTeam && TEAM_ROLE_COUNTS[selectedTeam]) || {};

    document.querySelectorAll('[data-role-count]').forEach(el => {
        const n = counts[el.dataset.roleCount] || 0;
        el.textContent = selectedTeam
            ? ' · ' + (n > 0 ? n + (n === 1 ? ' member' : ' members') : 'nobody holds this')
            : '';
        el.classList.toggle('text-amber-500', !!selectedTeam && n === 0);
    });
}

// ── Task groups ────────────────────────────────
// One group is one numbered step of the simulation, holding that position's task
// for every department that still has one.

function taskGroupBoxes(step) {
    return Array.from(document.querySelectorAll('.task-step-check[data-group="' + step + '"]'));
}

function toggleTaskGroup(btn, step) {
    const body = document.getElementById('taskGroupBody-' + step);
    if (!body) return;

    const open = !body.classList.toggle('hidden');
    // public/css/app.css is a frozen build with no rotate-180 in it, and Iconify
    // swaps the chevron span for an <svg> of its own, so the button around it is
    // turned with an inline transform.
    btn.style.transform = open ? 'rotate(180deg)' : '';
}

function toggleTaskGroupAll(step) {
    const master = document.getElementById('taskGroupCheck-' + step);
    if (!master) return;

    master.indeterminate = false;
    taskGroupBoxes(step).forEach(cb => cb.checked = master.checked);
    updateSubmitState();
}

// The header box reflects its rows: all, none, or the browser's own partial mark
// when a department has been unticked out of the step.
function syncTaskGroup(step) {
    const master = document.getElementById('taskGroupCheck-' + step);
    if (!master) return;

    const boxes = taskGroupBoxes(step);
    const checked = boxes.filter(cb => cb.checked).length;

    master.checked = checked > 0 && checked === boxes.length;
    master.indeterminate = checked > 0 && checked < boxes.length;
}

function selectAllVisibleTasks() {
    const boxes = Array.from(document.querySelectorAll('.task-check'));
    const allChecked = boxes.length > 0 && boxes.every(cb => cb.checked);

    boxes.forEach(cb => cb.checked = !allChecked);
    document.querySelectorAll('.task-group-check').forEach(master => {
        master.checked = !allChecked;
        master.indeterminate = false;
    });

    updateSubmitState();
}

// ── Summary + submit ───────────────────────────
// Assigning needs a team and at least one task; the server checks the team too,
// but there is no reason to let the button be pressed without one.
function updateSubmitState() {
    const checked = Array.from(document.querySelectorAll('.task-check:checked'));

    const btn = document.getElementById('submitTasksBtn');
    if (btn) btn.disabled = !selectedTeam || checked.length === 0;

    updateSummary(checked);
}

function updateSummary(checked) {
    checked = checked || Array.from(document.querySelectorAll('.task-check:checked'));

    document.getElementById('reviewTeamName').textContent = selectedTeam || '—';

    document.getElementById('reviewTaskCount').textContent =
        checked.length + ' task' + (checked.length !== 1 ? 's' : '');

    // A step spans every department that has work at that position, so the summary
    // counts them rather than naming one.
    const roles = new Set(checked.map(cb => cb.dataset.role));
    document.getElementById('reviewDeptName').textContent = roles.size
        ? 'across ' + roles.size + (roles.size === 1 ? ' department' : ' departments')
        : '—';

    // datetime-local already carries the time, so parse it as-is rather than
    // pinning midnight the way the date-only input needed.
    const dueDate = document.querySelector('input[name="due_date"]').value;
    document.getElementById('reviewDueDate').textContent = dueDate
        ? new Date(dueDate).toLocaleString('en-US', {
              month: 'short', day: 'numeric', year: 'numeric',
              hour: 'numeric', minute: '2-digit',
          })
        : 'No deadline';
}

function resetTaskWizard() {
    selectedTeam = null;
    document.querySelectorAll('.task-team-radio').forEach(r => r.checked = false);
    document.querySelectorAll('.task-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.task-group-check').forEach(master => {
        master.checked = false;
        master.indeterminate = false;
    });
    document.querySelector('input[name="due_date"]').value = '';

    updateRoleMemberCounts();
    updateSubmitState();
}

// Track checkbox and due-date changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('task-check')) {
        syncTaskGroup(e.target.dataset.group);
        updateSubmitState();
    } else if (e.target.name === 'due_date') {
        updateSummary();
    }
});


// Initialize on page load
(function() {
    const activeTab = '{{ $activeTab }}';
    if (activeTab === 'create_task') {
        // A failed submit re-renders with the team still ticked; pick it back up so
        // the summary and the headcount hints match what is on the form.
        const checkedTeam = document.querySelector('.task-team-radio:checked');
        if (checkedTeam) selectTeam(checkedTeam.value);
        updateSubmitState();
    }
    // Auto-open create team modal if there are validation errors from that form
    @if($errors->any() && in_array(old('_form_source'), ['create_teams_bulk', 'insert_student', 'create_team'], true))
        @if(old('_form_source') === 'create_teams_bulk')
            currentCreateTeamMode = 'multiple';
        @elseif(old('_form_source') === 'create_team')
            currentCreateTeamMode = 'single';
        @endif
        openCreateTeamModal();
        @if(old('_form_source') === 'insert_student')
            switchCreateModalTab('insert');
        @endif
        // Reflect the roles old() restored into the form as already-taken.
        refreshRoleAvailability('create');
        refreshRoleAvailability('insert');
    @endif
    // Open from Activity Logs "Add Team" (or ?create=1)
    @if(request()->boolean('create'))
        openCreateTeamModal();
        const url = new URL(window.location);
        url.searchParams.delete('create');
        history.replaceState(null, '', url);
    @endif
})();

@if ($errors->any() && old('_form_source') === 'update_team')
window.addEventListener('load', function () {
    Swal.fire({
        icon: 'error',
        title: 'Could not save team',
        text: @json($errors->first()),
        confirmButtonColor: '#DB2777',
    });
});
@endif

@if (session('success'))
// Creating a team redirects, so the modal is gone by the time the page is back and
// the green banner is the only thing that says it worked — easy to miss above a full
// table. Same popup the rest of the app uses for a completed action.
window.addEventListener('load', function () {
    Swal.fire({
        icon: 'success',
        title: @json(session('success_title', 'Success')),
        text: @json(session('success')),
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false,
        iconColor: '#DB2777',
        width: '22rem',
    });
});
@endif

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
        activityBody.innerHTML = '<tr><td colspan="6" class="px-3 py-6 text-center text-xs text-slate-400">No activity logs for this team yet.</td></tr>';
        if (pager) pager.classList.add('hidden');
        if (meta) meta.textContent = '';
        return;
    }

    const start = (teamModalActivityPage - 1) * TEAM_MODAL_ACTIVITY_PER_PAGE;
    const pageLogs = logs.slice(start, start + TEAM_MODAL_ACTIVITY_PER_PAGE);
    const end = start + pageLogs.length;

    activityBody.innerHTML = pageLogs.map(function(log) {
        const isDone = log.status === 'archived';
        // The concept is only "Completed" once it is approved, and approving stamps
        // the feedback — so a submitted-but-unanswered concept says Submitted.
        const doneLabel = (log.is_hotel_concept && !log.has_feedback) ? 'Submitted' : 'Completed';
        const statusBadge = isDone
            ? '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>' + doneLabel + '</span>'
            : '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Assigned</span>';

        // A task fans out one row per member, so the student tells identical titles apart.
        const student = log.student_name
            ? '<div class="flex items-center gap-1.5 min-w-0">' +
                '<span class="w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-[9px] font-bold flex items-center justify-center shrink-0">' +
                    escHtml(String(log.student_name).charAt(0).toUpperCase()) +
                '</span>' +
                '<span class="text-[11px] font-semibold text-slate-700 truncate" title="' + escHtml(log.student_name) + '">' + escHtml(log.student_name) + '</span>' +
              '</div>'
            : '<span class="text-[10px] text-slate-300">Unassigned</span>';

        // Only submitted work can be reviewed; nothing to look at before that.
        // The action lives in its own button rather than on the row or the date,
        // so it is obvious what is clickable and what is just information.
        const reviewable = isDone && log.id;
        const reviewCell = reviewable
            ? '<button type="button" data-review-task="' + Number(log.id) + '"'
                + ' title="Open this submission and leave feedback"'
                + ' class="w-full inline-flex items-center justify-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold transition '
                + (log.has_feedback
                    ? 'bg-white text-brand border border-brand/30 hover:bg-brand-soft'
                    : 'bg-brand text-white hover:opacity-90 shadow-sm shadow-brand/20') + '">'
                + '<span class="iconify text-xs shrink-0" data-icon="mdi:file-document-edit-outline"></span>'
                + (log.has_feedback ? 'Reviewed' : 'Review')
              + '</button>'
            : '<span class="block text-center text-[10px] font-semibold text-slate-300">Not submitted</span>';

        return '<tr class="hover:bg-slate-50 transition-colors">' +
            '<td class="px-3 py-2">' +
                '<p class="text-xs font-semibold text-slate-800 truncate" title="' + escHtml(log.title) + '">' +
                    escHtml(log.title || '—') +
                '</p>' +
                (log.description ? '<p class="text-[10px] text-slate-400 truncate">' + escHtml(log.description) + '</p>' : '') +
            '</td>' +
            '<td class="px-3 py-2">' + student + '</td>' +
            '<td class="px-3 py-2 text-[11px] font-semibold text-slate-600 whitespace-nowrap">' + escHtml(log.role_label || log.role || '—') + '</td>' +
            '<td class="px-3 py-2">' + statusBadge + '</td>' +
            '<td class="px-3 py-2 text-[11px] whitespace-nowrap ' + (isDone ? 'text-slate-500' : 'text-slate-300') + '">' +
                escHtml(isDone ? (log.submitted_at || log.updated_at || '—') : 'Not yet') +
            '</td>' +
            '<td class="px-3 py-2">' + reviewCell + '</td>' +
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
        tbody.innerHTML = '<tr><td colspan="4" class="px-3 py-6 text-center text-xs text-slate-400">No members found.</td></tr>';
    } else {
        tbody.innerHTML = members.map(function(m, i) {
            const roleLabels = m.role_labels || [m.role_label || m.role];
            const roleBadges = roleLabels.map(function(rl) {
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-100">' + rl + '</span>';
            }).join(' ');
            const activityBtn = m.user_id
                ? '<button type="button" data-activity-user="' + Number(m.user_id) + '"' +
                    ' data-activity-name="' + escHtml(m.name) + '"' +
                    ' class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold text-brand bg-brand-soft border border-brand/10 hover:bg-brand/10 transition"' +
                    ' title="View this member\'s activity logs">' +
                    '<span class="iconify text-xs" data-icon="mdi:clipboard-text-clock-outline"></span> Activity' +
                  '</button>'
                : '<span class="text-[10px] text-slate-300">—</span>';
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
                '<td class="px-3 py-2 text-center">' + activityBtn + '</td>' +
            '</tr>';
        }).join('');
    }

    closeMemberActivityPanel();

    teamModalActivityLogs = logs;
    teamModalActivityPage = 1;
    renderTeamModalActivityPage();

    // Always open on Members so the modal never reappears on the other tab.
    switchTeamModalTab('members');
    loadTeamHotelConcept(groupName);

    document.getElementById('teamInfoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

/* Centralized activity log — the server decides whether this faculty may read it. */
const MEMBER_ACTIVITY_URL = @json(route('faculty.activity.user', ['user' => '__ID__']));

function closeMemberActivityPanel() {
    const panel = document.getElementById('memberActivityPanel');
    if (panel) panel.classList.add('hidden');
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
    if (title) title.textContent = (memberName || 'Member') + ' — Activity Logs';
    body.innerHTML = '<div class="px-3 py-6 text-center text-xs text-slate-400">Loading activity…</div>';

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
            body.innerHTML = '<div class="px-3 py-6 text-center text-xs text-rose-500 font-semibold">'
                + escHtml(err.message || 'Could not load activity logs.') + '</div>';
        });
}

function renderActivityRows(logs) {
    if (!logs.length) {
        return '<div class="px-3 py-6 text-center text-xs text-slate-400">No recorded activity for this member yet.</div>';
    }
    return logs.map(function (log) {
        return '<div class="px-3 py-2.5 flex items-start gap-2.5 hover:bg-slate-50/70 transition">' +
            '<span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10 whitespace-nowrap">' +
                escHtml(log.activity_label || log.activity || '—') +
            '</span>' +
            '<div class="min-w-0 flex-1">' +
                '<p class="text-xs text-slate-700">' + escHtml(log.description || '—') + '</p>' +
                '<p class="text-[10px] text-slate-400 mt-0.5">' + escHtml(log.created_at || '') +
                    (log.created_at_human ? ' · ' + escHtml(log.created_at_human) : '') + '</p>' +
            '</div>' +
        '</div>';
    }).join('');
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