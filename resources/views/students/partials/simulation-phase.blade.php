{{-- The team's Hotel Customization approval progress, and the door to the
     Hotel Simulation. Rendered on the dashboard's Tasks section and re-rendered
     by students.tasks.live, so a faculty verdict moves it without a reload.
     Only approvals count: see App\Support\SimulationPhase. --}}
@php
    $simPhase = \App\Support\SimulationPhase::progress(
        $groupMembership?->group_name,
        $groupMembership ? (int) $groupMembership->faculty_id : null
    );
    $simSeat = \App\Support\SimulationPhase::seatFor($groupMembership);
    $simEntry = $simSeat
        ? collect(\App\Support\HotelTemplateBuilder::modulesForRoles([], [$simSeat]))->firstWhere('simulation_url')
        : null;
@endphp
@if($groupMembership && $simPhase['required'] > 0)
    <div class="bg-white rounded-2xl border border-[#E7E1DD] shadow-sm p-5 space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#8A817A]">
                    Hotel Customization &middot; Hotel Simulation
                </p>
                <h3 class="text-base font-bold text-[#181818]">Team {{ $groupMembership->group_name }} — Hotel Customization</h3>
            </div>
            @if($simSeat)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-[#E7E1DD] bg-[#F5F2EF] text-[11px] font-bold text-slate-600">
                    <span class="iconify text-sm" data-icon="mdi:bell-ring-outline"></span>
                    Simulation role: {{ \App\Support\HotelTemplateBuilder::seatLabel($simSeat, \App\Support\HotelTemplateBuilder::PHASE_SIMULATION) }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['Required Tasks', $simPhase['required'], 'not_started'],
                ['Approved', $simPhase['approved'], 'completed'],
                ['Pending Review', $simPhase['pending'], 'pending'],
                ['Revision Required', $simPhase['revision'], 'revision'],
            ] as [$label, $count, $key])
                <div class="rounded-xl border border-[#E7E1DD] px-3 py-2.5">
                    <p class="text-xl font-extrabold status-text-{{ $key }}">{{ $count }}</p>
                    <p class="text-[11px] font-semibold text-slate-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div>
            <div class="flex items-center justify-between text-[12px] font-bold text-slate-600 mb-1.5">
                <span>Approval Progress</span>
                <span>{{ $simPhase['percent'] }}%</span>
            </div>
            <div class="h-2 rounded-full bg-[#F0ECE8] overflow-hidden">
                <div class="h-full rounded-full status-fill-completed" style="width: {{ $simPhase['percent'] }}%"></div>
            </div>
        </div>

        {{-- The Simulation is open at any time; the approvals above still run
             alongside it until every customization task is approved. --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-[#BBF7D0] bg-[#F0FDF4] p-4">
            <span class="iconify text-2xl status-text-completed shrink-0" data-icon="mdi:lock-open-variant-outline"></span>
            <div class="flex-1">
                <p class="text-sm font-bold text-[#181818]">Hotel Simulation Open</p>
                <p class="text-[12px] text-slate-600">
                    @if($simPhase['unlocked'])
                        All required customization tasks have been approved.
                    @else
                        You can run the Hotel Simulation now. Keep working on your customization tasks and submit them for Faculty approval.
                    @endif
                </p>
            </div>
            @if($simPhase['can_start'] && $simEntry)
                <a href="{{ $simEntry['simulation_url'] }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-gradient text-white text-xs font-bold shadow-lg shadow-brand/20 hover:opacity-90 active:scale-[0.98] transition">
                    <span class="iconify text-base" data-icon="mdi:play-circle-outline"></span>
                    Start Simulation
                </a>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E7E1DD] bg-white text-xs font-bold text-slate-400 cursor-not-allowed">
                    <span class="iconify text-base" data-icon="mdi:account-clock-outline"></span>
                    No simulation role assigned
                </span>
            @endif
        </div>
    </div>
@endif
