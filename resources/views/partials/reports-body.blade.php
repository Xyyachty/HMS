{{--
    The Reports page body, shared by faculty and dean: Overview, Student Reports,
    Team Reports and Activity Reports. The data comes from App\Support\ReportDesk.
    Set $showFaculty to name each team's faculty under it, for the dean, whose
    list mixes every faculty's teams.
--}}
@php $showFaculty = $showFaculty ?? false; @endphp

<style>
    /* public/css/app.css is a frozen Tailwind build carrying no breakpoint
       utilities, so this screen's layout is written out rather than composed
       from sm:/lg:/xl: classes that would resolve to nothing. */
    .rp-tabs { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
    .rp-tab {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .7rem 1.25rem; border-radius: .875rem;
        font-size: .8125rem; font-weight: 700; color: #6B4A54;
        background: #fff; border: 1px solid #E4D3CF; cursor: pointer;
        white-space: nowrap; transition: all .2s ease;
    }
    .rp-tab:hover { border-color: rgba(123,23,48,.4); color: #7B1730; }
    .rp-tab.active {
        background: #7B1730;
        color: #fff; border-color: transparent;
        box-shadow: 0 8px 20px -6px rgba(123,23,48,.4);
    }
    .rp-panel { display: none; }
    .rp-panel.active { display: block; }

    .rp-stat-grid { display: grid; gap: 1rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .rp-split-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }

    /* Still used by the Role column on Activity Reports. */
    .rp-legend-dot { width: .625rem; height: .625rem; border-radius: 9999px; flex: 0 0 auto; }

    /* Filter bar: Team, Task and Role. */
    .rp-filters { display: flex; align-items: center; gap: .625rem; flex-wrap: wrap; }
    .rp-filter {
        height: 2.5rem; min-width: 11rem; max-width: 100%; padding: 0 .75rem;
        border-radius: .75rem; border: 1px solid #E4D3CF; background: #fff;
        font-size: 12px; font-weight: 600; color: #5A3941;
    }
    .rp-filter:focus { outline: none; border-color: #7B1730; box-shadow: 0 0 0 3px rgba(123,23,48,.12); }
    .rp-filter.is-set { border-color: #7B1730; color: #7B1730; background: #FBEEE9; }
    .rp-filter-clear {
        display: inline-flex; align-items: center; gap: .375rem; height: 2.5rem; padding: 0 .875rem;
        border-radius: .75rem; border: 1px solid #E4D3CF; background: #fff;
        font-size: 12px; font-weight: 700; color: #6B4A54;
    }
    .rp-filter-clear:hover { color: #7B1730; border-color: rgba(123,23,48,.4); }

    .rp-track { height: .5rem; border-radius: 9999px; background: #F2E9E7; overflow: hidden; }
    .rp-track > span { display: block; height: 100%; border-radius: 9999px; }

    @media (max-width: 1279px) {
        .rp-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rp-split-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 767px) {
        .rp-stat-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media print {
        .app-sidebar, .glass-header, .rp-tabs, .rp-no-print { display: none !important; }
        .rp-panel { display: block !important; }
    }

    /* Utilities the frozen build never emitted. */
    .text-\[10px\] { font-size: 10px; line-height: 1.35; }
    .text-\[11px\] { font-size: 11px; line-height: 1.4; }
    .text-\[12px\] { font-size: 12px; line-height: 1.45; }
    .text-\[13px\] { font-size: 13px; line-height: 1.45; }
    .text-\[15px\] { font-size: 15px; line-height: 1.5; }
    .text-\[28px\] { font-size: 28px; line-height: 1.1; }
    .text-\[30px\] { font-size: 30px; line-height: 1.15; }
    .gap-1\.5 { gap: .375rem; }
    .gap-2\.5 { gap: .625rem; }
    .mt-0\.5 { margin-top: .125rem; }
    .mt-1\.5 { margin-top: .375rem; }
    .py-0\.5 { padding-top: .125rem; padding-bottom: .125rem; }
    .py-1\.5 { padding-top: .375rem; padding-bottom: .375rem; }
    .py-2\.5 { padding-top: .625rem; padding-bottom: .625rem; }
    .py-3\.5 { padding-top: .875rem; padding-bottom: .875rem; }
    .px-2\.5 { padding-left: .625rem; padding-right: .625rem; }
    .px-3\.5 { padding-left: .875rem; padding-right: .875rem; }
    .pt-5 { padding-top: 1.25rem; }
    .pb-5 { padding-bottom: 1.25rem; }
    .pb-3 { padding-bottom: .75rem; }
    .leading-snug { line-height: 1.375; }
    .hover\:text-brand:hover { color: #7B1730; }
    .hover\:bg-slate-50:hover { background-color: #FAF6F5; }
    .hover\:border-brand\/40:hover { border-color: rgba(123,23,48,.4); }
    .bg-slate-50\/60 { background-color: rgba(250,246,245,.6); }
    .bg-slate-50\/80 { background-color: rgba(250,246,245,.8); }
    .border-brand\/10 { border-color: rgba(123,23,48,.1); }
    .shadow-brand\/20 { --tw-shadow-color: rgba(123,23,48,.2); }
</style>

@php
    // Chart colours, kept to the palette already used across the portal. Still
    // read by the Role column further down on Activity Reports.
    $rpRoleColors = [
        'front_desk'            => '#C4425E',
        'restaurant_management' => '#D9B86A',
        'room_management'       => '#7B1730',
        'maintenance'           => '#C9A45C',
        'housekeeping'          => '#9E1B3C',
    ];

    $rpRoleTotal = collect($roleParticipation)->sum('count');
    $rpTopStudents = collect($studentPerformance ?? [])->take(5);
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="rp-tabs" id="reportTabs">
        <button type="button" class="rp-tab active" data-report-tab="overview" onclick="switchReportTab('overview')">
            <span class="iconify text-base" data-icon="mdi:view-dashboard-outline"></span> Overview
        </button>
        <button type="button" class="rp-tab" data-report-tab="students" onclick="switchReportTab('students')">
            <span class="iconify text-base" data-icon="mdi:account-outline"></span> Student Reports
        </button>
        <button type="button" class="rp-tab" data-report-tab="teams" onclick="switchReportTab('teams')">
            <span class="iconify text-base" data-icon="mdi:account-group-outline"></span> Team Reports
        </button>
        <button type="button" class="rp-tab" data-report-tab="activity" onclick="switchReportTab('activity')">
            <span class="iconify text-base" data-icon="mdi:clipboard-text-clock-outline"></span> Activity Reports
        </button>
    </div>

    <div class="flex items-center gap-3 rp-no-print">
        {{-- The period the rows on screen cover, after the filters below. --}}
        <span class="inline-flex items-center gap-2 h-11 px-4 rounded-xl border border-slate-200 bg-white text-[13px] font-semibold text-slate-600 whitespace-nowrap">
            <span class="iconify text-base text-slate-400" data-icon="mdi:calendar-range-outline"></span>
            {{ $reportFrom ? $reportFrom->format('M j, Y') : '—' }} – {{ $reportTo ? $reportTo->format('M j, Y') : '—' }}
        </span>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 h-11 px-5 rounded-xl brand-gradient text-white text-[13px] font-bold shadow-md shadow-brand/20 hover:opacity-95 transition">
            <span class="iconify text-base" data-icon="mdi:download-outline"></span>
            Export Report
        </button>
    </div>
</div>

{{-- ═══════════════ FILTERS ═══════════════
     A plain GET form: every figure and table below is recounted on the server
     for the chosen team, task and role, so the tabs never disagree. The open
     tab rides along in "tab" and is reopened after the reload. --}}
<form method="GET" action="{{ url()->current() }}" id="reportFilters" class="rp-filters rp-no-print mb-5">
    <input type="hidden" name="tab" id="reportFilterTab" value="{{ request('tab', 'overview') }}">
    <span class="inline-flex items-center gap-1.5 text-[12px] font-bold text-slate-500">
        <span class="iconify text-base" data-icon="mdi:filter-variant"></span> Filter by
    </span>
    <select name="team" class="rp-filter {{ $filters['team'] ? 'is-set' : '' }}" onchange="this.form.submit()" aria-label="Filter by team">
        <option value="">All Teams</option>
        @foreach($teamOptions as $value => $label)
            <option value="{{ $value }}" @selected($filters['team'] === (string) $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="task" class="rp-filter {{ $filters['task'] ? 'is-set' : '' }}" onchange="this.form.submit()" aria-label="Filter by task">
        <option value="">All Tasks</option>
        @foreach($taskOptions as $title)
            <option value="{{ $title }}" @selected($filters['task'] === $title)>{{ $title }}</option>
        @endforeach
    </select>
    <select name="role" class="rp-filter {{ $filters['role'] ? 'is-set' : '' }}" onchange="this.form.submit()" aria-label="Filter by role">
        <option value="">All Roles</option>
        @foreach($roleOptions as $value => $label)
            <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if($isFiltered)
        <a href="{{ url()->current() }}?tab={{ urlencode(request('tab', 'overview')) }}" class="rp-filter-clear" id="reportFilterClear">
            <span class="iconify text-base" data-icon="mdi:filter-remove-outline"></span> Clear filters
        </a>
    @endif
</form>

{{-- ═══════════════ OVERVIEW ═══════════════ --}}
<div id="report-panel-overview" class="rp-panel active">
    <div class="rp-stat-grid mb-4">
        <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-soft flex items-center justify-center shrink-0">
                <span class="iconify text-brand text-2xl" data-icon="mdi:account-multiple-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[13px] text-slate-500 font-semibold">Total Students</p>
                <p class="text-[28px] font-extrabold text-slate-900 leading-none mt-1">{{ $totalStudents }}</p>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                <span class="iconify text-blue-500 text-2xl" data-icon="mdi:account-group-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[13px] text-slate-500 font-semibold">Total Teams</p>
                <p class="text-[28px] font-extrabold text-slate-900 leading-none mt-1">{{ $totalTeams }}</p>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-violet-50 flex items-center justify-center shrink-0">
                <span class="iconify text-violet-500 text-2xl" data-icon="mdi:clipboard-check-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[13px] text-slate-500 font-semibold">Total Activities</p>
                <p class="text-[28px] font-extrabold text-slate-900 leading-none mt-1">{{ $totalActivities }}</p>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0">
                <span class="iconify text-amber-500 text-2xl" data-icon="mdi:star-outline"></span>
            </div>
            <div class="min-w-0">
                <p class="text-[13px] text-slate-500 font-semibold">Overall Completion Rate</p>
                <p class="text-[28px] font-extrabold text-slate-900 leading-none mt-1">{{ $overallRate }}%</p>
            </div>
        </div>
    </div>

    <div class="rp-split-grid">
        <!-- Top Performing Students -->
        <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden">
            <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[15px] font-bold text-slate-800">Top Performing Students</p>
                    <p class="text-[12px] text-slate-400">Students with the highest activity completion rate.</p>
                </div>
                <button type="button" onclick="switchReportTab('students')"
                        class="h-9 px-3.5 rounded-xl border border-slate-200 text-[12px] font-bold text-slate-600 hover:border-brand/40 hover:text-brand transition shrink-0 rp-no-print">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500 w-10">#</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Student Name</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Team</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rpTopStudents as $row)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3 text-[13px] font-semibold text-slate-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @include('partials.user-avatar', [
                                            'user'         => $row['user'],
                                            'name'         => $row['name'],
                                            'size'         => 'w-8 h-8',
                                            'rounded'      => 'rounded-full',
                                            'extraClasses' => 'bg-brand-soft text-brand text-[11px] font-bold',
                                        ])
                                        <span class="text-[13px] font-semibold text-slate-700 truncate">{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[13px] text-slate-500 truncate">{{ $row['team'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="rp-track" style="flex: 1 1 auto">
                                            <span style="width: {{ $row['percent'] }}%; background: #7B1730"></span>
                                        </span>
                                        <span class="text-[13px] font-extrabold text-slate-700 shrink-0">{{ $row['percent'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-[13px] text-slate-400 font-semibold">No students on a team yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden">
            <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[15px] font-bold text-slate-800">Recent Activities</p>
                    <p class="text-[12px] text-slate-400">Latest completed activities from all teams.</p>
                </div>
                <button type="button" onclick="switchReportTab('activity')"
                        class="h-9 px-3.5 rounded-xl border border-slate-200 text-[12px] font-bold text-slate-600 hover:border-brand/40 hover:text-brand transition shrink-0 rp-no-print">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/60">
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Date</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Student</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Team</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Activity</th>
                            <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $row)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3">
                                    <p class="text-[12px] font-semibold text-slate-600 whitespace-nowrap">{{ $row['date'] }}</p>
                                    <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ $row['time'] }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @include('partials.user-avatar', [
                                            'user'         => $row['user'],
                                            'name'         => $row['student'],
                                            'size'         => 'w-8 h-8',
                                            'rounded'      => 'rounded-full',
                                            'extraClasses' => 'bg-brand-soft text-brand text-[11px] font-bold',
                                        ])
                                        <span class="text-[13px] font-semibold text-slate-700 truncate">{{ $row['student'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-[13px] text-slate-500 truncate">{{ $row['team'] }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-[13px] font-semibold text-slate-700 leading-snug">{{ $row['activity'] }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $row['role_label'] }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    {{-- Only handed-in work reaches this table, so the badge has one colour. --}}
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap bg-emerald-50 text-emerald-600">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-[13px] text-slate-400 font-semibold">No activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ STUDENT REPORTS ═══════════════ --}}
<div id="report-panel-students" class="rp-panel">
    <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden">
        <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[15px] font-bold text-slate-800">Student Reports</p>
                <p class="text-[12px] text-slate-400">Every student on a team, with the activities counted toward them.</p>
            </div>
            <span class="text-[12px] font-bold text-brand shrink-0">{{ collect($studentPerformance)->count() }} students</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500 w-10">#</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Student Name</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Team</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Activities</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentPerformance as $row)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-3 text-[13px] font-semibold text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    @include('partials.user-avatar', [
                                        'user'         => $row['user'],
                                        'name'         => $row['name'],
                                        'size'         => 'w-8 h-8',
                                        'rounded'      => 'rounded-full',
                                        'extraClasses' => 'bg-brand-soft text-brand text-[11px] font-bold',
                                    ])
                                    <span class="text-[13px] font-semibold text-slate-700 truncate">{{ $row['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-500 truncate">{{ $row['team'] }}</td>
                            <td class="px-4 py-3 text-[13px] text-slate-600 whitespace-nowrap">{{ $row['done'] }} / {{ $row['total'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="rp-track" style="flex: 1 1 auto; max-width: 12rem">
                                        <span style="width: {{ $row['percent'] }}%; background: #7B1730"></span>
                                    </span>
                                    <span class="text-[13px] font-extrabold text-slate-700 shrink-0">{{ $row['percent'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-[13px] text-slate-400 font-semibold">No students on a team yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════ TEAM REPORTS (the finalized work, as before) ═══════════════ --}}
<div id="report-panel-teams" class="rp-panel">
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Completed Tasks by Team</h3>
        <span class="text-[11px] font-semibold text-slate-400">{{ $teamReports->count() }} team{{ $teamReports->count() === 1 ? '' : 's' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" style="table-layout:fixed">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:18%">Team Name</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:24%">Page Name</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Assigned Date</th>
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:14%">Date Completed</th>
                    <th class="text-center px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:18%">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teamReports as $index => $report)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 truncate" title="{{ $report['team_name'] }}">{{ $report['team_name'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $report['task_count'] }} completed task{{ $report['task_count'] === 1 ? '' : 's' }}@if($showFaculty && $report['faculty_name']) &middot; {{ $report['faculty_name'] }}@endif</p>
                        </td>
                        <td class="px-4 py-3 text-xs font-semibold text-slate-600">
                            <span class="line-clamp-2" title="{{ $report['page_name'] }}">{{ $report['page_name'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $report['assigned_date'] }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $report['date_completed'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <button
                                type="button"
                                onclick="openTeamReportModal({{ $index }})"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-brand bg-brand-soft hover:bg-brand/10 transition"
                            >
                                <span class="iconify text-sm" data-icon="mdi:eye-outline"></span>
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 font-semibold">No finalized completed tasks to report yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>

{{-- ═══════════════ ACTIVITY REPORTS ═══════════════ --}}
<div id="report-panel-activity" class="rp-panel">
    <div class="rounded-2xl border border-slate-100 bg-white overflow-hidden">
        <div class="px-5 pt-5 pb-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[15px] font-bold text-slate-800">Activity Reports</p>
                <p class="text-[12px] text-slate-400">Completed against pending work, by role.</p>
            </div>
            <span class="text-[12px] font-bold text-brand shrink-0">{{ $doneActivities }} of {{ $totalActivities }} done</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Role</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Students Assigned</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Share of Assignments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roleParticipation as $slice)
                        @php $share = $rpRoleTotal > 0 ? (int) round(($slice['count'] / $rpRoleTotal) * 100) : 0; @endphp
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 text-[13px] font-bold text-slate-700">
                                    <span class="rp-legend-dot" style="background: {{ $rpRoleColors[$slice['role']] ?? '#C9AFAA' }}"></span>
                                    {{ $slice['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600">{{ $slice['count'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="rp-track" style="flex: 1 1 auto; max-width: 16rem">
                                        <span style="width: {{ $share }}%; background: {{ $rpRoleColors[$slice['role']] ?? '#C9AFAA' }}"></span>
                                    </span>
                                    <span class="text-[13px] font-extrabold text-slate-700 shrink-0">{{ $share }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 pt-4 pb-3 border-t border-slate-100">
            <p class="text-[15px] font-bold text-slate-800">Latest Completed</p>
            <p class="text-[12px] text-slate-400">The most recent rows across every team.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Date</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Student</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Team</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Activity</th>
                        <th class="px-4 py-2.5 text-[12px] font-bold text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivities as $row)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-3">
                                <p class="text-[12px] font-semibold text-slate-600 whitespace-nowrap">{{ $row['date'] }}</p>
                                <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ $row['time'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-[13px] font-semibold text-slate-700 truncate">{{ $row['student'] }}</td>
                            <td class="px-4 py-3 text-[13px] text-slate-500 truncate">{{ $row['team'] }}</td>
                            <td class="px-4 py-3">
                                <p class="text-[13px] font-semibold text-slate-700 leading-snug">{{ $row['activity'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $row['role_label'] }}</p>
                            </td>
                            <td class="px-4 py-3">
                                {{-- Only handed-in work reaches this table, so the badge has one colour. --}}
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold whitespace-nowrap bg-emerald-50 text-emerald-600">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-[13px] text-slate-400 font-semibold">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Team Report Modal -->
<div id="teamReportModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTeamReportModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-h-[90vh] flex flex-col" style="max-width: 56rem;">
        <div class="bg-brand-soft px-4 py-3 border-b border-brand/10 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <div class="min-w-0">
                <h4 id="teamReportModalTitle" class="font-bold text-brand text-sm truncate">Team Details</h4>
                <p id="teamReportModalSubtitle" class="text-[11px] text-slate-500 truncate"></p>
            </div>
            <button type="button" onclick="closeTeamReportModal()" class="text-slate-400 hover:text-brand hover:bg-white w-7 h-7 rounded-full transition flex items-center justify-center shrink-0" aria-label="Close">
                <span class="iconify text-lg" data-icon="mdi:close"></span>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-4 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned</p>
                    <p id="teamReportAssigned" class="text-sm font-semibold text-slate-700 mt-0.5">—</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Completed</p>
                    <p id="teamReportCompleted" class="text-sm font-semibold text-slate-700 mt-0.5">—</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tasks</p>
                    <p id="teamReportTaskCount" class="text-sm font-semibold text-slate-700 mt-0.5">0</p>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Team Members</p>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Member</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Role</th>
                            </tr>
                        </thead>
                        <tbody id="teamReportMembersBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Completed Tasks</p>
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Task</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Student</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Page / Role</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Due</th>
                                <th class="text-left px-3 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Completed</th>
                            </tr>
                        </thead>
                        <tbody id="teamReportTasksBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-4 py-2.5 border-t border-slate-100 flex justify-end rounded-b-2xl flex-shrink-0 bg-slate-50/50">
            <button type="button" onclick="closeTeamReportModal()" class="px-3.5 py-1.5 rounded-lg bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 transition font-semibold text-xs">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    /* Report tabs. Every panel is rendered; the tabs only decide which is on
       screen, so nothing here re-queries. */
    function switchReportTab(name) {
        document.querySelectorAll('.rp-panel').forEach((panel) => {
            panel.classList.toggle('active', panel.id === 'report-panel-' + name);
        });
        document.querySelectorAll('#reportTabs .rp-tab').forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.reportTab === name);
        });

        // A filter change reloads the page; this is how it comes back to this tab.
        const tabInput = document.getElementById('reportFilterTab');
        if (tabInput) tabInput.value = name;
        const clear = document.getElementById('reportFilterClear');
        if (clear) clear.href = clear.href.replace(/([?&]tab=)[^&]*/, '$1' + encodeURIComponent(name));
    }

    (function reopenReportTab() {
        const tab = new URLSearchParams(window.location.search).get('tab');
        if (tab && document.getElementById('report-panel-' + tab)) switchReportTab(tab);
    })();

    const teamReports = @json($teamReports);

    function openTeamReportModal(index) {
        const report = teamReports[index];
        if (!report) return;

        document.getElementById('teamReportModalTitle').textContent = report.team_name || 'Team Details';
        document.getElementById('teamReportModalSubtitle').textContent = report.page_name
            ? `Pages: ${report.page_name}`
            : '';

        document.getElementById('teamReportAssigned').textContent = report.assigned_date || '—';
        document.getElementById('teamReportCompleted').textContent = report.date_completed || '—';
        document.getElementById('teamReportTaskCount').textContent = String(report.task_count ?? (report.tasks || []).length);

        const membersBody = document.getElementById('teamReportMembersBody');
        const members = report.members || [];
        membersBody.innerHTML = members.length
            ? members.map((member, i) => `
                <tr>
                    <td class="px-3 py-2 text-xs text-slate-400">${i + 1}</td>
                    <td class="px-3 py-2 text-sm font-semibold text-slate-700">${escapeHtml(member.name || '—')}</td>
                    <td class="px-3 py-2 text-xs text-slate-500">${escapeHtml((member.roles || []).join(', ') || '—')}</td>
                </tr>
            `).join('')
            : `<tr><td colspan="3" class="px-3 py-6 text-center text-xs text-slate-400 font-semibold">No team members found.</td></tr>`;

        const tasksBody = document.getElementById('teamReportTasksBody');
        const tasks = report.tasks || [];
        tasksBody.innerHTML = tasks.length
            ? tasks.map((task) => `
                <tr>
                    <td class="px-3 py-2">
                        <p class="text-sm font-semibold text-slate-700">${escapeHtml(task.title || '—')}</p>
                        ${task.description ? `<p class="text-[11px] text-slate-400 mt-0.5 line-clamp-2">${escapeHtml(task.description)}</p>` : ''}
                    </td>
                    <td class="px-3 py-2 text-xs font-semibold text-slate-600 whitespace-nowrap">${escapeHtml(task.student_name || '—')}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">
                            ${escapeHtml(task.role_label || task.role || '—')}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-xs text-slate-500 whitespace-nowrap">${escapeHtml(task.due_date || '—')}</td>
                    <td class="px-3 py-2 text-xs text-slate-500 whitespace-nowrap">${escapeHtml(task.completed_at || '—')}</td>
                </tr>
            `).join('')
            : `<tr><td colspan="5" class="px-3 py-6 text-center text-xs text-slate-400 font-semibold">No completed tasks for this team.</td></tr>`;

        document.getElementById('teamReportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeTeamReportModal() {
        document.getElementById('teamReportModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeTeamReportModal();
    });
</script>
@endpush
