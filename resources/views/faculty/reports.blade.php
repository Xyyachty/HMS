@extends('faculty.layout.app')

@section('page_title', 'Manage Reports')
@section('reports_active', 'active')

@section('content')
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
                    <th class="text-left px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:12%">Final Grade</th>
                    <th class="text-center px-4 py-2.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-500" style="width:18%">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teamReports as $index => $report)
                    <tr class="hover:bg-slate-50/80">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 truncate" title="{{ $report['team_name'] }}">{{ $report['team_name'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $report['task_count'] }} completed task{{ $report['task_count'] === 1 ? '' : 's' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs font-semibold text-slate-600">
                            <span class="line-clamp-2" title="{{ $report['page_name'] }}">{{ $report['page_name'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $report['assigned_date'] }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $report['date_completed'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                {{ $report['final_grade'] }}
                            </span>
                        </td>
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
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
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
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Final Grade</p>
                    <p id="teamReportGrade" class="text-sm font-semibold text-amber-700 mt-0.5">TBA</p>
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
@endsection

@push('scripts')
<script>
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
        document.getElementById('teamReportGrade').textContent = report.final_grade || 'TBA';

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
