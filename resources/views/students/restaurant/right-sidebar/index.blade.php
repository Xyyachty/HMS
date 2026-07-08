<aside class="h-full flex flex-col text-zinc-200 overflow-hidden bg-zinc-900">
    <!-- Panel Header -->
    <div class="px-5 py-4 border-b border-zinc-800 shrink-0 bg-zinc-900">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <i class="fas fa-utensils text-white text-xs"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Design Panel</h2>
                    <p class="text-[10px] text-zinc-500 mt-0.5">Style &amp; customize elements</p>
                </div>
            </div>
            <button class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition-all" title="Reset all styles">
                <i class="fas fa-rotate-left text-[10px]"></i>
            </button>
        </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">

        <!-- Element Target -->
        <div class="px-5 py-3 border-b border-zinc-800/60 bg-zinc-900/50">
            <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-zinc-800/80 border border-zinc-700/50">
                <div class="w-6 h-6 rounded-md bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-mouse-pointer text-[9px] text-amber-400"></i>
                </div>
                <span class="text-xs text-zinc-400" id="selectedElement">Select an element to style</span>
            </div>
        </div>

        <!-- Restaurant Tasks -->
        <div class="px-5 py-4 border-b border-zinc-800/60">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-6 h-6 rounded-md bg-amber-500/15 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-[9px] text-amber-400"></i>
                </div>
                <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Tasks</span>
            </div>
            <div class="space-y-2">
                @forelse($tasks as $task)
                <div class="p-3 rounded-lg bg-zinc-800/50 border border-zinc-700/50">
                    <p class="text-xs font-bold text-white mb-1">{{ $task->title }}</p>
                    <p class="text-[10px] text-zinc-500">{{ $task->description ?? 'No description' }}</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[10px] px-2 py-0.5 rounded-full
                            {{ $task->priority === 'high' ? 'bg-red-500/10 text-red-400' : ($task->priority === 'medium' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-zinc-500/10 text-zinc-400') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                        @if($task->due_date)
                        <span class="text-[10px] text-zinc-600">
                            <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}
                        </span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-[10px] text-zinc-500 text-center py-4">No active tasks</p>
                @endforelse
            </div>
        </div>

        <!-- Bottom spacer -->
        <div class="h-4"></div>
    </div>

</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
</style>