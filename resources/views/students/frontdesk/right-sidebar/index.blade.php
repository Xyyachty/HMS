<aside class="h-full flex flex-col text-zinc-200 overflow-hidden bg-zinc-900">
    <!-- Panel Header -->
    <div class="px-5 py-4 border-b border-zinc-800 shrink-0 bg-zinc-900">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <i class="fas fa-palette text-white text-xs"></i>
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

        <!-- ── Element Target ── -->
        <div class="px-5 py-3 border-b border-zinc-800/60 bg-zinc-900/50">
            <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-zinc-800/80 border border-zinc-700/50">
                <div class="w-6 h-6 rounded-md bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-mouse-pointer text-[9px] text-cyan-400"></i>
                </div>
                <span class="text-xs text-zinc-400" id="selectedElement">Select an element to style</span>
            </div>
        </div>

        <!-- ── Heading Level ── -->
        <div class="design-section">
            <button onclick="toggleSection('heading')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-indigo-500/15 flex items-center justify-center">
                        <i class="fas fa-heading text-[9px] text-indigo-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Heading Level</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-heading"></i>
            </button>
            <div class="section-body px-5 pb-4" id="section-heading">
                <div class="grid grid-cols-3 gap-1.5">
                    <button onclick="applyHeading('h1')" class="heading-btn group" data-group="heading" title="Heading 1">
                        <span class="text-base font-extrabold text-white leading-none">H1</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Display</span>
                    </button>
                    <button onclick="applyHeading('h2')" class="heading-btn group" data-group="heading" title="Heading 2">
                        <span class="text-sm font-bold text-white leading-none">H2</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Title</span>
                    </button>
                    <button onclick="applyHeading('h3')" class="heading-btn group" data-group="heading" title="Heading 3">
                        <span class="text-[13px] font-bold text-white leading-none">H3</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Section</span>
                    </button>
                    <button onclick="applyHeading('h4')" class="heading-btn group" data-group="heading" title="Heading 4">
                        <span class="text-xs font-semibold text-white leading-none">H4</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Sub</span>
                    </button>
                    <button onclick="applyHeading('h5')" class="heading-btn group" data-group="heading" title="Heading 5">
                        <span class="text-[11px] font-semibold text-zinc-300 leading-none">H5</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Label</span>
                    </button>
                    <button onclick="applyHeading('h6')" class="heading-btn group" data-group="heading" title="Heading 6">
                        <span class="text-[10px] font-medium text-zinc-400 leading-none">H6</span>
                        <span class="text-[8px] text-zinc-600 group-hover:text-zinc-400 transition-colors mt-0.5 block">Caption</span>
                    </button>
                </div>
                <!-- Remove heading -->
                <button onclick="applyHeading('p')" class="mt-2 w-full py-1.5 rounded-lg bg-zinc-800/60 border border-zinc-700/40 text-[10px] text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 hover:border-zinc-600 transition-all">
                    <i class="fas fa-paragraph text-[9px] mr-1.5"></i>Reset to Paragraph
                </button>
            </div>
        </div>

        <!-- ── Font Style ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('typography')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-violet-500/15 flex items-center justify-center">
                        <i class="fas fa-font text-[9px] text-violet-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Font Style</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-typography"></i>
            </button>
            <div class="section-body px-5 pb-4 space-y-3" id="section-typography">
                <!-- Font Family -->
                <div>
                    <label class="settings-label">Font Family</label>
                    <div class="relative">
                        <select id="fontFamily" class="style-input appearance-none pr-8 cursor-pointer" onchange="applyStyle('font-family', this.value)">
                            <option value="inherit">Inherit</option>
                            <option value="'Manrope', sans-serif">Manrope</option>
                            <option value="'Inter', sans-serif">Inter</option>
                            <option value="system-ui, sans-serif">System UI</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="'SF Pro Display', sans-serif">SF Pro Display</option>
                            <option value="'Helvetica Neue', sans-serif">Helvetica Neue</option>
                        </select>
                        <i class="fas fa-chevron-down text-[8px] text-zinc-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Font Weight -->
                <div>
                    <label class="settings-label">Weight</label>
                    <div class="relative">
                        <select id="fontWeight" class="style-input appearance-none pr-7 cursor-pointer" onchange="applyStyle('font-weight', this.value)">
                            <option value="300">Light</option>
                            <option value="400">Regular</option>
                            <option value="500">Medium</option>
                            <option value="600">Semi Bold</option>
                            <option value="700" selected>Bold</option>
                            <option value="800">Extra Bold</option>
                        </select>
                        <i class="fas fa-chevron-down text-[8px] text-zinc-500 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>

                <!-- Style Toggles -->
                <div>
                    <label class="settings-label">Style</label>
                    <div class="flex gap-1">
                        <button onclick="toggleInlineStyle('font-style', 'italic')" class="style-toggle-btn" title="Italic">
                            <i class="fas fa-italic text-[10px]"></i>
                        </button>
                        <button onclick="toggleInlineStyle('text-decoration', 'underline')" class="style-toggle-btn" title="Underline">
                            <i class="fas fa-underline text-[10px]"></i>
                        </button>
                        <button onclick="toggleInlineStyle('text-decoration', 'line-through')" class="style-toggle-btn" title="Strikethrough">
                            <i class="fas fa-strikethrough text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Colors ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('colors')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-pink-500/15 flex items-center justify-center">
                        <i class="fas fa-droplet text-[9px] text-pink-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Colors</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-colors"></i>
            </button>
            <div class="section-body px-5 pb-4 space-y-3" id="section-colors">
                <!-- Text Color -->
                <div>
                    <label class="settings-label">Text Color</label>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="color" id="textColor" value="#e4e4e7" class="color-swatch" onchange="applyStyle('color', this.value); updateColorHex('textColorHex', this.value)">
                            <i class="fas fa-eye-dropper text-[8px] text-zinc-400 absolute inset-0 m-auto pointer-events-none"></i>
                        </div>
                        <input type="text" id="textColorHex" value="#e4e4e7" class="style-input flex-1 font-mono text-[11px]" oninput="syncColorPicker('textColor', this.value)" maxlength="7">
                    </div>
                    <!-- Text Color Swatches -->
                    <div class="flex gap-1.5 mt-2">
                        <button onclick="setColor('text','#ffffff')" class="color-preset-swatch" style="background:#ffffff" title="#ffffff"></button>
                        <button onclick="setColor('text','#e4e4e7')" class="color-preset-swatch ring-1 ring-cyan-400/50" style="background:#e4e4e7" title="#e4e4e7"></button>
                        <button onclick="setColor('text','#a1a1aa')" class="color-preset-swatch" style="background:#a1a1aa" title="#a1a1aa"></button>
                        <button onclick="setColor('text','#71717a')" class="color-preset-swatch" style="background:#71717a" title="#71717a"></button>
                        <button onclick="setColor('text','#DB2777')" class="color-preset-swatch" style="background:#DB2777" title="#DB2777"></button>
                        <button onclick="setColor('text','#3b82f6')" class="color-preset-swatch" style="background:#3b82f6" title="#3b82f6"></button>
                        <button onclick="setColor('text','#10b981')" class="color-preset-swatch" style="background:#10b981" title="#10b981"></button>
                        <button onclick="setColor('text','#f59e0b')" class="color-preset-swatch" style="background:#f59e0b" title="#f59e0b"></button>
                        <button onclick="setColor('text','#ef4444')" class="color-preset-swatch" style="background:#ef4444" title="#ef4444"></button>
                    </div>
                </div>

                <!-- Background Color -->
                <div>
                    <label class="settings-label">Background</label>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <input type="color" id="bgColor" value="#18181b" class="color-swatch" onchange="applyStyle('background-color', this.value); updateColorHex('bgColorHex', this.value)">
                            <i class="fas fa-fill-drip text-[8px] text-zinc-400 absolute inset-0 m-auto pointer-events-none"></i>
                        </div>
                        <input type="text" id="bgColorHex" value="#18181b" class="style-input flex-1 font-mono text-[11px]" oninput="syncColorPicker('bgColor', this.value)" maxlength="7">
                        <button onclick="applyStyle('background-color', 'transparent')" class="w-8 h-8 rounded-lg bg-zinc-800 border border-zinc-700/50 flex items-center justify-center hover:bg-zinc-700 transition-colors shrink-0" title="Transparent">
                            <i class="fas fa-ban text-[9px] text-zinc-500"></i>
                        </button>
                    </div>
                    <div class="flex gap-1.5 mt-2">
                        <button onclick="setColor('bg','transparent')" class="color-preset-swatch !bg-[repeating-conic-gradient(#333_0%_25%,#222_0%_50%)]_bg-[length:8px_8px]" title="Transparent"></button>
                        <button onclick="setColor('bg','#000000')" class="color-preset-swatch" style="background:#000000" title="#000000"></button>
                        <button onclick="setColor('bg','#18181b')" class="color-preset-swatch ring-1 ring-cyan-400/50" style="background:#18181b" title="#18181b"></button>
                        <button onclick="setColor('bg','#27272a')" class="color-preset-swatch" style="background:#27272a" title="#27272a"></button>
                        <button onclick="setColor('bg','#3f3f46')" class="color-preset-swatch" style="background:#3f3f46" title="#3f3f46"></button>
                        <button onclick="setColor('bg','#ffffff')" class="color-preset-swatch" style="background:#ffffff" title="#ffffff"></button>
                        <button onclick="setColor('bg','#f4f4f5')" class="color-preset-swatch" style="background:#f4f4f5" title="#f4f4f5"></button>
                        <button onclick="setColor('bg','#FDF2F8')" class="color-preset-swatch" style="background:#FDF2F8" title="#FDF2F8"></button>
                        <button onclick="setColor('bg','#DB2777')" class="color-preset-swatch" style="background:#DB2777" title="#DB2777"></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom spacer -->
        <div class="h-4"></div>
    </div>

</aside>

<style>
    /* ── Scrollbar ── */
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3f3f46; }

    /* ── Section Toggles ── */
    .section-toggle { user-select: none; }
    .section-toggle:active { background: rgba(39, 39, 42, 0.6) !important; }

    /* ── Heading Buttons ── */
    .heading-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.25rem;
        border-radius: 0.5rem;
        background: rgba(39, 39, 42, 0.6);
        border: 1px solid rgba(63, 63, 70, 0.4);
        transition: all 0.15s ease;
        cursor: pointer;
        min-height: 44px;
    }
    .heading-btn:hover {
        background: rgba(63, 63, 70, 0.8);
        border-color: rgba(99, 102, 241, 0.4);
    }
    .heading-btn.active {
        background: rgba(99, 102, 241, 0.15);
        border-color: rgba(99, 102, 241, 0.5);
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.2);
    }

    /* ── Style Inputs (selects + text fields) ── */
    .style-input {
        width: 100%;
        padding: 7px 10px;
        border-radius: 8px;
        background: #18181b;
        border: 1px solid #27272a;
        color: #d4d4d8;
        font-size: 11px;
        font-family: 'Inter', sans-serif;
        transition: all 0.15s ease;
        outline: none;
    }
    .style-input:hover {
        border-color: #3f3f46;
        background: #1c1c1f;
    }
    .style-input:focus {
        border-color: #06b6d4;
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.12);
    }
    .style-input option {
        background: #18181b;
        color: #d4d4d8;
        padding: 6px;
    }

    /* ── Style Toggle Buttons (italic/underline/strikethrough) ── */
    .style-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #18181b;
        border: 1px solid #27272a;
        color: #71717a;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .style-toggle-btn:hover {
        background: #27272a;
        color: #d4d4d8;
        border-color: #3f3f46;
    }
    .style-toggle-btn.active {
        background: rgba(99, 102, 241, 0.12);
        border-color: rgba(99, 102, 241, 0.45);
        color: #a78bfa;
        box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.15);
    }

    /* ── Color Preset Swatches ── */
    .color-preset-swatch {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .color-preset-swatch:hover {
        transform: scale(1.2);
        border-color: rgba(255, 255, 255, 0.25);
    }

    /* ── Color Swatch (native picker) ── */
    .color-swatch {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid #27272a;
        transition: all 0.15s ease;
        padding: 0;
    }
    .color-swatch:hover {
        transform: scale(1.12);
        border-color: #3f3f46;
    }
    .color-swatch::-webkit-color-swatch-wrapper { padding: 2px; }
    .color-swatch::-webkit-color-swatch { border-radius: 4px; border: none; }

    /* ── Settings Label ── */
    .settings-label {
        font-size: 10px;
        font-weight: 600;
        color: #52525b;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 6px;
        display: block;
    }
</style>

<script>
    function applyHeading(tag) {
        if (!window.selectedBlock) return;
        const el = window.selectedBlock;

        // Find the closest heading/paragraph inside the block, or use the block itself
        const headingTags = ['H1','H2','H3','H4','H5','H6','P'];
        let target = null;

        // Check if selected element is itself a heading/p
        if (headingTags.includes(el.tagName)) {
            target = el;
        } else {
            // Look for first heading/p child
            target = el.querySelector('h1,h2,h3,h4,h5,h6,p');
        }

        if (!target) return;

        // Replace the element with the new tag, preserving content & attributes
        const newEl = document.createElement(tag);
        newEl.innerHTML = target.innerHTML;
        // Copy class and style
        newEl.className = target.className;
        newEl.style.cssText = target.style.cssText;
        target.replaceWith(newEl);

        // Update active state on buttons
        document.querySelectorAll('[data-group="heading"]').forEach(b => b.classList.remove('active'));
        const matched = [...document.querySelectorAll('[data-group="heading"]')].find(b =>
            b.getAttribute('onclick') === `applyHeading('${tag}')`
        );
        if (matched) matched.classList.add('active');
    }
</script>