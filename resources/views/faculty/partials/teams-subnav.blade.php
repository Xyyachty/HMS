{{-- Shared Teams chrome (matches Teams list): Block tabs + Set Task + Team Setup --}}
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

    /* Why Set Task cannot be pressed, if it cannot. Worked out on the server -
       the same sentence guards the page itself, so the button and the page can
       never disagree about the reason. Null means there is somewhere to send a
       task and the button behaves as it always did. */
    $setTaskBlockReason = $setTaskBlockReason ?? null;
@endphp

<div class="px-4 pt-4 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2 min-w-0">
        @forelse($classes as $classTab)
            @php
                $taken = $classTab->seats_taken ?? 0;
                $cap = $classTab->capacity ?? $classCapacity;
                $isActiveClass = $activeClass && $activeClass->faculty_class_id === $classTab->faculty_class_id;
                $isClosed = $classTab->status === 'closed';
                $teamCount = $teamCountsByClass[$classTab->faculty_class_id] ?? 0;
            @endphp
            {{-- Stay on the tab you are on. Hard-coding 'teams' threw faculty out of
                 the Set Task wizard whenever they switched block. --}}
            <a href="{{ route('faculty.role', ['class' => $classTab->letter, 'tab' => $teamsSubTab]) }}"
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
            <p class="text-sm text-slate-500 pb-3">No blocks yet.</p>
        @endforelse
    </div>

    <div class="flex items-center gap-2 pb-3 shrink-0 ml-auto">
        @if($setTaskBlockReason)
            {{-- A button rather than a link: a disabled <a> is still a link, and
                 still goes where it points. The reason is the tooltip. --}}
            <button type="button" disabled
                    title="{{ $setTaskBlockReason }}"
                    aria-disabled="true"
                    aria-describedby="setTaskBlockedNote"
                    class="h-10 px-4 rounded-xl text-sm font-bold inline-flex items-center gap-2 whitespace-nowrap
                           bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed">
                <span class="iconify text-base" data-icon="mdi:clipboard-alert-outline"></span>
                Set Task
            </button>
        @else
            <a href="{{ $setTaskHref }}"
               data-action-btn="set_task"
               onclick="setTeamsActionHighlight('set_task')"
               class="teams-action-btn h-10 px-4 rounded-xl text-sm font-bold transition inline-flex items-center gap-2 whitespace-nowrap
               {{ $isSetTask
                    ? 'is-active bg-brand text-white shadow-md shadow-brand/20 border border-transparent'
                    : 'bg-white text-slate-600 border border-slate-200 hover:border-brand/40 hover:text-brand' }}">
                <span class="iconify text-base" data-icon="mdi:clipboard-plus-outline"></span>
                Set Task
            </a>
        @endif
        {{-- Team Setup, where Add Team used to be. They opened the same screen under
             two names in two places, which read as two different things to do.
             Deliberately not a .teams-action-btn: that class is driven by
             setTeamsActionHighlight(), which would paint over the gradient. --}}
        <button type="button" onclick="openCreateTeamModal()"
            title="Create new team(s) or modify teams"
            class="h-10 px-4 rounded-xl brand-gradient text-white text-sm font-bold transition inline-flex items-center gap-2 whitespace-nowrap shadow-md shadow-brand/20 hover:opacity-95">
            <span class="iconify text-base" data-icon="mdi:cog-outline"></span>
            <span class="iconify text-sm" data-icon="mdi:plus"></span>
            Team Setup
        </button>
    </div>
</div>

@if($setTaskBlockReason)
    {{-- Said on the page as well as in the tooltip: a tooltip is only found by
         someone who already suspects the button is disabled, and on a touch
         screen it is not found at all. --}}
    <div id="setTaskBlockedNote" role="status"
         class="px-4 py-3 bg-amber-50 border-b border-amber-200 flex items-start gap-2.5">
        <span class="iconify text-amber-500 text-lg mt-0.5 shrink-0" data-icon="mdi:information-outline"></span>
        <p class="text-xs text-amber-800 font-semibold leading-relaxed">{{ $setTaskBlockReason }}</p>
    </div>
@endif
