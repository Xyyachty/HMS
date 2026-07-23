@extends('faculty.layout.app')

@section('page_title', 'Task Management')
@section('tasks_active', 'active')

@section('content')
<style>
    /* ── Priority badges ── */
    .badge-high   { background:#FEF2F2; color:#B91C1C; border:1px solid #FECACA; }
    .badge-medium { background:#FFFBEB; color:#B45309; border:1px solid #FDE68A; }
    .badge-low    { background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; }

    /* ── Role tab colours ── */
    .tab-btn { transition: all .25s ease; }
    .tab-btn.active-tab {
        background: linear-gradient(135deg,#F472B6,#DB2777,#9D174D);
        color:#fff; border-color:transparent;
        box-shadow: 0 8px 20px -4px rgba(219,39,119,.35);
    }

    /* ── Task card hover ── */
    .task-card { transition: all .2s ease; }
    .task-card:hover { box-shadow: 0 8px 24px -6px rgba(0,0,0,.08); transform:translateY(-1px); }

    /* ── Slide-in modal ── */
    #taskModalOverlay { transition: opacity .3s ease; }
    #taskModalPanel {
        transition: transform .35s cubic-bezier(.16,1,.3,1), opacity .35s ease;
        transform: translateX(100%);
    }
    #taskModalPanel.open { transform: translateX(0); }

    /* ── Empty state ring ── */
    @keyframes pulse-ring {
        0%,100%{ border-color:#e2e8f0; } 50%{ border-color:#F472B6; }
    }
    .empty-ring { animation: pulse-ring 3s ease-in-out infinite; }
</style>

{{-- ══════ PAGE HEADER ══════ --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Task Management</h2>
        <p class="text-sm text-slate-400 mt-1">Create and manage tasks for each student role department.</p>
    </div>
    <button onclick="openTaskModal(null)"
        class="inline-flex items-center gap-2 bg-brand text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:scale-105 transition shadow-lg shadow-brand/25">
        <span class="iconify text-base" data-icon="mdi:plus-circle-outline"></span>
        Create Task
    </button>
</div>

{{-- ══════ SUCCESS FLASH ══════ --}}
@if(session('success'))
<div id="flashBanner" class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-2xl text-sm font-semibold">
    <span class="iconify text-lg" data-icon="mdi:check-circle-outline"></span>
    {{ session('success') }}
    <button onclick="document.getElementById('flashBanner').remove()" class="ml-auto text-green-400 hover:text-green-600">
        <span class="iconify" data-icon="mdi:close"></span>
    </button>
</div>
@endif

{{-- ══════ ROLE TABS ══════ --}}
@php
    $roles = [
        'front_desk'            => ['label'=>'Front Desk',            'icon'=>'mdi:desk',                    'color'=>'text-rose-500',   'bg'=>'bg-rose-50'],
        'restaurant_management' => ['label'=>'Restaurant Management', 'icon'=>'mdi:silverware-fork-knife',   'color'=>'text-amber-500',  'bg'=>'bg-amber-50'],
        'room_management'       => ['label'=>'Room Management',       'icon'=>'mdi:bed-outline',             'color'=>'text-brand',      'bg'=>'bg-brand-soft'],
        'maintenance'           => ['label'=>'Maintenance',           'icon'=>'mdi:broom',                   'color'=>'text-plum-accent','bg'=>'bg-plum-soft'],
        'housekeeping'          => ['label'=>'Housekeeping Services', 'icon'=>'mdi:sparkles',                'color'=>'text-teal-500',   'bg'=>'bg-teal-50'],
    ];
    $activeTab = request('tab', 'front_desk');
    if (!array_key_exists($activeTab, $roles)) $activeTab = 'front_desk';
@endphp

<div class="flex gap-2 mb-6 overflow-x-auto pb-1 flex-wrap">
    @foreach($roles as $roleKey => $roleMeta)
    <a href="{{ route('faculty.tasks', ['tab' => $roleKey]) }}"
        class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-500 whitespace-nowrap
               {{ $activeTab === $roleKey ? 'active-tab' : 'bg-white hover:bg-slate-50 hover:text-slate-700' }}">
        <span class="iconify text-base {{ $activeTab === $roleKey ? 'text-white' : $roleMeta['color'] }}"
              data-icon="{{ $roleMeta['icon'] }}"></span>
        {{ $roleMeta['label'] }}
        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold
                     {{ $activeTab === $roleKey ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
            {{ $taskCounts[$roleKey] ?? 0 }}
        </span>
    </a>
    @endforeach
</div>

{{-- ══════ TASK LIST ══════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 {{ $roles[$activeTab]['bg'] }} rounded-xl flex items-center justify-center">
                <span class="iconify text-lg {{ $roles[$activeTab]['color'] }}"
                      data-icon="{{ $roles[$activeTab]['icon'] }}"></span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">{{ $roles[$activeTab]['label'] }} Tasks</h3>
                <p class="text-xs text-slate-400">{{ $taskCounts[$activeTab] ?? 0 }} active task(s)</p>
            </div>
        </div>
        <button onclick="openTaskModal('{{ $activeTab }}')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand text-white text-xs font-bold hover:scale-105 transition shadow-sm shadow-brand/20">
            <span class="iconify" data-icon="mdi:plus"></span> Add Task
        </button>
    </div>

    {{-- Task rows --}}
    <div class="divide-y divide-slate-50">
        @forelse(($tasksByRole[$activeTab] ?? collect()) as $task)
        <div class="task-card flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 group">

            {{-- Main info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 truncate">{{ $task->title }}</p>
                @if($task->description)
                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ $task->description }}</p>
                @endif
            </div>

            {{-- Meta --}}
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                {{-- Due date --}}
                @if($task->due_date)
                <div class="flex items-center gap-1 text-xs text-slate-400">
                    <span class="iconify" data-icon="mdi:calendar-outline"></span>
                    <span class="{{ $task->due_date->isPast() ? 'text-red-500 font-semibold' : '' }}">
                        {{ $task->due_date->format('M d, Y') }}
                    </span>
                </div>
                @endif

                {{-- Created at --}}
                <div class="flex items-center gap-1 text-xs text-slate-300">
                    <span class="iconify" data-icon="mdi:clock-outline"></span>
                    {{ $task->created_at->diffForHumans() }}
                </div>

                {{-- Delete --}}
                <form method="POST" action="{{ route('faculty.tasks.destroy', $task) }}"
                      onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                        <span class="iconify text-base" data-icon="mdi:trash-can-outline"></span>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-20 gap-4 text-center">
            <div class="empty-ring w-20 h-20 rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center">
                <span class="iconify text-3xl text-slate-300" data-icon="mdi:clipboard-text-outline"></span>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-400">No tasks yet for {{ $roles[$activeTab]['label'] }}</p>
                <p class="text-xs text-slate-300 mt-1">Click "Add Task" to create the first one.</p>
            </div>
            <button onclick="openTaskModal('{{ $activeTab }}')"
                class="mt-2 inline-flex items-center gap-2 px-5 py-2.5 bg-brand text-white text-xs font-bold rounded-xl hover:scale-105 transition shadow-md shadow-brand/20">
                <span class="iconify" data-icon="mdi:plus"></span> Create First Task
            </button>
        </div>
        @endforelse
    </div>
</div>


{{-- ══════════════════════════════════════
     CREATE TASK SLIDE-IN PANEL
══════════════════════════════════════ --}}
<div id="taskModalOverlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 hidden opacity-0"
     onclick="closeTaskModal()"></div>

<div id="taskModalPanel"
     class="fixed top-0 right-0 bottom-0 w-full max-w-md bg-white z-50 shadow-2xl flex flex-col overflow-hidden">

    {{-- Panel header --}}
    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-brand-soft">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-brand/10 rounded-xl flex items-center justify-center">
                <span class="iconify text-brand text-xl" data-icon="mdi:clipboard-plus-outline"></span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-brand">Create New Task</h4>
                <p class="text-[11px] text-slate-400">Fill in the task details below</p>
            </div>
        </div>
        <button onclick="closeTaskModal()"
            class="w-8 h-8 rounded-full hover:bg-brand/10 flex items-center justify-center text-slate-400 hover:text-brand transition">
            <span class="iconify text-xl" data-icon="mdi:close"></span>
        </button>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('faculty.tasks.store') }}" class="flex-1 overflow-y-auto">
        @csrf

        @if($errors->any())
        <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="p-6 space-y-5">

            {{-- Role --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Department / Role <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <select id="taskRoleSelect" name="role"
                        class="w-full h-12 pl-4 pr-10 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700
                               focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition appearance-none">
                        @foreach($roles as $rKey => $rMeta)
                        <option value="{{ $rKey }}" {{ old('role', $activeTab) === $rKey ? 'selected' : '' }}>
                            {{ $rMeta['label'] }}
                        </option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 iconify text-slate-400"
                          data-icon="mdi:chevron-down"></span>
                </div>
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Task Title <span class="text-red-400">*</span>
                </label>
                <input name="title" type="text" value="{{ old('title') }}"
                    placeholder="e.g. Check-in procedures review"
                    class="w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Description <span class="text-slate-300 font-normal">(optional)</span>
                </label>
                <textarea name="description" rows="4"
                    placeholder="Describe what students need to do..."
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none
                           focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">{{ old('description') }}</textarea>
            </div>

            {{-- Due date --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    Due Date
                </label>
                <input name="due_date" type="date" value="{{ old('due_date') }}"
                    class="w-full h-12 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
            </div>
            <input type="hidden" name="priority" value="medium">

        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 px-6 py-4 bg-white border-t border-slate-100 flex gap-3">
            <button type="button" onclick="closeTaskModal()"
                class="flex-1 py-2.5 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 transition font-semibold text-sm">
                Cancel
            </button>
            <button type="submit"
                class="flex-1 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20">
                Save Task
            </button>
        </div>
    </form>
</div>

<script>
    function openTaskModal(preselectedRole) {
        const overlay = document.getElementById('taskModalOverlay');
        const panel   = document.getElementById('taskModalPanel');

        if (preselectedRole) {
            const sel = document.getElementById('taskRoleSelect');
            if (sel) sel.value = preselectedRole;
        }

        overlay.classList.remove('hidden');
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            panel.classList.add('open');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeTaskModal() {
        const overlay = document.getElementById('taskModalOverlay');
        const panel   = document.getElementById('taskModalPanel');

        overlay.style.opacity = '0';
        panel.classList.remove('open');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 350);
        document.body.style.overflow = '';
    }

    // Auto-open if there are validation errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => openTaskModal(null));
    @endif
</script>
@endsection
