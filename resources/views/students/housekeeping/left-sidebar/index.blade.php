<aside class="h-full flex flex-col text-zinc-200">
    <div class="px-5 py-5 border-b border-zinc-800/80">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-bold text-white tracking-wide uppercase">Team Members</h2>
                <p class="text-[11px] text-zinc-500 mt-1">Static team list for the housekeeping group</p>
            </div>
            <div class="w-9 h-9 rounded-xl bg-teal-500/15 border border-teal-400/20 flex items-center justify-center text-teal-300">
                <i class="fas fa-users text-sm"></i>
            </div>
        </div>

        <div class="settings-card mb-0">
            <span class="settings-label">Housekeeping Group</span>
            <p class="text-lg font-bold text-white">Team Members</p>
            <p class="text-[11px] text-zinc-500 mt-1">Static preview of each member and task slot</p>
        </div>
    </div>

    <div class="px-5 py-5 flex-1 overflow-y-auto">
        <div class="space-y-5">
            @foreach($groupMembers as $member)
            <div>
                <p class="text-sm font-bold text-white">{{ $member->name }} <span class="text-zinc-500 font-medium">({{ implode(', ', array_map(fn($r) => str_replace('_', ' ', $r), $member->roles)) }})</span></p>
                <p class="text-[11px] text-zinc-500 mt-1">Task</p>
                <div class="mt-3 h-px bg-zinc-700/80"></div>
            </div>
            @endforeach
        </div>
    </div>
</aside>