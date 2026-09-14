<aside class="h-full flex flex-col text-zinc-200">
    {{-- No scrollbar of its own: Assigned Tasks pages at 4 cards precisely so
         this panel never has to grow past the sidebar's own height. --}}
    <div class="px-5 py-5 flex-1 overflow-hidden">
        {{-- The template number used to be a badge in the top header
             (FRONT DESK / Template 1); it lives here now, above the team it
             belongs to, since every member below already carries their own
             role label. Reuses utility classes the theme overrides already
             recolor for Template 2, so no extra CSS is needed per shell. --}}
        @if(!empty($selectedTemplate))
        <div class="mb-4 flex items-center gap-2">
            <span class="w-7 h-7 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center text-[11px] font-extrabold text-cyan-400">
                T{{ $selectedTemplate }}
            </span>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Template {{ $selectedTemplate }}</span>
        </div>
        @endif
        <div class="space-y-4">
            @foreach($groupMembers as $member)
                @php
                    $isMe = (int) ($member->id ?? 0) === (int) (auth()->id() ?? 0);
                    $rolesLabel = implode(', ', array_map(fn ($r) => ucwords(str_replace('_', ' ', $r)), $member->roles ?? []));
                @endphp
                <div class="flex items-start gap-3">
                    <div class="pt-1.5">
                        <span
                            data-member-online="{{ $member->id }}"
                            class="inline-block w-2.5 h-2.5 rounded-full {{ $isMe ? 'bg-emerald-400' : 'bg-zinc-600' }}"
                        ></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-white truncate">
                            {{ $member->name }}
                            @if($isMe)<span class="text-[10px] text-cyan-400 font-semibold ml-1">YOU</span>@endif
                        </p>
                        <p class="text-[11px] text-zinc-500 mt-0.5 truncate">{{ $rolesLabel !== '' ? $rolesLabel : 'No role' }}</p>
                        <p class="text-[10px] mt-1 {{ $isMe ? 'text-emerald-400' : 'text-zinc-600' }}" data-member-online-label>{{ $isMe ? 'Online' : 'Offline' }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- The tasks faculty assigned this team, read off the same Task rows Set
             Task writes. Only the Template Editor shows it (department.blade.php
             passes showAssignedTasks); the list is drawn and kept current by
             renderAssignedTasks() / syncAssignedTasks() there, seeded from
             $assignedTasks so it is right on first paint. Paged at 4 a screen so
             a long list pages instead of growing the sidebar's own scrollbar. --}}
        @if($showAssignedTasks ?? false)
        <div class="mt-5 pt-4 border-t border-zinc-800">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Assigned Tasks</p>
            <div id="assignedTaskList" class="space-y-1.5"></div>
            <div id="assignedTaskPager" class="hidden items-center justify-between mt-2.5"></div>
            <script type="application/json" id="assignedTaskSeed">@json($assignedTasks ?? [])</script>
        </div>
        @endif

        {{-- Staff Tools belongs to Simulation, not the website editor. The editor
             (department.blade.php) passes showStaffTools=false so the two areas
             stop overlapping; ops-shell, which is Simulation itself, keeps the
             default and still shows the nav. --}}
        @if(($showStaffTools ?? true) && in_array($builderRole ?? null, ['front_desk', 'room_management', 'restaurant_management', 'maintenance', 'housekeeping'], true))
        <div class="mt-5 pt-4 border-t border-zinc-800">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Staff Tools</p>
            <div class="space-y-1.5">
                @if(($builderRole ?? null) === 'front_desk')
                <a href="{{ route('students.frontdesk.verify-guest') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.verify-guest') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-user-check text-[13px] text-emerald-400"></i> Guest Information
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'verify-guest'])
                </a>
                <a href="{{ route('students.frontdesk.dine-in') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.dine-in') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-utensils text-[13px] text-emerald-400"></i> Dine-in Tables
                </a>
                <a href="{{ route('students.frontdesk.room-service') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.room-service') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-bell-concierge text-[13px] text-emerald-400"></i> Room Service
                </a>
                <a href="{{ route('students.frontdesk.amenities') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.amenities') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-person-swimming text-[13px] text-emerald-400"></i> Amenities
                    {{-- Guests signed in and not yet signed back out. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'amenities'])
                </a>
                <a href="{{ route('students.frontdesk.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-comment-dots text-[13px] text-emerald-400"></i> Complaints
                    {{-- Resolved by the department: the guest still has to be told. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'complaints'])
                </a>
                <a href="{{ route('students.frontdesk.reports') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.frontdesk.reports') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-chart-line text-[13px] text-emerald-400"></i> Reports
                </a>
                @elseif(($builderRole ?? null) === 'maintenance')
                <a href="{{ route('students.maintenance.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.maintenance.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-screwdriver-wrench text-[13px] text-emerald-400"></i> Complaints / Concerns
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'complaints'])
                </a>
                @elseif(($builderRole ?? null) === 'housekeeping')
                <a href="{{ route('students.housekeeping.inspections') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.inspections') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-broom text-[13px] text-emerald-400"></i> Room Inspections
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'inspections'])
                </a>
                <a href="{{ route('students.housekeeping.addons') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.addons') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-cart-flatbed text-[13px] text-emerald-400"></i> Add-ons
                    {{-- Nothing left to lend until one comes back. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'addons'])
                </a>
                <a href="{{ route('students.housekeeping.amenities') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.amenities') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-person-swimming text-[13px] text-emerald-400"></i> Amenities
                    {{-- Broken facilities waiting on this desk: not yet reported, or repaired
                         and needing a look before they reopen. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'amenities'])
                </a>
                <a href="{{ route('students.housekeeping.complaints') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.housekeeping.complaints') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-comment-dots text-[13px] text-emerald-400"></i> Complaints / Concerns
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'complaints'])
                </a>
                @elseif(($builderRole ?? null) === 'room_management')
                <a href="{{ route('students.roommanagement.manage', ['nav' => 'manage-room']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    {{-- Anything that is not Guest Details lands on Manage Room now, including
                         the retired ?nav=rooms, so this highlights for all of it. --}}
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.roommanagement.manage') && request()->query('nav') !== 'guest-details' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-bed text-[13px] text-emerald-400"></i> Manage Room
                    {{-- Rooms sitting at Cleaning or Maintenance cannot be sold yet. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'manage-room'])
                </a>
                <a href="{{ route('students.roommanagement.manage', ['nav' => 'guest-details']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.roommanagement.manage') && request()->query('nav') === 'guest-details' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-user text-[13px] text-emerald-400"></i> Guest Details
                    {{-- Guests registered but not checked in yet. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'guest-details'])
                </a>
                @elseif(($builderRole ?? null) === 'restaurant_management')
                <a href="{{ route('students.restaurant.manage', ['nav' => 'manage-menu']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav', 'manage-menu') === 'manage-menu' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-utensils text-[13px] text-emerald-400"></i> Manage Menu
                    {{-- Dishes at zero stock: nobody can order them until restocked. --}}
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'manage-menu'])
                </a>
                <a href="{{ route('students.restaurant.manage', ['nav' => 'manage-tables']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav') === 'manage-tables' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-chair text-[13px] text-emerald-400"></i> Manage Tables
                </a>
                <a href="{{ route('students.restaurant.manage', ['nav' => 'catering-packages']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav') === 'catering-packages' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-champagne-glasses text-[13px] text-emerald-400"></i> Catering Packages
                </a>
                <a href="{{ route('students.restaurant.manage', ['nav' => 'orders']) }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.manage') && request()->query('nav') === 'orders' ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-receipt text-[13px] text-emerald-400"></i> Orders
                    @include('students.frontdesk.left-sidebar.badge', ['key' => 'orders'])
                </a>
                <a href="{{ route('students.restaurant.reports') }}"
                    onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
                    class="w-full h-10 px-3 rounded-lg text-sm font-semibold text-zinc-200 bg-zinc-800 border hover:border-emerald-500/50 hover:text-white transition flex items-center gap-2.5 {{ request()->routeIs('students.restaurant.reports') ? 'border-emerald-500/50 text-white' : 'border-zinc-700' }}">
                    <i class="fas fa-chart-line text-[13px] text-emerald-400"></i> Reports
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="px-5 py-4 border-t border-zinc-800 shrink-0 bg-zinc-950/80">
        <a id="backToTasksBtn" href="{{ route('students.dashboard', ['section' => 'tasks']) }}"
           onclick="return typeof confirmLeaveBuilder === 'function' ? confirmLeaveBuilder(event) : true"
           class="w-full inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-xs font-bold
                  bg-zinc-900 text-zinc-200 border border-zinc-700 hover:border-cyan-500/40 hover:text-white hover:bg-zinc-800 transition"
           title="Back to Tasks">
            <i class="fas fa-arrow-left text-[11px]"></i>
            Back to Tasks
        </a>
    </div>
</aside>
