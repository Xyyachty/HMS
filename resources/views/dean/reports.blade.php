@extends('dean.layouts.app')

@section('page_title', 'Reports')
@section('reports_active', 'active')

@section('content')
{{--
    Three reports on one screen: completed work, how each student is doing, and
    the log behind both. All three are rendered at once and switched in the
    browser — they are the same few hundred rows, and a filter that has to wait
    on the server is a filter nobody uses. Export writes what is on screen after
    the filters, not the whole table.

    The dean layout loads Tailwind from the CDN (not the frozen build the faculty
    pages are stuck with), so ordinary utility classes, breakpoints included, are
    safe here.
--}}
<style>
    .rp-tab {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .7rem 1.1rem; border-radius: .85rem;
        font-size: .82rem; font-weight: 700; color: #64748b;
        background: #fff; border: 1px solid #e2e8f0; transition: all .18s ease;
    }
    .rp-tab:hover { color: #DB2777; border-color: rgba(219,39,119,.35); }
    .rp-tab.is-active { background: linear-gradient(135deg,#F472B6,#DB2777,#9D174D); color: #fff; border-color: transparent; box-shadow: 0 8px 18px -6px rgba(219,39,119,.5); }
    .rp-field {
        height: 2.6rem; border-radius: .85rem; border: 1px solid #e2e8f0; background: #fff;
        font-size: .8rem; font-weight: 600; color: #475569; padding: 0 .85rem;
    }
    .rp-field:focus { outline: none; border-color: #DB2777; box-shadow: 0 0 0 3px rgba(219,39,119,.12); }
    .rp-chip { display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .55rem; border-radius: 999px; font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .rp-row:hover { background: #fdf2f8; }
    .rp-empty { display: none; }
    .rp-th { text-align: left; padding: .75rem 1rem; font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; white-space: nowrap; }
    .rp-td { padding: .85rem 1rem; font-size: .78rem; color: #475569; vertical-align: top; }
</style>

{{-- ═══════ Header ═══════ --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
    <div class="min-w-0">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Reports</h1>
        <p class="text-[13px] text-slate-500 mt-1">View completed tasks and performance records of all teams in the hotel simulation.</p>
    </div>
    <nav class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-400 shrink-0" aria-label="Breadcrumb">
        <a href="{{ route('dean.dashboard') }}" class="hover:text-brand transition">Home</a>
        <span class="iconify text-sm" data-icon="mdi:chevron-right"></span>
        <span class="text-brand">Reports</span>
    </nav>
</div>

{{-- ═══════ Tabs ═══════ --}}
<div class="flex flex-wrap items-center gap-2 mb-4">
    <button type="button" class="rp-tab is-active" data-rp-tab="completed" onclick="rpSwitchTab('completed')">
        <span class="iconify text-base" data-icon="mdi:clipboard-check-outline"></span> Completed Tasks
    </button>
    <button type="button" class="rp-tab" data-rp-tab="students" onclick="rpSwitchTab('students')">
        <span class="iconify text-base" data-icon="mdi:account-school-outline"></span> Student Performance
    </button>
    <button type="button" class="rp-tab" data-rp-tab="activity" onclick="rpSwitchTab('activity')">
        <span class="iconify text-base" data-icon="mdi:history"></span> Activity Logs
    </button>
</div>

{{-- ═══════ Filters ═══════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-3 mb-4">
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative flex-1 min-w-[220px]">
            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base" data-icon="mdi:magnify"></span>
            <input id="rpSearch" type="search" oninput="rpApply()" autocomplete="off"
                   placeholder="Search team, task, activity, student, or keyword…"
                   class="rp-field w-full pl-9">
        </div>

        <select id="rpTeam" class="rp-field" onchange="rpApply()">
            <option value="">All Teams</option>
            @foreach($teamOptions as $team)
                <option value="{{ $team }}">{{ $team }}</option>
            @endforeach
        </select>

        <select id="rpTask" class="rp-field" onchange="rpApply()" data-rp-only="completed">
            <option value="">All Tasks</option>
            @foreach($taskOptions as $task)
                <option value="{{ $task }}">{{ $task }}</option>
            @endforeach
        </select>

        <select id="rpRole" class="rp-field" onchange="rpApply()">
            <option value="">All Roles</option>
            @foreach($roleOptions as $key => $label)
                <option value="{{ $label }}">{{ $label }}</option>
            @endforeach
        </select>

        <select id="rpStatus" class="rp-field" onchange="rpApply()">
            <option value="">All Statuses</option>
            @foreach($statusOptions as $status)
                <option value="{{ $status }}">{{ $status }}</option>
            @endforeach
        </select>

        {{-- Both ends optional: one alone reads as "from" or "until". --}}
        <div class="flex items-center gap-1.5">
            <span class="iconify text-slate-400 text-base" data-icon="mdi:calendar-range-outline"></span>
            <input id="rpFrom" type="date" class="rp-field" onchange="rpApply()" aria-label="From date">
            <span class="text-slate-300 text-xs font-bold">–</span>
            <input id="rpTo" type="date" class="rp-field" onchange="rpApply()" aria-label="To date">
        </div>

        <button type="button" onclick="rpExport()"
                class="rp-field brand-gradient text-white border-0 inline-flex items-center gap-1.5 px-4 font-bold hover:opacity-95 transition">
            <span class="iconify text-base" data-icon="mdi:tray-arrow-down"></span> Export Report
        </button>

        <button type="button" onclick="rpClearFilters()"
                class="rp-field inline-flex items-center gap-1.5 text-slate-500 hover:text-brand hover:border-brand/40 transition">
            <span class="iconify text-base" data-icon="mdi:filter-remove-outline"></span> Clear
        </button>
    </div>
</div>

{{-- ═══════ Panel ═══════ --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <div class="min-w-0">
            <h3 id="rpPanelTitle" class="text-[15px] font-bold text-slate-800">Completed Tasks by Team</h3>
            <p id="rpPanelHint" class="text-[12px] text-slate-400 mt-0.5">List of tasks that have been completed by all teams in the system.</p>
        </div>
        <p id="rpCount" class="text-[12px] font-semibold text-slate-400"></p>
    </div>

    <div class="overflow-x-auto">
        {{-- Completed tasks --}}
        <table class="w-full" data-rp-table="completed">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="rp-th w-10">#</th>
                    <th class="rp-th">Team</th>
                    <th class="rp-th">Task</th>
                    <th class="rp-th">Included Roles (No. of Activities)</th>
                    <th class="rp-th text-center">Total<br>Activities</th>
                    <th class="rp-th">Assigned Date</th>
                    <th class="rp-th">Date Completed</th>
                    <th class="rp-th">Status</th>
                    <th class="rp-th">Action</th>
                </tr>
            </thead>
            <tbody id="rpBodyCompleted" class="divide-y divide-slate-100"></tbody>
        </table>

        {{-- Student performance --}}
        <table class="w-full hidden" data-rp-table="students">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="rp-th w-10">#</th>
                    <th class="rp-th">Student</th>
                    <th class="rp-th">Team</th>
                    <th class="rp-th">Role(s)</th>
                    <th class="rp-th text-center">Assigned</th>
                    <th class="rp-th text-center">Submitted</th>
                    <th class="rp-th text-center">Approved</th>
                    <th class="rp-th text-center">Pending</th>
                    <th class="rp-th">Completion</th>
                    <th class="rp-th">Status</th>
                </tr>
            </thead>
            <tbody id="rpBodyStudents" class="divide-y divide-slate-100"></tbody>
        </table>

        {{-- Activity logs --}}
        <table class="w-full hidden" data-rp-table="activity">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="rp-th w-10">#</th>
                    <th class="rp-th">Date &amp; Time</th>
                    <th class="rp-th">User</th>
                    <th class="rp-th">Role</th>
                    <th class="rp-th">Team</th>
                    <th class="rp-th">Activity</th>
                    <th class="rp-th">Related Task</th>
                    <th class="rp-th">Result</th>
                </tr>
            </thead>
            <tbody id="rpBodyActivity" class="divide-y divide-slate-100"></tbody>
        </table>
    </div>

    {{-- The two states a table can be in besides holding rows. There is no
         loading state: all three reports ship with the page, so a tab switch is
         a redraw rather than a fetch, and a spinner would be theatre. --}}
    <div id="rpEmpty" class="rp-empty px-5 py-14 text-center">
        <span class="iconify text-4xl text-slate-300" data-icon="mdi:file-search-outline"></span>
        <p class="text-sm font-bold text-slate-500 mt-2">Nothing matches these filters</p>
        <p class="text-[12px] text-slate-400 mt-1">Clear a filter or widen the date range to see more records.</p>
    </div>
    <div id="rpError" class="rp-empty px-5 py-14 text-center">
        <span class="iconify text-4xl text-rose-300" data-icon="mdi:alert-circle-outline"></span>
        <p class="text-sm font-bold text-slate-600 mt-2">This report could not be drawn</p>
        <p id="rpErrorText" class="text-[12px] text-slate-400 mt-1"></p>
    </div>

    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
        <p id="rpShowing" class="text-[12px] font-semibold text-slate-500"></p>
        <div class="flex items-center gap-2">
            <div id="rpPager" class="flex items-center gap-1"></div>
            <select id="rpPerPage" class="rp-field text-[12px]" onchange="rpSetPerPage(this.value)">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
    </div>
</div>

<p class="mt-4 rounded-2xl border border-brand/15 bg-brand-soft px-4 py-3 text-[12px] text-brand/90 flex flex-wrap items-center gap-2">
    <span class="iconify text-base" data-icon="mdi:information-outline"></span>
    The activities counted in each role badge are the ones that role actually handed in. Roles still working on a task carry no badge until they submit.
    <span class="ml-auto text-slate-400 font-semibold">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</span>
</p>

{{-- ═══════ Details modal ═══════ --}}
<div id="rpModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="rpCloseModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full flex flex-col"
         style="max-width: min(56rem, 95vw); max-height: min(88vh, 900px);">
        <div class="bg-brand-soft px-5 py-4 border-b border-brand/10 flex items-start justify-between gap-3 rounded-t-2xl shrink-0">
            <div class="min-w-0">
                <h4 id="rpModalTitle" class="font-extrabold text-slate-900 text-base truncate">Task details</h4>
                <p id="rpModalMeta" class="text-[12px] text-slate-500 mt-0.5 truncate"></p>
            </div>
            <button type="button" onclick="rpCloseModal()"
                    class="text-slate-400 hover:text-brand w-8 h-8 rounded-full transition flex items-center justify-center shrink-0">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        <div id="rpModalBody" class="overflow-y-auto flex-1 p-5 space-y-4"></div>
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 rounded-b-2xl flex justify-end shrink-0">
            <button type="button" onclick="rpCloseModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-white transition">Close</button>
        </div>
    </div>
</div>

<script>
/* ── The three reports, as the server read them ──────────────────────────── */
const RP_DATA = {
    completed: @json($completedRows),
    students: @json($studentRows),
    activity: @json($activityRows),
};

const RP_PANELS = {
    completed: {
        title: 'Completed Tasks by Team',
        hint: 'List of tasks that have been completed by all teams in the system.',
        noun: 'record',
    },
    students: {
        title: 'Student Performance',
        hint: 'Every student holding a role, and how much of their assigned work is in.',
        noun: 'student',
    },
    activity: {
        title: 'Activity Logs',
        hint: 'What each person did, in the order it happened.',
        noun: 'entry',
    },
};

let rpTab = 'completed';
let rpPage = 1;
let rpPerPage = 10;
let rpFiltered = [];

const rpEl = (id) => document.getElementById(id);
const rpEsc = (value) => String(value === null || value === undefined ? '' : value)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

/* Every field a row can be searched by, flattened once per row. */
function rpHaystack(row) {
    if (row.__hay) return row.__hay;
    const parts = [];
    const walk = (value) => {
        if (value === null || value === undefined) return;
        if (Array.isArray(value)) { value.forEach(walk); return; }
        if (typeof value === 'object') { Object.values(value).forEach(walk); return; }
        parts.push(String(value));
    };
    walk(row);
    row.__hay = parts.join(' ').toLowerCase();
    return row.__hay;
}

/* The dates a row can be filtered on, per report. */
function rpRowDates(row) {
    if (rpTab === 'completed') return [row.assigned_sort, row.completed_sort];
    if (rpTab === 'activity') return [row.at_sort];
    return [];
}

function rpRowRoles(row) {
    if (Array.isArray(row.roles)) return row.roles.map((r) => r.label);
    if (row.role) return [row.role];
    return [];
}

function rpRowStatus(row) {
    return row.status || row.result || '';
}

function rpApply() {
    try {
        const term = (rpEl('rpSearch').value || '').trim().toLowerCase();
        const team = rpEl('rpTeam').value;
        const task = rpEl('rpTask').value;
        const role = rpEl('rpRole').value;
        const status = rpEl('rpStatus').value;
        const from = rpEl('rpFrom').value ? Math.floor(new Date(rpEl('rpFrom').value + 'T00:00:00').getTime() / 1000) : null;
        const to = rpEl('rpTo').value ? Math.floor(new Date(rpEl('rpTo').value + 'T23:59:59').getTime() / 1000) : null;

        rpFiltered = (RP_DATA[rpTab] || []).filter((row) => {
            if (term && !rpHaystack(row).includes(term)) return false;
            if (team && row.team_name !== team) return false;
            if (task && rpTab === 'completed' && row.task_title !== task) return false;
            if (role && !rpRowRoles(row).includes(role)) return false;
            if (status && rpRowStatus(row) !== status) return false;

            if (from !== null || to !== null) {
                const dates = rpRowDates(row).filter((d) => typeof d === 'number' && d > 0);
                // A report with no date of its own is not hidden by a date filter;
                // it simply has nothing to be judged on.
                if (dates.length) {
                    const inRange = dates.some((d) => (from === null || d >= from) && (to === null || d <= to));
                    if (!inRange) return false;
                }
            }

            return true;
        });

        rpPage = 1;
        rpRender();
        rpEl('rpError').style.display = 'none';
    } catch (err) {
        console.error(err);
        rpEl('rpErrorText').textContent = err.message || 'Something went wrong while filtering.';
        rpEl('rpError').style.display = 'block';
    }
}

function rpRender() {
    const total = rpFiltered.length;
    const pages = Math.max(1, Math.ceil(total / rpPerPage));
    if (rpPage > pages) rpPage = pages;

    const start = (rpPage - 1) * rpPerPage;
    const slice = rpFiltered.slice(start, start + rpPerPage);

    const body = rpEl(rpTab === 'completed' ? 'rpBodyCompleted' : (rpTab === 'students' ? 'rpBodyStudents' : 'rpBodyActivity'));
    body.innerHTML = slice.map((row, i) => rpRowHtml(row, start + i + 1)).join('');

    rpEl('rpEmpty').style.display = total === 0 ? 'block' : 'none';
    document.querySelectorAll('[data-rp-table]').forEach((table) => {
        const on = table.dataset.rpTable === rpTab && total > 0;
        table.classList.toggle('hidden', !on);
    });

    const noun = RP_PANELS[rpTab].noun;
    const all = (RP_DATA[rpTab] || []).length;
    rpEl('rpCount').textContent = total === all
        ? 'Showing ' + total + ' ' + noun + (total === 1 ? '' : 's')
        : 'Showing ' + total + ' of ' + all + ' ' + noun + (all === 1 ? '' : 's');
    rpEl('rpShowing').textContent = total === 0
        ? 'No records to show'
        : 'Showing ' + (start + 1) + ' to ' + Math.min(start + rpPerPage, total) + ' of ' + total + ' records';

    rpRenderPager(pages);
}

function rpRenderPager(pages) {
    const nav = rpEl('rpPager');
    const btn = (label, page, disabled, active) =>
        '<button type="button" ' + (disabled ? 'disabled' : 'onclick="rpGo(' + page + ')"') +
        ' class="w-8 h-8 rounded-lg border text-[12px] font-bold transition ' +
        (active ? 'bg-brand text-white border-brand' : 'border-slate-200 text-slate-500 hover:border-brand/40 hover:text-brand') +
        (disabled ? ' opacity-40 cursor-default' : '') + '">' + label + '</button>';

    const parts = [btn('‹', rpPage - 1, rpPage <= 1, false)];
    // A window around the current page: twenty numbered buttons is not a pager.
    const first = Math.max(1, Math.min(rpPage - 2, pages - 4));
    const last = Math.min(pages, first + 4);
    for (let p = first; p <= last; p++) parts.push(btn(String(p), p, false, p === rpPage));
    parts.push(btn('›', rpPage + 1, rpPage >= pages, false));

    nav.innerHTML = parts.join('');
}

function rpGo(page) { rpPage = page; rpRender(); }
function rpSetPerPage(value) { rpPerPage = parseInt(value, 10) || 10; rpPage = 1; rpRender(); }

/* ── Row templates ──────────────────────────────────────────────────────── */
const RP_STATUS_TINT = {
    'Completed': 'bg-emerald-50 text-emerald-700',
    'Approved': 'bg-emerald-50 text-emerald-700',
    'Submitted': 'bg-sky-50 text-sky-700',
    'In Progress': 'bg-amber-50 text-amber-700',
    'Overdue': 'bg-rose-50 text-rose-700',
    'Awaiting review': 'bg-sky-50 text-sky-700',
    'On track': 'bg-indigo-50 text-indigo-700',
    'Behind': 'bg-rose-50 text-rose-700',
    'No work yet': 'bg-slate-100 text-slate-500',
};

const RP_ROLE_TINT = {
    'Front Desk': 'bg-sky-50 text-sky-700',
    'Room Management': 'bg-brand-soft text-brand',
    'Restaurant': 'bg-amber-50 text-amber-700',
    'Housekeeping': 'bg-teal-50 text-teal-700',
    'Maintenance': 'bg-violet-50 text-violet-700',
};

function rpStatusChip(status) {
    const tint = RP_STATUS_TINT[status] || 'bg-slate-100 text-slate-500';
    return '<span class="rp-chip ' + tint + '"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>' + rpEsc(status) + '</span>';
}

function rpRowHtml(row, index) {
    if (rpTab === 'completed') return rpCompletedRow(row, index);
    if (rpTab === 'students') return rpStudentRow(row, index);
    return rpActivityRow(row, index);
}

function rpCompletedRow(row, index) {
    const roles = (row.roles || []).map((r) =>
        '<span class="rp-chip ' + (RP_ROLE_TINT[r.label] || 'bg-slate-100 text-slate-600') + '">' +
        rpEsc(r.label) + ' (' + r.count + ')</span>').join(' ') || '<span class="text-slate-300 text-xs">—</span>';

    return '<tr class="rp-row">' +
        '<td class="rp-td text-slate-400 font-semibold">' + index + '</td>' +
        '<td class="rp-td"><p class="font-bold text-slate-800">' + rpEsc(row.team_name) + '</p>' +
            '<p class="text-[11px] text-slate-400">' + row.member_count + ' member' + (row.member_count === 1 ? '' : 's') + '</p></td>' +
        '<td class="rp-td"><p class="font-extrabold text-slate-800 text-[12px] tracking-wide">' + rpEsc(row.task_title) + '</p>' +
            '<p class="text-[11px] text-slate-400 line-clamp-2">' + rpEsc(row.task_description) + '</p></td>' +
        '<td class="rp-td"><div class="flex flex-wrap gap-1">' + roles + '</div></td>' +
        '<td class="rp-td text-center font-extrabold text-slate-700">' + row.total_activities + '</td>' +
        '<td class="rp-td whitespace-nowrap">' + rpEsc(row.assigned_date) + '</td>' +
        '<td class="rp-td whitespace-nowrap">' + rpEsc(row.completed_date) + '</td>' +
        '<td class="rp-td">' + rpStatusChip(row.status) + '</td>' +
        '<td class="rp-td"><div class="flex items-center gap-1.5">' +
            /* The id travels on the element, not through a hand-quoted onclick:
               a team name with an apostrophe in it would close the attribute. */
            '<button type="button" data-rp-view="' + rpEsc(row.id) + '" ' +
            'class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold text-brand bg-brand-soft hover:bg-brand/10 transition">' +
            '<span class="iconify text-sm" data-icon="mdi:eye-outline"></span> View</button>' +
            '<button type="button" title="More" data-rp-view="' + rpEsc(row.id) + '" ' +
            'class="w-7 h-7 rounded-lg border border-slate-200 text-slate-400 hover:text-brand hover:border-brand/40 transition inline-flex items-center justify-center">' +
            '<span class="iconify text-base" data-icon="mdi:dots-vertical"></span></button>' +
        '</div></td>' +
    '</tr>';
}

function rpStudentRow(row, index) {
    const roles = (row.roles || []).map((r) =>
        '<span class="rp-chip ' + (RP_ROLE_TINT[r.label] || 'bg-slate-100 text-slate-600') + '">' + rpEsc(r.label) + '</span>'
    ).join(' ') || '<span class="text-slate-300 text-xs">No role</span>';

    return '<tr class="rp-row">' +
        '<td class="rp-td text-slate-400 font-semibold">' + index + '</td>' +
        '<td class="rp-td"><p class="font-bold text-slate-800">' + rpEsc(row.student_name) + '</p>' +
            '<p class="text-[11px] text-slate-400">' + rpEsc(row.student_no) + '</p></td>' +
        '<td class="rp-td font-semibold text-slate-600">' + rpEsc(row.team_name) + '</td>' +
        '<td class="rp-td"><div class="flex flex-wrap gap-1">' + roles + '</div></td>' +
        '<td class="rp-td text-center font-bold text-slate-700">' + row.assigned + '</td>' +
        '<td class="rp-td text-center font-bold text-sky-600">' + row.submitted + '</td>' +
        '<td class="rp-td text-center font-bold text-emerald-600">' + row.approved + '</td>' +
        '<td class="rp-td text-center font-bold text-amber-600">' + row.pending + '</td>' +
        '<td class="rp-td"><div class="flex items-center gap-2 min-w-[110px]">' +
            '<span class="text-[12px] font-extrabold text-slate-700 w-9">' + row.percent + '%</span>' +
            '<span class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">' +
            '<span class="block h-full rounded-full brand-gradient" style="width:' + row.percent + '%"></span></span>' +
        '</div></td>' +
        '<td class="rp-td">' + rpStatusChip(row.status) + '</td>' +
    '</tr>';
}

function rpActivityRow(row, index) {
    return '<tr class="rp-row">' +
        '<td class="rp-td text-slate-400 font-semibold">' + index + '</td>' +
        '<td class="rp-td whitespace-nowrap font-semibold text-slate-600">' + rpEsc(row.at) + '</td>' +
        '<td class="rp-td font-bold text-slate-800">' + rpEsc(row.user_name) + '</td>' +
        '<td class="rp-td">' + rpEsc(row.role) + '</td>' +
        '<td class="rp-td">' + rpEsc(row.team_name) + '</td>' +
        '<td class="rp-td"><p class="font-semibold text-slate-700">' + rpEsc(row.activity) + '</p>' +
            '<p class="text-[11px] text-slate-400 line-clamp-2">' + rpEsc(row.description) + '</p></td>' +
        '<td class="rp-td">' + (row.task ? rpEsc(row.task) : '<span class="text-slate-300">—</span>') + '</td>' +
        '<td class="rp-td">' + rpStatusChip(row.result) + '</td>' +
    '</tr>';
}

/* ── Tabs ───────────────────────────────────────────────────────────────── */
function rpSwitchTab(tab) {
    rpTab = tab;
    document.querySelectorAll('[data-rp-tab]').forEach((btn) => {
        btn.classList.toggle('is-active', btn.dataset.rpTab === tab);
    });
    // The task filter only means something on the completed report.
    document.querySelectorAll('[data-rp-only]').forEach((el) => {
        el.classList.toggle('hidden', el.dataset.rpOnly !== tab);
    });
    rpEl('rpPanelTitle').textContent = RP_PANELS[tab].title;
    rpEl('rpPanelHint').textContent = RP_PANELS[tab].hint;
    rpApply();
}

function rpClearFilters() {
    ['rpSearch', 'rpTeam', 'rpTask', 'rpRole', 'rpStatus', 'rpFrom', 'rpTo'].forEach((id) => { rpEl(id).value = ''; });
    rpApply();
}

/* ── Details ────────────────────────────────────────────────────────────── */
function rpOpenModal(id) {
    const row = (RP_DATA.completed || []).find((r) => r.id === id);
    if (!row) return;

    rpEl('rpModalTitle').textContent = row.task_title + ' — ' + row.team_name;
    rpEl('rpModalMeta').textContent = row.faculty_name + ' · assigned ' + row.assigned_date + ' · completed ' + row.completed_date;

    const members = (row.members || []).map((m) =>
        '<div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-50 last:border-0">' +
        '<span class="text-[13px] font-semibold text-slate-700">' + rpEsc(m.name) + '</span>' +
        '<span class="flex flex-wrap gap-1 justify-end">' + (m.roles || []).map((r) =>
            '<span class="rp-chip ' + (RP_ROLE_TINT[r] || 'bg-slate-100 text-slate-600') + '">' + rpEsc(r) + '</span>').join('') +
        '</span></div>').join('') || '<p class="text-[12px] text-slate-400">No members recorded.</p>';

    // Grouped by role, because that is the shape of the task itself.
    const byRole = {};
    (row.activities || []).forEach((a) => {
        (byRole[a.role_label || 'Other'] = byRole[a.role_label || 'Other'] || []).push(a);
    });

    const activities = Object.keys(byRole).map((label) =>
        '<div class="rounded-xl border border-slate-200 overflow-hidden">' +
        '<div class="px-3 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between">' +
            '<span class="rp-chip ' + (RP_ROLE_TINT[label] || 'bg-slate-100 text-slate-600') + '">' + rpEsc(label) + '</span>' +
            '<span class="text-[11px] font-bold text-slate-400">' + byRole[label].length + ' activit' + (byRole[label].length === 1 ? 'y' : 'ies') + '</span>' +
        '</div>' +
        byRole[label].map((a) =>
            '<div class="px-3 py-2.5 border-b border-slate-50 last:border-0">' +
            '<div class="flex flex-wrap items-start justify-between gap-2">' +
                '<div class="min-w-0">' +
                    '<p class="text-[13px] font-bold text-slate-800">' + rpEsc(a.title) + '</p>' +
                    '<p class="text-[11px] text-slate-400">' + rpEsc(a.student_name) + '</p>' +
                '</div>' + rpStatusChip(a.status) +
            '</div>' +
            '<p class="text-[11px] text-slate-500 mt-1.5">' +
                'Assigned ' + rpEsc(a.assigned_date || '—') +
                (a.submitted_date ? ' · Submitted ' + rpEsc(a.submitted_date) : '') +
                (a.reviewed_date ? ' · Reviewed ' + rpEsc(a.reviewed_date) : '') +
            '</p>' +
            ((a.steps || []).length
                ? '<ul class="mt-1.5 space-y-0.5">' + a.steps.map((s) =>
                    '<li class="text-[11px] ' + (s.done ? 'text-slate-400 line-through' : 'text-slate-600') + '">' +
                    (s.done ? '✓ ' : '• ') + rpEsc(s.text) + '</li>').join('') + '</ul>'
                : '') +
            (a.feedback
                ? '<div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5">' +
                  '<p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Faculty feedback</p>' +
                  '<p class="text-[11px] text-amber-800 mt-0.5 whitespace-pre-line">' + rpEsc(a.feedback) + '</p></div>'
                : '') +
            (a.has_comparison
                ? '<p class="text-[11px] text-slate-400 mt-1.5"><span class="iconify align-[-2px]" data-icon="mdi:compare-horizontal"></span> ' +
                  'Before / After / Changes available in the faculty review of this activity.</p>'
                : '') +
            '</div>').join('') +
        '</div>').join('') || '<p class="text-[12px] text-slate-400">No activities recorded.</p>';

    rpEl('rpModalBody').innerHTML =
        '<div class="grid gap-4 sm:grid-cols-2">' +
            '<div class="rounded-xl border border-slate-200 p-3">' +
                '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Team</p>' +
                '<p class="text-sm font-extrabold text-slate-800">' + rpEsc(row.team_name) + '</p>' +
                '<p class="text-[11px] text-slate-400 mb-2">' + row.member_count + ' members · ' + rpEsc(row.faculty_name) + '</p>' +
                members +
            '</div>' +
            '<div class="rounded-xl border border-slate-200 p-3">' +
                '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Task</p>' +
                '<p class="text-sm font-extrabold text-slate-800">' + rpEsc(row.task_title) + '</p>' +
                '<p class="text-[12px] text-slate-500 mt-1">' + rpEsc(row.task_description) + '</p>' +
                '<div class="flex flex-wrap gap-1.5 mt-3">' + (row.roles || []).map((r) =>
                    '<span class="rp-chip ' + (RP_ROLE_TINT[r.label] || 'bg-slate-100 text-slate-600') + '">' +
                    rpEsc(r.label) + ' (' + r.count + ')</span>').join('') + '</div>' +
                '<p class="text-[11px] text-slate-400 mt-3">' + row.total_activities + ' activities handed in · ' + rpEsc(row.status) + '</p>' +
            '</div>' +
        '</div>' +
        '<div class="space-y-3">' +
            '<p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Activities by role</p>' +
            activities +
        '</div>';

    const modal = rpEl('rpModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function rpCloseModal() {
    const modal = rpEl('rpModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', (e) => { if (e.key === 'Escape') rpCloseModal(); });

// Delegated: the rows are redrawn on every filter and page turn, so binding to
// the table once outlives all of them.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-rp-view]');
    if (btn) rpOpenModal(btn.dataset.rpView);
});

/* ── Export ─────────────────────────────────────────────────────────────── */
const RP_EXPORT_COLUMNS = {
    completed: [
        ['Team', (r) => r.team_name],
        ['Members', (r) => r.member_count],
        ['Task', (r) => r.task_title],
        ['Activities', (r) => r.task_description],
        ['Included Roles', (r) => (r.roles || []).map((x) => x.label + ' (' + x.count + ')').join('; ')],
        ['Total Activities', (r) => r.total_activities],
        ['Assigned Date', (r) => r.assigned_date],
        ['Date Completed', (r) => r.completed_date],
        ['Status', (r) => r.status],
        ['Faculty', (r) => r.faculty_name],
    ],
    students: [
        ['Student', (r) => r.student_name],
        ['Student No.', (r) => r.student_no],
        ['Team', (r) => r.team_name],
        ['Roles', (r) => (r.roles || []).map((x) => x.label).join('; ')],
        ['Assigned', (r) => r.assigned],
        ['Submitted', (r) => r.submitted],
        ['Approved', (r) => r.approved],
        ['Pending', (r) => r.pending],
        ['Completion %', (r) => r.percent],
        ['Status', (r) => r.status],
    ],
    activity: [
        ['Date & Time', (r) => r.at],
        ['User', (r) => r.user_name],
        ['Role', (r) => r.role],
        ['Team', (r) => r.team_name],
        ['Activity', (r) => r.activity],
        ['Description', (r) => r.description],
        ['Related Task', (r) => r.task],
        ['Result', (r) => r.result],
    ],
};

function rpCsvCell(value) {
    const text = value === null || value === undefined ? '' : String(value);
    return '"' + text.replace(/"/g, '""') + '"';
}

/* What is on screen after the filters, never the whole table — and the filters
   themselves are written into the file, so a printed report says what it is. */
function rpExport() {
    const columns = RP_EXPORT_COLUMNS[rpTab];
    const applied = [
        ['Search', rpEl('rpSearch').value],
        ['Team', rpEl('rpTeam').value],
        ['Task', rpTab === 'completed' ? rpEl('rpTask').value : ''],
        ['Role', rpEl('rpRole').value],
        ['Status', rpEl('rpStatus').value],
        ['From', rpEl('rpFrom').value],
        ['To', rpEl('rpTo').value],
    ].filter((pair) => pair[1]);

    const lines = [];
    lines.push([rpCsvCell('HMS ' + RP_PANELS[rpTab].title)].join(','));
    lines.push([rpCsvCell('Generated'), rpCsvCell(new Date().toLocaleString())].join(','));
    lines.push([rpCsvCell('Records'), rpCsvCell(rpFiltered.length)].join(','));
    lines.push([rpCsvCell('Filters applied'),
        rpCsvCell(applied.length ? applied.map((p) => p[0] + ': ' + p[1]).join(' | ') : 'None')].join(','));
    lines.push('');
    lines.push(columns.map((c) => rpCsvCell(c[0])).join(','));
    rpFiltered.forEach((row) => lines.push(columns.map((c) => rpCsvCell(c[1](row))).join(',')));

    // The BOM is what makes Excel read the accents and the middot correctly.
    const blob = new Blob(['\\ufeff' + lines.join('\\r\\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'hms-' + rpTab + '-report-' + new Date().toISOString().slice(0, 10) + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

/* ── Start ──────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    rpSwitchTab('completed');
});
</script>
@endsection
