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
            <div class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden {{ $accentBorder }} transition-all duration-200 text-left">
                <div class="h-32 bg-zinc-800 overflow-hidden relative">
                    <iframe src="{{ route('students.frontdesk.template.1', ['role' => $builderRole ?? 'front_desk']) }}" class="w-full h-full border-0 pointer-events-none scale-[0.5] origin-top-left" style="width:200%;height:200%;" tabindex="-1" aria-hidden="true"></iframe>
                    <div class="absolute inset-0 bg-zinc-900/20 group-hover:bg-transparent transition-colors"></div>
                </div>
                <div class="p-3">
                    <p class="text-xs font-bold text-white mb-0.5">Template 1</p>
                    <p class="text-[10px] text-zinc-500 mb-2">Dark luxury — Playfair Display</p>
                    <div class="flex gap-2">
                        <button type="button" onclick="previewHotelTemplate('1')" class="flex-1 text-[10px] font-semibold uppercase tracking-wide px-2 py-1.5 rounded-md border border-zinc-600 text-zinc-300 hover:bg-zinc-800 transition-colors">Preview</button>
                        <button type="button" onclick="selectHotelTemplate('1')" class="flex-1 text-[10px] font-semibold uppercase tracking-wide px-2 py-1.5 rounded-md text-black transition-colors" style="background: {{ $accent }}">Select</button>
                    </div>
                </div>
            </div>

            <div class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden {{ $accentBorder }} transition-all duration-200 text-left">
                <div class="h-32 bg-zinc-800 overflow-hidden relative">
                    <iframe src="{{ route('students.frontdesk.template.2', ['role' => $builderRole ?? 'front_desk']) }}" class="w-full h-full border-0 pointer-events-none scale-[0.5] origin-top-left" style="width:200%;height:200%;" tabindex="-1" aria-hidden="true"></iframe>
                    <div class="absolute inset-0 bg-zinc-900/20 group-hover:bg-transparent transition-colors"></div>
                </div>
                <div class="p-3">
                    <p class="text-xs font-bold text-white mb-0.5">Template 2</p>
                    <p class="text-[10px] text-zinc-500 mb-2">Light natural — Cormorant Garamond</p>
                    <div class="flex gap-2">
                        <button type="button" onclick="previewHotelTemplate('2')" class="flex-1 text-[10px] font-semibold uppercase tracking-wide px-2 py-1.5 rounded-md border border-zinc-600 text-zinc-300 hover:bg-zinc-800 transition-colors">Preview</button>
                        <button type="button" onclick="selectHotelTemplate('2')" class="flex-1 text-[10px] font-semibold uppercase tracking-wide px-2 py-1.5 rounded-md text-black transition-colors" style="background: {{ $accent }}">Select</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="templatePreviewModal" class="hidden fixed inset-0 z-50 flex flex-col bg-black/80 backdrop-blur-sm p-4 md:p-8">
        <div class="flex-1 min-h-0 w-full max-w-6xl mx-auto flex flex-col bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-700 shrink-0">
                <div>
                    <p class="text-[10px] font-semibold tracking-widest uppercase" style="color: {{ $accent }}">Default Design Preview</p>
                    <h3 id="templatePreviewTitle" class="text-sm font-bold text-white">Template</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="templatePreviewSelectBtn" onclick="selectHotelTemplate(window.__previewingTemplate)" class="text-[11px] font-semibold uppercase tracking-wide px-3 py-1.5 rounded-md text-black transition-colors" style="background: {{ $accent }}">Select this template</button>
                    <button type="button" onclick="closeTemplatePreview()" class="text-zinc-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-md hover:bg-zinc-800 transition-colors" aria-label="Close preview">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 min-h-0 bg-white">
                <iframe id="templatePreviewFrame" src="" title="Template preview" class="w-full h-full border-0"></iframe>
            </div>
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
            <div class="flex-1 min-h-0 bg-white">
                <iframe id="templateFrame" src="" title="{{ $moduleLabel }} Template" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

</div>

@if($canPickTemplate)
<script>
    var TEMPLATE_PREVIEW_URLS = {
        '1': @json(route('students.frontdesk.template.1', ['role' => $builderRole ?? 'front_desk'])),
        '2': @json(route('students.frontdesk.template.2', ['role' => $builderRole ?? 'front_desk']))
    };
    var TEMPLATE_PREVIEW_LABELS = { '1': 'Template 1 — Dark luxury', '2': 'Template 2 — Light natural' };

    function previewHotelTemplate(key) {
        window.__previewingTemplate = key;
        var modal = document.getElementById('templatePreviewModal');
        var frame = document.getElementById('templatePreviewFrame');
        var title = document.getElementById('templatePreviewTitle');
        title.textContent = TEMPLATE_PREVIEW_LABELS[key] || ('Template ' + key);
        frame.src = TEMPLATE_PREVIEW_URLS[key];
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeTemplatePreview() {
        var modal = document.getElementById('templatePreviewModal');
        var frame = document.getElementById('templatePreviewFrame');
        modal.classList.add('hidden');
        frame.src = '';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeTemplatePreview();
    });

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
            '1': '{{ route("students.frontdesk.template.1", ["role" => $builderRole ?? "front_desk"]) }}',
            '2': '{{ route("students.frontdesk.template.2", ["role" => $builderRole ?? "front_desk"]) }}'
        };
        var TEMPLATE_LABELS = { '1': 'Template 1', '2': 'Template 2' };
        var serverTemplate = '{{ $selectedTemplate }}';
        var canEdit = @json((bool) ($canEditTemplate ?? false));
        var editablePages = @json($editablePages ?? []);
        var preferredPage = @json($preferredPage ?? 'home');
        if (serverTemplate && TEMPLATE_URLS[serverTemplate]) {
            var frame = document.getElementById('templateFrame');
            frame.src = TEMPLATE_URLS[serverTemplate];
            var pill = document.getElementById('urlPill');
            if (pill) pill.textContent = TEMPLATE_LABELS[serverTemplate];
            var side = document.getElementById('sidebarTemplateUrl');
            if (side) side.textContent = TEMPLATE_LABELS[serverTemplate];
            frame.addEventListener('load', function () {
                if (typeof postToTemplate === 'function') {
                    // Redesign is gated by HMS role assignment (not hotel Staff login)
                    var designOn = canEdit && (window.currentEditorMode !== 'preview');
                    postToTemplate({ type: 'set-editable-pages', pages: editablePages });
                    postToTemplate({ type: 'set-can-edit', canEdit: designOn });
                    postToTemplate({ type: 'set-mode', mode: designOn ? 'design' : 'preview' });
                    if (preferredPage) {
                        postToTemplate({ type: 'navigate-page', page: preferredPage });
                    }
                    if (window.templateCustomizations) {
                        postToTemplate({ type: 'load-customizations', customizations: window.templateCustomizations });
                    }
                }
            });
        }
    })();
</script>
@endif
