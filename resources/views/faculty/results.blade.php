@extends('faculty.layout.app')

@section('page_title', 'Student Results & Outputs')
@section('results_active', 'active')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Student Outputs</h3>
                <p class="text-sm text-slate-500 mt-1">Completed task assignments from your students.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $outputs->count() }} completed
                </span>
            </div>
        </div>

        <div class="mt-6 flex gap-2 border-b border-slate-200 -mb-6 pb-0 relative z-10 overflow-x-auto">
            <a href="{{ route('faculty.results') }}" class="px-5 py-3 text-xs font-bold text-white bg-brand rounded-t-xl border border-b-0 border-brand shadow-sm relative shrink-0">
                All Outputs
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-slate-900 text-white text-[10px] rounded-full flex items-center justify-center border-2 border-white">{{ $outputs->count() }}</span>
            </a>
            @foreach(['room_management' => 'Room Mgmt', 'front_desk' => 'Front Desk', 'restaurant_management' => 'Restaurant', 'maintenance' => 'Maintenance', 'housekeeping' => 'Housekeeping'] as $roleKey => $label)
                <span class="px-5 py-3 text-xs font-semibold text-slate-500 rounded-t-xl shrink-0">
                    {{ $label }}
                    <span class="ml-1 text-slate-400">({{ $countsByRole[$roleKey] ?? 0 }})</span>
                </span>
            @endforeach
        </div>
    </div>

    <div class="divide-y divide-slate-100">
        @forelse($outputs as $task)
            @php
                $user = $task->student?->user ?? $task->assignedTo;
                $displayName = trim(implode(' ', array_filter([
                    $user?->last_name,
                    $user?->first_name,
                ])));
                if ($displayName !== '' && $user?->last_name && $user?->first_name) {
                    $displayName = $user->last_name . ', ' . $user->first_name;
                } else {
                    $displayName = trim(implode(' ', array_filter([$user?->first_name, $user?->last_name])))
                        ?: ($user?->name ?? 'Student');
                }
                $priority = strtolower($task->priority ?? 'medium');
            @endphp
            <div class="p-6 hover:bg-brand-soft/20 transition flex flex-col md:flex-row gap-6 items-start md:items-center">
                <div class="flex items-center gap-4 w-full md:w-1/4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&background=DB2777&color=fff&size=40&font-size=0.4" class="w-12 h-12 rounded-xl shadow-md border-2 border-white" alt="{{ $displayName }}">
                    <div class="min-w-0">
                        <h4 class="font-bold text-slate-800 text-sm truncate">{{ $displayName }}</h4>
                        <p class="text-xs text-slate-400 font-mono">{{ $task->student?->student_id ? '#' . $task->student->student_id : ($user?->email ?? '—') }}</p>
                    </div>
                </div>

                <div class="flex-1 w-full md:w-2/4 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-brand-soft text-brand border border-brand/10">
                            {{ $roleLabels[$task->role] ?? ($task->role ?: 'General') }}
                        </span>
                        <span class="text-[10px] text-slate-400">Completed: {{ optional($task->updated_at)->format('M d, Y') }}</span>
                    </div>
                    <h5 class="font-semibold text-slate-700 text-sm">{{ $task->title }}</h5>
                    @if($task->description)
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $task->description }}</p>
                    @endif
                </div>

                <div class="w-full md:w-1/4 flex flex-col items-end gap-3">
                    <div class="text-right">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold
                            {{ $priority === 'high' ? 'bg-red-50 text-red-700 border border-red-100' : ($priority === 'low' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100') }}">
                            {{ ucfirst($priority) }} priority
                        </span>
                        <span class="block text-[10px] text-emerald-600 uppercase font-bold mt-2">Completed</span>
                    </div>
                    <a href="{{ route('faculty.activity', ['filter' => 'completed']) }}"
                       class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:border-brand transition flex items-center gap-1.5">
                        <span class="iconify" data-icon="mdi:eye-outline"></span> View Logs
                    </a>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <span class="iconify text-5xl text-slate-300" data-icon="mdi:file-document-outline"></span>
                <p class="mt-3 font-semibold text-slate-500">No completed outputs yet</p>
                <p class="text-xs text-slate-400 mt-1">When students finish assigned tasks, they will appear here.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
