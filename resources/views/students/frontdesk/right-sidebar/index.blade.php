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
            <button class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition-all" title="Reset selected element styles" onclick="resetSelectedStyles()">
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
            <div class="grid grid-cols-2 gap-1.5 mt-2.5">
                <button type="button" onclick="duplicateSelectedElement()" class="py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-zinc-300 hover:border-cyan-500/40 hover:text-white transition">
                    <i class="fas fa-copy mr-1"></i>Duplicate
                </button>
                <button type="button" onclick="deleteSelectedElement()" class="py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-rose-300 hover:border-rose-500/40 hover:text-rose-200 transition">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
            </div>
        </div>

        <!-- ── Add Elements ── -->
        <div class="design-section border-b border-zinc-800/60">
            <button onclick="toggleSection('add')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-cyan-500/15 flex items-center justify-center">
                        <i class="fas fa-plus text-[9px] text-cyan-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Add to page</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-add"></i>
            </button>
            <div class="section-body px-5 pb-4" id="section-add">
                <p class="text-[10px] text-zinc-500 mb-2">Adds objects onto the live template. Move and resize them on the page.</p>
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="addCanvasElement('text')" class="add-el-btn"><i class="fas fa-font"></i>Text</button>
                    <button type="button" onclick="addCanvasElement('button')" class="add-el-btn"><i class="fas fa-square"></i>Button</button>
                    <button type="button" onclick="addCanvasElement('image')" class="add-el-btn"><i class="fas fa-image"></i>Image</button>
                    <button type="button" onclick="addCanvasElement('textfield')" class="add-el-btn"><i class="fas fa-i-cursor"></i>Text field</button>
                    <button type="button" onclick="addCanvasElement('icon')" class="add-el-btn"><i class="fas fa-icons"></i>Icon</button>
                    <button type="button" onclick="addCanvasElement('card')" class="add-el-btn"><i class="fas fa-id-card"></i>Card</button>
                    <button type="button" onclick="addCanvasElement('container')" class="add-el-btn col-span-2"><i class="fas fa-border-all"></i>Container</button>
                </div>
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

                <!-- Font Size -->
                <div>
                    <label class="settings-label">Font Size</label>
                    <div class="flex items-center gap-2">
                        <input id="fontSizeRange" type="range" min="10" max="96" value="16" class="flex-1 accent-cyan-500"
                            oninput="document.getElementById('fontSizeVal').value = this.value; applyStyle('font-size', this.value + 'px')">
                        <input id="fontSizeVal" type="number" min="8" max="200" value="16" class="style-input w-16 text-center"
                            onchange="document.getElementById('fontSizeRange').value = this.value; applyStyle('font-size', this.value + 'px')">
                    </div>
                </div>

                <!-- Alignment -->
                <div>
                    <label class="settings-label">Align</label>
                    <div class="flex gap-1">
                        <button type="button" onclick="applyStyle('text-align','left')" class="style-toggle-btn" title="Left"><i class="fas fa-align-left text-[10px]"></i></button>
                        <button type="button" onclick="applyStyle('text-align','center')" class="style-toggle-btn" title="Center"><i class="fas fa-align-center text-[10px]"></i></button>
                        <button type="button" onclick="applyStyle('text-align','right')" class="style-toggle-btn" title="Right"><i class="fas fa-align-right text-[10px]"></i></button>
                        <button type="button" onclick="applyStyle('text-align','justify')" class="style-toggle-btn" title="Justify"><i class="fas fa-align-justify text-[10px]"></i></button>
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

        <!-- ── Spacing ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('spacing')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-sky-500/15 flex items-center justify-center">
                        <i class="fas fa-expand text-[9px] text-sky-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Spacing</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-spacing"></i>
            </button>
            <div class="section-body px-5 pb-4 space-y-3" id="section-spacing">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="settings-label">Padding</label>
                        <input id="padInput" type="text" class="style-input" placeholder="e.g. 12px" onchange="applyStyle('padding', this.value)">
                    </div>
                    <div>
                        <label class="settings-label">Margin</label>
                        <input id="marginInput" type="text" class="style-input" placeholder="e.g. 8px" onchange="applyStyle('margin', this.value)">
                    </div>
                    <div>
                        <label class="settings-label">Width</label>
                        <input id="widthInput" type="text" class="style-input" placeholder="e.g. 240px" onchange="applyStyle('width', this.value)">
                    </div>
                    <div>
                        <label class="settings-label">Height</label>
                        <input id="heightInput" type="text" class="style-input" placeholder="e.g. 80px" onchange="applyStyle('height', this.value)">
                    </div>
                    <div>
                        <label class="settings-label">Radius</label>
                        <input id="radiusInput" type="text" class="style-input" placeholder="e.g. 12px" onchange="applyStyle('border-radius', this.value)">
                    </div>
                    <div>
                        <label class="settings-label">Opacity</label>
                        <input id="opacityInput" type="number" min="0" max="1" step="0.05" value="1" class="style-input" onchange="applyStyle('opacity', this.value)">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Layers ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('layers')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-orange-500/15 flex items-center justify-center">
                        <i class="fas fa-layer-group text-[9px] text-orange-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Layers</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-layers"></i>
            </button>
            <div class="section-body px-5 pb-4" id="section-layers">
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="layerSelected('front')" class="add-el-btn"><i class="fas fa-arrow-up"></i>To front</button>
                    <button type="button" onclick="layerSelected('back')" class="add-el-btn"><i class="fas fa-arrow-down"></i>To back</button>
                    <button type="button" onclick="layerSelected('forward')" class="add-el-btn"><i class="fas fa-caret-up"></i>Forward</button>
                    <button type="button" onclick="layerSelected('backward')" class="add-el-btn"><i class="fas fa-caret-down"></i>Backward</button>
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

        <!-- ── Content ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('content')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-emerald-500/15 flex items-center justify-center">
                        <i class="fas fa-align-left text-[9px] text-emerald-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Content</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-content"></i>
            </button>
            <div class="section-body px-5 pb-4 space-y-3" id="section-content">
                <div>
                    <label class="settings-label">Text / Label</label>
                    <textarea id="elementText" rows="3" class="style-input resize-y min-h-[72px]" placeholder="Select text in the template, then edit here"
                        oninput="applyTextContent(this.value)"></textarea>
                    <p class="text-[10px] text-zinc-600 mt-1.5">Tip: double-click text in the template to type directly.</p>
                </div>
                <div>
                    <label class="settings-label">Icon class (Font Awesome)</label>
                    <input id="iconClass" type="text" class="style-input font-mono" placeholder="e.g. fas fa-hotel"
                        onchange="applyIconClass(this.value)">
                </div>
            </div>
        </div>

        <!-- ── Media ── -->
        <div class="design-section border-t border-zinc-800/60">
            <button onclick="toggleSection('media')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-amber-500/15 flex items-center justify-center">
                        <i class="fas fa-image text-[9px] text-amber-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Logo &amp; Images</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-media"></i>
            </button>
            <div class="section-body px-5 pb-4 space-y-3" id="section-media">
                <p class="text-[10px] text-zinc-500">Select an image, logo area, or hero background in the template, then upload a new picture.</p>
                <input type="file" id="designImageInput" accept="image/*" class="hidden" onchange="uploadSelectedImage(this)">
                <button type="button" onclick="document.getElementById('designImageInput').click()"
                    class="w-full h-10 rounded-xl bg-zinc-800 border border-zinc-700 text-xs font-semibold text-zinc-200 hover:border-cyan-500/50 hover:text-white transition flex items-center justify-center gap-2">
                    <i class="fas fa-cloud-upload-alt text-cyan-400"></i>
                    Upload / Replace Image
                </button>
                <input id="imageUrlInput" type="url" class="style-input" placeholder="Or paste image URL"
                    onchange="applyImageUrl(this.value)">
            </div>
        </div>

        <!-- Bottom spacer -->
        <div class="h-4"></div>

        @include('students.partials.hotel-builder-panels', ['builderRole' => $builderRole ?? 'front_desk'])
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

    .add-el-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 6px;
        border-radius: 8px;
        background: #18181b;
        border: 1px solid #27272a;
        color: #d4d4d8;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .add-el-btn:hover {
        border-color: rgba(6, 182, 212, 0.45);
        color: #fff;
        background: #1c1c1f;
    }
    .add-el-btn i { font-size: 10px; color: #22d3ee; }
</style>

<script>
    window.selectedElementId = null;
    window.templateCustomizations = {};
    window.currentEditorMode = 'design';

    function postToTemplate(payload) {
        const frame = document.getElementById('templateFrame');
        if (!frame || !frame.contentWindow) return;
        frame.contentWindow.postMessage(Object.assign({ source: 'hms-parent' }, payload), '*');
    }

    function toggleSection(id) {
        const body = document.getElementById('section-' + id);
        const chevron = document.getElementById('chevron-' + id);
        if (!body) return;
        const hidden = body.classList.toggle('hidden');
        if (chevron) chevron.style.transform = hidden ? 'rotate(-90deg)' : '';
    }

    function requireSelection() {
        if (!window.selectedElementId) {
            if (typeof toast === 'function') toast('Click an element in the template first');
            return false;
        }
        return true;
    }

    function applyHeading(tag) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, heading: tag });
        document.querySelectorAll('[data-group="heading"]').forEach(b => b.classList.remove('active'));
        const matched = [...document.querySelectorAll('[data-group="heading"]')].find(b =>
            b.getAttribute('onclick') === "applyHeading('" + tag + "')"
        );
        if (matched) matched.classList.add('active');
    }

    function applyStyle(prop, value) {
        if (!requireSelection()) return;
        const style = {};
        style[prop] = value;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, style: style });
    }

    function toggleInlineStyle(prop, value) {
        if (!requireSelection()) return;
        const btn = (typeof event !== 'undefined' && event) ? event.currentTarget : null;
        const active = btn ? btn.classList.toggle('active') : true;
        const style = {};
        style[prop] = active ? value : (prop === 'font-style' ? 'normal' : 'none');
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, style: style });
    }

    function setColor(kind, value) {
        if (kind === 'text') {
            const picker = document.getElementById('textColor');
            const hex = document.getElementById('textColorHex');
            if (picker && value !== 'transparent') picker.value = value;
            if (hex) hex.value = value;
            applyStyle('color', value);
        } else {
            const picker = document.getElementById('bgColor');
            const hex = document.getElementById('bgColorHex');
            if (picker && value !== 'transparent') picker.value = value;
            if (hex) hex.value = value;
            applyStyle('background-color', value);
        }
    }

    function syncColorPicker(pickerId, hex) {
        if (!/^#([0-9A-Fa-f]{3}){1,2}$/.test(hex)) return;
        const picker = document.getElementById(pickerId);
        if (picker) picker.value = hex;
        if (pickerId === 'textColor') applyStyle('color', hex);
        if (pickerId === 'bgColor') applyStyle('background-color', hex);
    }

    function updateColorHex(hexId, value) {
        const el = document.getElementById(hexId);
        if (el) el.value = value;
    }

    function applyTextContent(value) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, text: value });
    }

    function applyIconClass(value) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, iconClass: value });
    }

    function applyImageUrl(url) {
        if (!requireSelection() || !url) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, src: url });
    }

    async function uploadSelectedImage(input) {
        if (!requireSelection()) { input.value = ''; return; }
        const file = input.files && input.files[0];
        if (!file) return;
        const form = new FormData();
        form.append('image', file);
        const token = document.querySelector('meta[name="csrf-token"]');
        form.append('_token', token ? token.content : '');
        try {
            const res = await fetch(@json(route('students.frontdesk.template.media')), {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!res.ok || !data.url) throw new Error('Upload failed');
            postToTemplate({ type: 'image-uploaded', url: data.url });
            const urlInput = document.getElementById('imageUrlInput');
            if (urlInput) urlInput.value = data.url;
            if (typeof toast === 'function') toast('Image updated');
        } catch (err) {
            console.error(err);
            if (typeof toast === 'function') toast('Image upload failed');
        } finally {
            input.value = '';
        }
    }

    function resetSelectedStyles() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'reset-styles' });
        if (typeof toast === 'function') toast('Styles reset for selection');
    }

    function addCanvasElement(type) {
        if (!window.HMS_CAN_EDIT_TEMPLATE) {
            if (typeof toast === 'function') toast('View only — you cannot edit this role page');
            return;
        }
        postToTemplate({ type: 'add-element', elementType: type });
        if (typeof toast === 'function') toast('Added ' + type + ' — drag to position');
    }

    function deleteSelectedElement() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'delete-element' });
        window.selectedElementId = null;
        const label = document.getElementById('selectedElement');
        if (label) label.textContent = 'Select an element to style';
    }

    function duplicateSelectedElement() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'duplicate-element' });
    }

    function layerSelected(direction) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'layer-element', direction: direction });
    }

    function onTemplateMessage(event) {
        const data = event.data || {};
        if (!data || data.source !== 'hms-template') return;

        if (data.type === 'editor-ready') {
            window.templateCustomizations = data.customizations || window.templateCustomizations || {};
            postToTemplate({ type: 'set-mode', mode: window.currentEditorMode });
            postToTemplate({ type: 'load-customizations', customizations: window.templateCustomizations });
        }

        if (data.type === 'element-selected') {
            window.selectedElementId = data.id;
            const label = document.getElementById('selectedElement');
            if (label) label.textContent = data.label || 'Selected element';
            const textArea = document.getElementById('elementText');
            if (textArea) textArea.value = data.text || '';
            const iconInput = document.getElementById('iconClass');
            if (iconInput) iconInput.value = data.iconClass || '';
            const urlInput = document.getElementById('imageUrlInput');
            if (urlInput) urlInput.value = data.src || '';
            if (data.styles) {
                if (data.styles.color && data.styles.color !== 'transparent' && /^#/.test(data.styles.color)) {
                    updateColorHex('textColorHex', data.styles.color);
                    const tc = document.getElementById('textColor');
                    if (tc) tc.value = data.styles.color;
                }
                if (data.styles['background-color'] && data.styles['background-color'] !== 'transparent' && /^#/.test(data.styles['background-color'])) {
                    updateColorHex('bgColorHex', data.styles['background-color']);
                    const bc = document.getElementById('bgColor');
                    if (bc) bc.value = data.styles['background-color'];
                }
                const fw = document.getElementById('fontWeight');
                if (fw && data.styles['font-weight']) fw.value = data.styles['font-weight'];
                const fs = data.styles['font-size'];
                if (fs) {
                    const n = parseInt(fs, 10);
                    if (!isNaN(n)) {
                        const range = document.getElementById('fontSizeRange');
                        const val = document.getElementById('fontSizeVal');
                        if (range) range.value = n;
                        if (val) val.value = n;
                    }
                }
                const pad = document.getElementById('padInput');
                if (pad && data.styles.padding) pad.value = data.styles.padding;
                const margin = document.getElementById('marginInput');
                if (margin && data.styles.margin) margin.value = data.styles.margin;
                const width = document.getElementById('widthInput');
                if (width && data.styles.width) width.value = data.styles.width;
                const height = document.getElementById('heightInput');
                if (height && data.styles.height) height.value = data.styles.height;
                const radius = document.getElementById('radiusInput');
                if (radius && data.styles['border-radius']) radius.value = data.styles['border-radius'];
                const opacity = document.getElementById('opacityInput');
                if (opacity && data.styles.opacity) opacity.value = data.styles.opacity;
            }
        }

        if (data.type === 'element-deselected') {
            window.selectedElementId = null;
            const label = document.getElementById('selectedElement');
            if (label) label.textContent = 'Select an element to style';
        }

        if (data.type === 'customizations-changed') {
            window.templateCustomizations = data.customizations || {};
            if (window.hmsBuilder) {
                window.hmsBuilder.state.customizations = window.templateCustomizations;
                window.hmsBuilder.markDirty();
            }
            const status = document.getElementById('autoSaveStatus');
            if (status) status.textContent = 'Unsaved changes';
        }
    }

    window.addEventListener('message', onTemplateMessage);
</script>