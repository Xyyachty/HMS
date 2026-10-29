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
    // A seat can cover more than one role (Room & Housekeeping), so Start
    // Simulation becomes a menu when there is more than one page to open.
    $simEntries = $simSeats
        ? collect(\App\Support\HotelTemplateBuilder::modulesForRoles([], $simSeats))->filter(fn ($m) => !empty($m['simulation_url']))->values()
        : collect();
@endphp
@if($groupMembership && $simPhase['required'] > 0)
    <div data-tour="phase" class="bg-white rounded-2xl border border-[#E7E1DD] shadow-sm p-5 space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[0.625rem] font-bold uppercase tracking-wider text-[#8A817A]">
                    Hotel Customization &middot; Hotel Simulation
                </p>
                <h3 class="text-base font-bold text-[#181818]">{{ \App\Models\Group::label($groupMembership->group_name) }} — Hotel Customization</h3>
            </div>
            @if($simSeats)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-[#E7E1DD] bg-[#F5F2EF] text-[0.6875rem] font-bold text-slate-600">
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
                    <p class="text-[0.6875rem] font-semibold text-slate-500">{{ $label }}</p>
                </div>
            @endforeach
        </div>

        <div>
            <div class="flex items-center justify-between text-[0.75rem] font-bold text-slate-600 mb-1.5">
                <span>Approval Progress</span>
                <span>{{ $simPhase['percent'] }}%</span>
            </div>
            <div class="h-2 rounded-full bg-[#F0ECE8] overflow-hidden">
                <div class="h-full rounded-full status-fill-completed" style="width: {{ $simPhase['percent'] }}%"></div>
            </div>
        </div>

        {{-- The team's website, where guests book online. Front Desk opens it;
             until then /hotel/{slug} 404s (PublicSiteController::publishedTeam). --}}
        @php
            $siteOpen = (bool) \App\Models\GroupSettings::where('group_name', $groupMembership->group_name)
                ->where('faculty_id', $groupMembership->faculty_id)
                ->value('is_published');
            $siteSlug = \App\Models\Group::slugForTeam($groupMembership->group_name, (int) $groupMembership->faculty_id);
        @endphp
        {{-- Simulation on the left, Website on the right, equal widths; stacked on narrow screens. --}}
        <div class="grid grid-cols-1 {{ $siteSlug ? 'lg:grid-cols-2' : '' }} gap-3 items-stretch">
            {{-- Locked until faculty approves every customization task
                 (SimulationPhase::progress, EnsureSimulationUnlocked). --}}
            <div class="flex flex-col gap-3 rounded-xl border p-4 {{ $simPhase['can_start'] ? 'border-[#BBF7D0] bg-[#F0FDF4]' : 'border-[#FDE68A] bg-[#FFFBEB]' }}">
                <div class="flex items-start gap-3">
                    <span class="iconify text-2xl shrink-0 {{ $simPhase['can_start'] ? 'status-text-completed' : 'text-[#B45309]' }}"
                          data-icon="{{ $simPhase['can_start'] ? 'mdi:lock-open-variant-outline' : 'mdi:lock-outline' }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-[#181818]">{{ $simPhase['can_start'] ? 'Hotel Simulation Open' : 'Hotel Simulation Locked' }}</p>
                        <p class="text-[0.75rem] text-slate-600">
                            @if($simPhase['can_start'])
                                All required customization tasks have been approved.
                            @else
                                Opens once Faculty approves all {{ $simPhase['required'] }} customization tasks. {{ $simPhase['required'] - $simPhase['approved'] }} still waiting for approval.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-auto">
                    @if(!$simPhase['can_start'])
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#E7E1DD] bg-white text-xs font-bold text-slate-400 cursor-not-allowed">
                            <span class="iconify text-base" data-icon="mdi:lock-outline"></span>
                            Start Simulation
                        </span>
                    @elseif($simEntries->count() === 1)
                        <a href="{{ $simEntries[0]['simulation_url'] }}" data-tour="start-simulation"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-gradient text-white text-xs font-bold shadow-lg shadow-brand/20 hover:opacity-90 active:scale-[0.98] transition">
                            <span class="iconify text-base" data-icon="mdi:play-circle-outline"></span>
                            Start Simulation
                        </a>
                    @elseif($simEntries->count() > 1)
                        {{-- toggleTaskAreaMenu() lives on the dashboard and closes on an outside click. --}}
                        <div class="relative inline-block" data-task-area-menu>
                            <button type="button" onclick="toggleTaskAreaMenu('simulationStartMenu')" data-tour="start-simulation"
                                    aria-haspopup="true" aria-controls="simulationStartMenu"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-gradient text-white text-xs font-bold shadow-lg shadow-brand/20 hover:opacity-90 active:scale-[0.98] transition">
                                <span class="iconify text-base" data-icon="mdi:play-circle-outline"></span>
                                Start Simulation
                                <span class="iconify text-sm" data-icon="mdi:chevron-down"></span>
                            </button>
                            <div id="simulationStartMenu" hidden
                                 class="absolute left-0 mt-2 w-56 bg-white rounded-xl border border-[#E7E1DD] shadow-xl py-1.5 z-30">
                                <p class="px-3 pt-1 pb-1.5 text-[0.625rem] font-bold uppercase tracking-wider text-slate-400">Choose a role</p>
                                @foreach($simEntries as $module)
                                    <a href="{{ $module['simulation_url'] }}"
                                       class="flex items-center gap-2 px-3 py-2 text-[0.8125rem] font-semibold text-slate-600 hover:bg-[#F5F2EF] hover:text-[#181818]">
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

            @if($siteSlug)
                <div class="flex flex-col gap-3 rounded-xl border border-[#E7E1DD] bg-white p-4">
                    <div class="flex items-start gap-3">
                        <span class="iconify text-2xl shrink-0 {{ $siteOpen ? 'status-text-completed' : 'text-slate-400' }}" data-icon="mdi:web"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-[#181818]">
                                Hotel Website &middot; {{ $siteOpen ? 'Open to guests' : 'Closed' }}
                            </p>
                            @if($siteOpen)
                                <div class="flex items-center gap-2 mt-1.5">
                                    <input id="hotelSiteUrl" readonly value="{{ url('/hotel/' . $siteSlug) }}" onclick="this.select()"
                                           class="flex-1 min-w-0 rounded-lg border border-[#E7E1DD] bg-[#F5F2EF] px-2 py-1 text-[0.75rem] text-slate-600">
                                    <button type="button" title="Copy link"
                                            onclick="(function(b){var i=document.getElementById('hotelSiteUrl');i.select();navigator.clipboard&&navigator.clipboard.writeText(i.value);b.textContent='Copied';setTimeout(function(){b.textContent='Copy';},1200);})(this)"
                                            class="px-3 py-1 rounded-lg border border-[#E7E1DD] text-[0.75rem] font-semibold text-slate-600 hover:bg-[#F5F2EF]">Copy</button>
                                    <a href="{{ url('/hotel/' . $siteSlug) }}" target="_blank" rel="noopener"
                                       class="px-3 py-1 rounded-lg border border-[#E7E1DD] text-[0.75rem] font-semibold text-slate-600 hover:bg-[#F5F2EF]">Open</a>
                                </div>
                            @else
                                <p class="text-[0.75rem] text-slate-600">Guests can book online once Front Desk opens the hotel.</p>
                            @endif
                        </div>
                    </div>
                    @if(in_array('front_desk', $simSeats, true))
                        <form method="POST" action="{{ route('students.hotel-site.publish') }}" data-tour="hotel-site" class="mt-auto">
                            @csrf
                            <input type="hidden" name="publish" value="{{ $siteOpen ? 0 : 1 }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition {{ $siteOpen ? 'border border-[#E7E1DD] bg-white text-slate-600 hover:bg-[#F5F2EF]' : 'brand-gradient text-white shadow-lg shadow-brand/20 hover:opacity-90' }}">
                                <span class="iconify text-base" data-icon="{{ $siteOpen ? 'mdi:door-closed' : 'mdi:door-open' }}"></span>
                                {{ $siteOpen ? 'Close to guests' : 'Open hotel to guests' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
