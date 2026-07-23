{{-- Shared hotel template builder: mode + version history --}}
@php
    $builderRole = $builderRole ?? 'front_desk';
    $builderCanEdit = (bool) ($canEditTemplate ?? false);
@endphp

<div class="px-4 py-4 border-t border-zinc-800 space-y-4" id="hotelBuilderPanels" data-role="{{ $builderRole }}">
    <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-2">Mode</p>
        <div class="flex gap-1 bg-zinc-900 rounded-lg p-1 border border-zinc-800">
            <button type="button" id="hbModeBuild"
                class="flex-1 py-1.5 rounded-md text-[11px] font-semibold {{ $builderCanEdit ? 'bg-zinc-800 text-white' : 'text-zinc-600 cursor-not-allowed' }}"
                {{ $builderCanEdit ? '' : 'disabled' }}>Build</button>
            <button type="button" id="hbModePreview"
                class="flex-1 py-1.5 rounded-md text-[11px] font-semibold text-zinc-500">Preview</button>
        </div>
        @unless($builderCanEdit)
            <p class="text-[10px] text-amber-400/90 mt-2">View only — faculty must assign this role (or grant edit permission).</p>
        @else
            <p class="text-[10px] text-zinc-600 mt-2">Build: edit on the page. Preview: live hotel site.</p>
        @endunless
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Version history</p>
            <button type="button" id="hbRefreshVersions" class="text-[10px] text-cyan-400 hover:underline">Refresh</button>
        </div>
        <div id="hbVersionList" class="space-y-1.5 max-h-40 overflow-y-auto text-[11px]">
            <p class="text-zinc-600">Loading…</p>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="button" id="hbSaveBtn"
            class="flex-1 py-2 rounded-lg text-[11px] font-bold bg-zinc-800 border border-zinc-700 text-white {{ $builderCanEdit ? '' : 'opacity-40 cursor-not-allowed' }}"
            {{ $builderCanEdit ? '' : 'disabled' }}>Save</button>
        <button type="button" id="hbPublishBtn"
            class="flex-1 py-2 rounded-lg text-[11px] font-bold bg-cyan-600 text-white {{ $builderCanEdit ? '' : 'opacity-40 cursor-not-allowed' }}"
            {{ $builderCanEdit ? '' : 'disabled' }}>Publish</button>
    </div>
    <p id="hbStatus" class="text-[10px] text-zinc-500">Save with Ctrl+S or Save Draft — changes are not auto-saved</p>
</div>
