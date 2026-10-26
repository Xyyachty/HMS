<div class="flex-1 min-h-0 p-4 flex flex-col">

    @php
        $hasTemplate = !empty($selectedTemplate);
    @endphp

    @if(!$hasTemplate)
    {{-- No Template Selected Screen --}}
    <div class="flex-1 flex flex-col items-center justify-center gap-8">
        <div class="text-center">
            <p class="text-[0.625rem] font-semibold tracking-widest text-amber-500 uppercase mb-2">Restaurant Management</p>
            <h2 class="text-xl font-bold text-white mb-1">No Template Selected</h2>
            <p class="text-xs text-zinc-500">The Front Desk team has not chosen a template.</p>
        </div>
        <div class="w-16 h-16 rounded-full border-2 border-dashed border-zinc-700 flex items-center justify-center">
            <i class="fas fa-image text-2xl text-zinc-600"></i>
        </div>
    </div>
    @endif

    {{-- Canvas Frame --}}
    <div id="canvasFrame" class="flex-1 min-h-0 {{ !$hasTemplate ? 'hidden' : '' }}">
        <div class="canvas-frame h-full">
            <div class="flex-1 min-h-0 bg-white">
                <iframe id="templateFrame" src="{{ $selectedTemplate ? route('students.frontdesk.template.' . $selectedTemplate, ['role' => $builderRole ?? 'restaurant_management']) : '' }}" title="Restaurant Template" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

</div>