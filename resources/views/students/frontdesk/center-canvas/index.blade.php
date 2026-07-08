<div class="flex-1 min-h-0 p-4 flex flex-col">

    @php
        $isFrontDesk = in_array('front_desk', $studentRoles ?? []);
        $hasTemplate = !empty($selectedTemplate);
    @endphp

    @if($isFrontDesk && !$hasTemplate)
    {{-- Template Selection Screen — only shown when no template is locked --}}
    <div id="templatePicker" class="flex-1 flex flex-col items-center justify-center gap-8">
        <div class="text-center">
            <p class="text-[10px] font-semibold tracking-widest text-cyan-500 uppercase mb-2">Front Desk Editor</p>
            <h2 class="text-xl font-bold text-white mb-1">Choose a Template</h2>
            <p class="text-xs text-zinc-500">Select a starting template for your group. This cannot be changed once selected.</p>
        </div>
        <div class="flex gap-6">
            {{-- Template 1 --}}
            <button onclick="selectTemplate('{{ route('students.frontdesk.template.1') }}', 'Template 1')"
                class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden hover:border-cyan-500 hover:shadow-[0_0_20px_rgba(6,182,212,0.2)] transition-all duration-200 text-left">
                <div class="h-32 bg-zinc-800 overflow-hidden relative">
                    <iframe src="{{ route('students.frontdesk.template.1') }}" class="w-full h-full border-0 pointer-events-none scale-[0.5] origin-top-left" style="width:200%;height:200%;" tabindex="-1" aria-hidden="true"></iframe>
                    <div class="absolute inset-0 bg-zinc-900/20 group-hover:bg-transparent transition-colors"></div>
                </div>
                <div class="p-3">
                    <p class="text-xs font-bold text-white mb-0.5">Template 1</p>
                    <p class="text-[10px] text-zinc-500">Dark luxury — Playfair Display</p>
                </div>
            </button>

            {{-- Template 2 --}}
            <button onclick="selectTemplate('{{ route('students.frontdesk.template.2') }}', 'Template 2')"
                class="group w-56 bg-zinc-900 border border-zinc-700 rounded-xl overflow-hidden hover:border-cyan-500 hover:shadow-[0_0_20px_rgba(6,182,212,0.2)] transition-all duration-200 text-left">
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
    @endif

    {{-- Canvas Frame: hidden for front desk until template selected, always shown for others --}}
    <div id="canvasFrame" class="flex-1 min-h-0 {{ ($isFrontDesk && !$hasTemplate) ? 'hidden' : '' }}">
        <div class="canvas-frame h-full">
            <div class="browser-bar flex items-center justify-between">
                <div class="browser-controls">
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-chevron-left"></i></div>
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
                    <div class="browser-btn" aria-hidden="true"><i class="fas fa-rotate-right"></i></div>
                </div>
                <div id="urlPill" class="url-pill truncate max-w-[60%]">—</div>
                <div></div>
            </div>
            <div class="flex-1 min-h-0 bg-white">
                <iframe id="templateFrame" src="" title="Front Desk Template" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

</div>

@if($isFrontDesk && !$hasTemplate)
<script>
    const STORAGE_KEY = 'fd_selected_template';
    const TEMPLATE_URLS = {
        '1': '{{ route("students.frontdesk.template.1") }}',
        '2': '{{ route("students.frontdesk.template.2") }}'
    };
    const TEMPLATE_LABELS = { '1': 'Template 1', '2': 'Template 2' };

    function selectTemplate(url, label) {
        // Save to database via GET with ?save=1
        fetch(url + '?save=1', { credentials: 'same-origin' });

        // Reload page so server-side lock takes effect
        window.location.reload();
    }
</script>
@endif

@if($isFrontDesk && $hasTemplate)
<script>
    // Load locked template on page load
    (function () {
        var TEMPLATE_URLS = {
            '1': '{{ route("students.frontdesk.template.1") }}',
            '2': '{{ route("students.frontdesk.template.2") }}'
        };
        var TEMPLATE_LABELS = { '1': 'Template 1', '2': 'Template 2' };
        var serverTemplate = '{{ $selectedTemplate }}';
        if (serverTemplate && TEMPLATE_URLS[serverTemplate]) {
            document.getElementById('templateFrame').src = TEMPLATE_URLS[serverTemplate];
            document.getElementById('urlPill').textContent = TEMPLATE_LABELS[serverTemplate];
            var side = document.getElementById('sidebarTemplateUrl');
            if (side) side.textContent = TEMPLATE_LABELS[serverTemplate];
        }
    })();
</script>
@endif
