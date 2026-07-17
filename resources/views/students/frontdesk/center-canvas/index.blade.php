<div class="flex-1 min-h-0 p-4 flex flex-col">

    @php
        $builderRole = $builderRole ?? 'front_desk';
        $isFrontDeskRole = $builderRole === 'front_desk';
        $moduleLabel = $moduleLabel ?? (\App\Support\HotelTemplateBuilder::ROLES[$builderRole] ?? 'Editor');
        $hasTemplate = !empty($selectedTemplate);
        // Only Front Desk can pick; everyone else waits until Front Desk chooses
        $canPickTemplate = $isFrontDeskRole && ($canEditTemplate ?? false) && !$hasTemplate;
        $waitingForFrontDesk = !$hasTemplate && !$canPickTemplate;
        $accent = $theme['badge_color'] ?? '#22d3ee';
        $accentBorder = match ($builderRole) {
            'room_management' => 'hover:border-rose-500 hover:shadow-[0_0_20px_rgba(244,63,94,0.2)]',
            'restaurant_management' => 'hover:border-amber-500 hover:shadow-[0_0_20px_rgba(245,158,11,0.2)]',
            'housekeeping' => 'hover:border-emerald-500 hover:shadow-[0_0_20px_rgba(16,185,129,0.2)]',
            'maintenance' => 'hover:border-purple-500 hover:shadow-[0_0_20px_rgba(168,85,247,0.2)]',
            default => 'hover:border-cyan-500 hover:shadow-[0_0_20px_rgba(6,182,212,0.2)]',
        };
    @endphp

    @if($canPickTemplate)
    <div id="templatePicker" class="flex-1 flex flex-col items-center justify-center gap-8">
        <div class="text-center">
            <p class="text-[10px] font-semibold tracking-widest uppercase mb-2" style="color: {{ $accent }}">Front Desk Editor</p>
            <h2 class="text-xl font-bold text-white mb-1">Choose a Template</h2>
            <p class="text-xs text-zinc-500">Select a starting template for your group. Teammates will see it only after you choose.</p>
        </div>
        <div class="flex gap-6 flex-wrap justify-center">
            <button type="button" onclick="selectHotelTemplate('1')"
                class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden {{ $accentBorder }} transition-all duration-200 text-left">
                <div class="h-32 bg-zinc-800 overflow-hidden relative">
                    <iframe src="{{ route('students.frontdesk.template.1') }}" class="w-full h-full border-0 pointer-events-none scale-[0.5] origin-top-left" style="width:200%;height:200%;" tabindex="-1" aria-hidden="true"></iframe>
                    <div class="absolute inset-0 bg-zinc-900/20 group-hover:bg-transparent transition-colors"></div>
                </div>
                <div class="p-3">
                    <p class="text-xs font-bold text-white mb-0.5">Template 1</p>
                    <p class="text-[10px] text-zinc-500">Dark luxury — Playfair Display</p>
                </div>
            </button>

            <button type="button" onclick="selectHotelTemplate('2')"
                class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden {{ $accentBorder }} transition-all duration-200 text-left">
                <div class="h-32 bg-zinc-800 overflow-hidden relative">
                    <iframe src="{{ route('students.frontdesk.template.2') }}" class="w-full h-full border-0 pointer-events-none scale-[0.5] origin-top-left" style="width:200%;height:200%;" tabindex="-1" aria-hidden="true"></iframe>
                    <div class="absolute inset-0 bg-zinc-900/20 group-hover:bg-transparent transition-colors"></div>
                </div>
                <div class="p-3">
                    <p class="text-xs font-bold text-white mb-0.5">Template 2</p>
                    <p class="text-[10px] text-zinc-500">Light natural — Cormorant Garamond</p>
                </div>
            </button>
        </div>
    </div>
    @elseif($waitingForFrontDesk)
    <div class="flex-1 flex flex-col items-center justify-center gap-6">
        <div class="text-center max-w-sm">
            <p class="text-[10px] font-semibold tracking-widest uppercase mb-2" style="color: {{ $accent }}">{{ $moduleLabel }}</p>
            <h2 class="text-xl font-bold text-white mb-1">Waiting for Front Desk</h2>
            <p class="text-xs text-zinc-500">Your team will see the hotel template only after Front Desk chooses Template 1 or Template 2.</p>
        </div>
        <div class="w-16 h-16 rounded-full border-2 border-dashed border-zinc-700 flex items-center justify-center">
            <i class="fas fa-hourglass-half text-2xl text-zinc-600"></i>
        </div>
    </div>
    @endif

    <div id="canvasFrame" class="flex-1 min-h-0 {{ ($canPickTemplate || $waitingForFrontDesk) ? 'hidden' : '' }}">
        <div class="canvas-frame h-full">
            <div class="browser-bar flex items-center justify-between">
                <div class="browser-controls">
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-chevron-left"></i></div>
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-rotate-right"></i></div>
                </div>
                <div id="urlPill" class="url-pill truncate max-w-[50%]">—</div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleFullscreenRedesign()" class="browser-btn" title="Fullscreen redesign" style="width:auto;padding:0 10px;gap:6px;font-size:11px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-expand" id="fsCanvasIcon"></i>
                        <span id="fsCanvasLabel">Fullscreen</span>
                    </button>
                </div>
            </div>
            <div class="flex-1 min-h-0 bg-white">
                <iframe id="templateFrame" src="" title="{{ $moduleLabel }} Template" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

</div>

@if($canPickTemplate)
<script>
    async function selectHotelTemplate(key) {
        try {
            const res = await fetch(@json(route('students.templates.save', ['role' => 'front_desk'])), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ selected_template: String(key), publish: false, label: 'Template ' + key + ' selected' })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Could not save template');
            window.location.reload();
        } catch (err) {
            if (typeof toast === 'function') toast(err.message || 'Could not select template');
            else alert(err.message || 'Could not select template');
        }
    }
</script>
@endif

@if($hasTemplate)
<script>
    (function () {
        var TEMPLATE_URLS = {
            '1': '{{ route("students.frontdesk.template.1") }}',
            '2': '{{ route("students.frontdesk.template.2") }}'
        };
        var TEMPLATE_LABELS = { '1': 'Template 1', '2': 'Template 2' };
        var serverTemplate = '{{ $selectedTemplate }}';
        var canEdit = @json((bool) ($canEditTemplate ?? false));
        if (serverTemplate && TEMPLATE_URLS[serverTemplate]) {
            var frame = document.getElementById('templateFrame');
            frame.src = TEMPLATE_URLS[serverTemplate];
            document.getElementById('urlPill').textContent = TEMPLATE_LABELS[serverTemplate];
            var side = document.getElementById('sidebarTemplateUrl');
            if (side) side.textContent = TEMPLATE_LABELS[serverTemplate];
            frame.addEventListener('load', function () {
                if (typeof postToTemplate === 'function') {
                    postToTemplate({ type: 'set-can-edit', canEdit: canEdit });
                    postToTemplate({ type: 'set-mode', mode: canEdit ? (window.currentEditorMode || 'design') : 'preview' });
                    if (window.templateCustomizations) {
                        postToTemplate({ type: 'load-customizations', customizations: window.templateCustomizations });
                    }
                }
            });
        }
    })();
</script>
@endif
