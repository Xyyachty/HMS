{{-- The team's Hotel Customization approval progress, and the door to the
     Hotel Simulation. Rendered on the dashboard's Tasks section and re-rendered
     by students.tasks.live, so a faculty verdict moves it without a reload.
     Only approvals count: see App\Support\SimulationPhase. --}}
@php
    $simPhase = \App\Support\SimulationPhase::progress(
        $groupMembership?->group_name,
        $groupMembership ? (int) $groupMembership->faculty_id : null
    );
    // Several seats for a member of a team short of students.
    $simSeats = \App\Support\SimulationPhase::seatsFor($groupMembership);
    // A seat can cover more than one role (Room & Maintenance), so Start
    // Simulation becomes a menu when there is more than one page to open.
    $simEntries = $simSeats
        ? collect(\App\Support\HotelTemplateBuilder::modulesForRoles([], $simSeats))->filter(fn ($m) => !empty($m['simulation_url']))->values()
        : collect();
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
            @if($simSeats)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-[#E7E1DD] bg-[#F5F2EF] text-[11px] font-bold text-slate-600">
                    <span class="iconify text-sm" data-icon="mdi:bell-ring-outline"></span>
                    {{ count($simSeats) > 1 ? 'Simulation roles' : 'Simulation role' }}: {{ collect($simSeats)->map(fn ($seat) => \App\Support\HotelTemplateBuilder::seatLabel($seat, \App\Support\HotelTemplateBuilder::PHASE_SIMULATION))->implode(', ') }}
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
            @if($simPhase['can_start'] && $simEntries->count() === 1)
                <a href="{{ $simEntries[0]['simulation_url'] }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-gradient text-white text-xs font-bold shadow-lg shadow-brand/20 hover:opacity-90 active:scale-[0.98] transition">
                    <span class="iconify text-base" data-icon="mdi:play-circle-outline"></span>
                    Start Simulation
                </a>
            @elseif($simPhase['can_start'] && $simEntries->count() > 1)
                {{-- toggleTaskAreaMenu() lives on the dashboard and closes on an outside click. --}}
                <div class="relative" data-task-area-menu>
                    <button type="button" onclick="toggleTaskAreaMenu('simulationStartMenu')"
                            aria-haspopup="true" aria-controls="simulationStartMenu"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-gradient text-white text-xs font-bold shadow-lg shadow-brand/20 hover:opacity-90 active:scale-[0.98] transition">
                        <span class="iconify text-base" data-icon="mdi:play-circle-outline"></span>
                        Start Simulation
                        <span class="iconify text-sm" data-icon="mdi:chevron-down"></span>
                    </button>
                    <div id="simulationStartMenu" hidden
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-[#E7E1DD] shadow-xl py-1.5 z-30">
                        <p class="px-3 pt-1 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Choose a role</p>
                        @foreach($simEntries as $module)
                            <a href="{{ $module['simulation_url'] }}"
                               class="flex items-center gap-2 px-3 py-2 text-[13px] font-semibold text-slate-600 hover:bg-[#F5F2EF] hover:text-[#181818]">
                                {{ $module['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E7E1DD] bg-white text-xs font-bold text-slate-400 cursor-not-allowed">
                    <span class="iconify text-base" data-icon="mdi:account-clock-outline"></span>
                    No simulation role assigned
                </span>
            @endif
        </div>
    </div>
@endif
