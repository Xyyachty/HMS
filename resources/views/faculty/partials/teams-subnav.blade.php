{{-- Shared Teams chrome (matches Teams list): Class tabs + Set Task + Add Team --}}
@php
    $teamsSubTab = $teamsSubTab ?? request('tab', 'teams');
    $classes = $classes ?? collect();
    $activeClass = $activeClass ?? null;
    $classCapacity = $classCapacity ?? 40;
    $teamCountsByClass = $teamCountsByClass ?? [];
    $isSetTask = $teamsSubTab === 'create_task';

    $classQuery = [];
    if (request()->filled('class')) {
        $classQuery['class'] = request('class');
    } elseif (!empty($activeClass?->letter)) {
        $classQuery['class'] = $activeClass->letter;
    }

    $setTaskHref = route('faculty.role', array_merge($classQuery, ['tab' => 'create_task']));
@endphp

<div class="px-4 pt-4 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2 min-w-0">
        @forelse($classes as $classTab)
            @php
                $taken = $classTab->seats_taken ?? 0;
                $cap = $classTab->capacity ?? $classCapacity;
                $isActiveClass = $activeClass && $activeClass->id === $classTab->id;
                $isClosed = $classTab->status === 'closed';
                $teamCount = $teamCountsByClass[$classTab->id] ?? 0;
            @endphp
            <a href="{{ route('faculty.role', ['class' => $classTab->letter, 'tab' => 'teams']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-t-xl text-sm font-bold border border-b-0 transition
               {{ $isActiveClass
                    ? 'bg-white text-brand border-slate-200 -mb-px relative z-10'
                    : 'bg-slate-50 text-slate-500 border-transparent hover:text-slate-700 hover:bg-slate-100' }}">
                <span>{{ $classTab->name }}</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                    {{ $teamCount }} {{ \Illuminate\Support\Str::plural('team', $teamCount) }}
                </span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full
                    {{ $isClosed ? 'bg-slate-200 text-slate-600' : 'bg-brand-soft text-brand' }}">
                    {{ $taken }}/{{ $cap }}
                </span>
                @if(!$isClosed)
                    <span class="text-[10px] uppercase tracking-wide text-emerald-600">Open</span>
                @endif
            </a>
        @empty
            <p class="text-sm text-slate-500 pb-3">No classes yet.</p>
        @endforelse
    </div>

    <div class="flex items-center gap-2 pb-3 shrink-0 ml-auto">
        <a href="{{ $setTaskHref }}"
           class="h-10 px-4 rounded-xl text-sm font-bold transition inline-flex items-center gap-2 whitespace-nowrap
           {{ $isSetTask
                ? 'bg-brand text-white shadow-md shadow-brand/20'
                : 'bg-white text-slate-600 border border-slate-200 hover:border-brand/40 hover:text-brand' }}">
            <span class="iconify text-base" data-icon="mdi:clipboard-plus-outline"></span>
            Set Task
        </a>
        <button type="button" onclick="openCreateTeamModal()"
            class="h-10 px-4 rounded-xl text-sm font-bold bg-brand text-white hover:opacity-95 transition shadow-md shadow-brand/20 inline-flex items-center gap-2 whitespace-nowrap">
            <span class="iconify text-base" data-icon="mdi:plus"></span>
            Add Team
        </button>
    </div>
</div>
