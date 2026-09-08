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
            <div class="flex items-center gap-1">
                <button id="undoEditorBtn" class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 flex items-center justify-center text-zinc-500 hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed" title="Undo (Ctrl+Z)" onclick="undoEditorChange()" disabled>
                    <i class="fas fa-rotate-left text-[10px]"></i>
                </button>
                <button id="redoEditorBtn" class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 flex items-center justify-center text-zinc-500 hover:text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed" title="Redo (Ctrl+Y)" onclick="redoEditorChange()" disabled>
                    <i class="fas fa-rotate-right text-[10px]"></i>
                </button>
                <button class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition-all" title="Reset selected element styles" onclick="resetSelectedStyles()">
                    <i class="fas fa-eraser text-[10px]"></i>
                </button>
                <button class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-rose-900/60 border border-zinc-700 hover:border-rose-500/50 flex items-center justify-center text-zinc-400 hover:text-rose-300 transition-all" title="Reset all design customizations to default" onclick="resetAllDesign()">
                    <i class="fas fa-rotate-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">

        {{-- ── Hotel Information ─────────────────────────────────
             One form for what the hotel is, rather than a click-and-type edit on
             each page: the name, its words, its contact details and its social
             profiles are the same wherever they appear, so they are stored once
             for the team and every page reads that one record.

             Everything here saves as a draft the moment it is typed - the
             builder's own autosave carries it - and none of it submits anything.
             That is what Submit Changes in the toolbar is for. --}}
        <div class="design-section border-b border-zinc-800/60">
            <button onclick="toggleSection('identity')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-amber-500/15 flex items-center justify-center">
                        <i class="fas fa-hotel text-[9px] text-amber-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Hotel Information</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-identity"></i>
            </button>
            <div class="section-body px-5 pb-4" id="section-identity">
                <p class="text-[10px] text-zinc-500 mb-3 leading-relaxed">
                    Shown on every page. Leave a field empty to keep your approved hotel concept's own wording.
                </p>

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Hotel name</label>
                <input type="text" id="identityName" maxlength="60" placeholder="Your hotel's name"
                       oninput="queueIdentityPush()"
                       class="w-full mb-3 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Tagline</label>
                <input type="text" id="identityTagline" maxlength="140" placeholder="A short line under the name"
                       oninput="queueIdentityPush()"
                       class="w-full mb-3 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Description</label>
                <textarea id="identityDescription" rows="4" maxlength="1200" placeholder="What the hotel is, who it serves, what makes it different."
                          oninput="queueIdentityPush()"
                          class="w-full mb-3 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 leading-relaxed focus:outline-none focus:border-amber-500/50"></textarea>

                <p class="text-[10px] font-semibold text-zinc-400 mb-1.5 uppercase tracking-wider">Contact</p>
                <input type="text" id="identityPhone" maxlength="40" placeholder="Phone"
                       oninput="queueIdentityPush()"
                       class="w-full mb-2 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">
                <input type="text" id="identityEmail" maxlength="120" placeholder="Email"
                       oninput="queueIdentityPush()"
                       class="w-full mb-2 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">
                <input type="text" id="identityAddress" maxlength="200" placeholder="Address"
                       oninput="queueIdentityPush()"
                       class="w-full mb-2 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">
                <input type="text" id="identityHours" maxlength="120" placeholder="Front desk hours"
                       oninput="queueIdentityPush()"
                       class="w-full mb-3 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-amber-500/50">

                <p class="text-[10px] font-semibold text-zinc-400 mb-1.5 uppercase tracking-wider">Social links</p>
                <p class="text-[10px] text-zinc-500 mb-2">Each one shows in the footer as its own icon. Blank rows are ignored.</p>
                <div id="identitySocialRows" class="space-y-2 mb-2"></div>
                <button type="button" onclick="addIdentitySocialRow()"
                        class="w-full py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-zinc-300 hover:border-amber-500/40 hover:text-white transition">
                    <i class="fas fa-plus mr-1"></i>Add social link
                </button>
            </div>
        </div>

        {{-- ── Hotel Branding ──────────────────────────────────
             Type is set for the whole site here, as CSS custom properties, so it
             reaches text nobody has selected. The logo, the slider images and the
             background palette already have their own tools on the canvas, so
             this section points at those rather than owning a second copy that
             could disagree with them. --}}
        <div class="design-section border-b border-zinc-800/60">
            <button onclick="toggleSection('branding')" class="section-toggle w-full flex items-center justify-between px-5 py-3.5 hover:bg-zinc-800/50 transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-md bg-fuchsia-500/15 flex items-center justify-center">
                        <i class="fas fa-swatchbook text-[9px] text-fuchsia-400"></i>
                    </div>
                    <span class="text-xs font-semibold text-zinc-300 uppercase tracking-wider">Hotel Branding</span>
                </div>
                <i class="fas fa-chevron-down text-[8px] text-zinc-600 section-chevron transition-transform" id="chevron-branding"></i>
            </button>
            <div class="section-body px-5 pb-4" id="section-branding">
                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Font family</label>
                <select id="brandFontFamily" onchange="queueIdentityPush(true)"
                        class="w-full mb-3 bg-zinc-800 border border-zinc-700 rounded-lg px-2.5 py-2 text-[11px] text-zinc-200 focus:outline-none focus:border-fuchsia-500/50"></select>

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Base font size</label>
                <div class="flex items-center gap-2 mb-3">
                    <input type="range" id="brandFontSizeRange" min="12" max="22" step="1" value="16"
                           oninput="onBrandFontSizeSlide(this.value)" class="flex-1 accent-fuchsia-500">
                    <span id="brandFontSizeLabel" class="text-[10px] text-zinc-400 w-14 text-right">Default</span>
                </div>
                <button type="button" onclick="clearBrandFontSize()"
                        class="w-full mb-3 py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-zinc-400 hover:text-white transition">Use template size</button>

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Body text colour</label>
                <div class="flex items-center gap-2 mb-3">
                    <input type="color" id="brandFontColor" value="#f5f0e8" onchange="queueIdentityPush(true)"
                           class="w-9 h-8 rounded-lg bg-zinc-800 border border-zinc-700 cursor-pointer">
                    <button type="button" onclick="clearBrandColor('color')"
                            class="flex-1 py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-zinc-400 hover:text-white transition">Use template colour</button>
                </div>

                <label class="block text-[10px] font-semibold text-zinc-400 mb-1">Heading colour</label>
                <div class="flex items-center gap-2 mb-3">
                    <input type="color" id="brandHeadingColor" value="#f5f0e8" onchange="queueIdentityPush(true)"
                           class="w-9 h-8 rounded-lg bg-zinc-800 border border-zinc-700 cursor-pointer">
                    <button type="button" onclick="clearBrandColor('headingColor')"
                            class="flex-1 py-1.5 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] font-semibold text-zinc-400 hover:text-white transition">Use template colour</button>
                </div>

                <div class="rounded-lg border border-zinc-700/70 bg-zinc-800/50 p-2.5">
                    <p class="text-[10px] text-zinc-400 leading-relaxed">
                        <i class="fas fa-circle-info text-[9px] text-fuchsia-400 mr-1"></i>
                        Logo, slider highlights and the colour palette are edited on the page itself:
                        click the logo in the header to replace it, use the pencil on the hero slider
                        for highlight images, and Background Colours for the palette. All three apply
                        to every page.
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Element Target ── -->
        <div class="px-5 py-3 border-b border-zinc-800/60 bg-zinc-900/50">
            <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-zinc-800/80 border border-zinc-700/50">
                <div class="w-6 h-6 rounded-md bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-mouse-pointer text-[9px] text-cyan-400"></i>
                </div>
                <span class="text-xs text-zinc-400" id="selectedElement">Select an element to style</span>
            </div>
            <p id="selectionHint" class="text-[9px] text-zinc-600 mt-2 leading-relaxed">
                Click text, a button, image, or icon. Alt+click selects its layout container.
            </p>
            <button id="selectParentBtn" type="button" onclick="selectParentElement()" class="hidden w-full mt-2 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/20 text-[10px] font-semibold text-cyan-300 hover:bg-cyan-500/15 transition">
                <i class="fas fa-arrow-up mr-1"></i><span id="selectParentLabel">Select parent container</span>
            </button>
            <div id="movementControls" class="hidden mt-2 rounded-lg border border-zinc-700/70 bg-zinc-800/50 p-2.5">
                <div class="flex items-start gap-2">
                    <i class="fas fa-up-down-left-right text-[9px] text-cyan-400 mt-0.5" id="movementIcon"></i>
                    <div class="min-w-0 flex-1">
                        <p id="movementStatus" class="text-[10px] font-semibold text-zinc-300">Position &amp; align</p>
                        <p id="movementHelp" class="text-[9px] leading-relaxed text-zinc-500 mt-0.5">Drag the cyan Move handle to reposition. Pink guides snap it into alignment. Arrow keys nudge (Shift = 10px).</p>
                    </div>
                </div>
                <div id="movementAlignButtons" class="grid grid-cols-3 gap-1 mt-2">
                    <button type="button" onclick="alignSelectedElement('left')" class="add-el-btn" title="Align left"><i class="fas fa-align-left"></i>Left</button>
                    <button type="button" onclick="alignSelectedElement('center')" class="add-el-btn" title="Center horizontally"><i class="fas fa-align-center"></i>Center</button>
                    <button type="button" onclick="alignSelectedElement('right')" class="add-el-btn" title="Align right"><i class="fas fa-align-right"></i>Right</button>
                    <button type="button" onclick="alignSelectedElement('top')" class="add-el-btn" title="Align top"><i class="fas fa-arrow-up"></i>Top</button>
                    <button type="button" onclick="alignSelectedElement('middle')" class="add-el-btn" title="Center vertically"><i class="fas fa-arrows-up-down"></i>Middle</button>
                    <button type="button" onclick="alignSelectedElement('bottom')" class="add-el-btn" title="Align bottom"><i class="fas fa-arrow-down"></i>Bottom</button>
                </div>
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
                <p class="text-[10px] text-zinc-500 mb-2">Adds objects onto the live template. Select one, then use its cyan Move handle and resize points.</p>
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

    function resetAllDesign() {
        if (!confirm('Reset all design customizations? This will restore the page to its original default appearance.')) return;
        postToTemplate({ type: 'reset-all' });
        window.selectedElementId = null;
        window.templateCustomizations = {};
        const label = document.getElementById('selectedElement');
        if (label) label.textContent = 'Select an element to style';
        const parentBtn = document.getElementById('selectParentBtn');
        if (parentBtn) parentBtn.classList.add('hidden');
        const movement = document.getElementById('movementControls');
        if (movement) movement.classList.add('hidden');
        if (typeof toast === 'function') toast('Design reset to defaults');
    }

    function undoEditorChange() {
        postToTemplate({ type: 'undo' });
    }

    function redoEditorChange() {
        postToTemplate({ type: 'redo' });
    }

    function updateHistoryButtons(canUndo, canRedo) {
        const undoBtn = document.getElementById('undoEditorBtn');
        const redoBtn = document.getElementById('redoEditorBtn');
        if (undoBtn) undoBtn.disabled = !canUndo;
        if (redoBtn) redoBtn.disabled = !canRedo;
    }

    function addCanvasElement(type) {
        if (!window.HMS_CAN_EDIT_TEMPLATE) {
            if (typeof toast === 'function') toast('View only — you cannot edit this role page');
            return;
        }
        postToTemplate({ type: 'add-element', elementType: type });
        if (typeof toast === 'function') toast('Added ' + type + ' — use the cyan Move handle');
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

    function selectParentElement() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'select-parent' });
    }

    function alignSelectedElement(direction) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'align-element', direction: direction });
    }

    function updateMovementControls() {
        const controls = document.getElementById('movementControls');
        if (!controls) return;
        controls.classList.remove('hidden');
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
            // Draw the Hotel Information form from the site itself, so it shows
            // what a teammate saved rather than a copy this panel kept.
            postToTemplate({ type: 'request-site-identity' });
        }

        if (data.type === 'site-identity') {
            fillIdentityForm(data);
        }

        if (data.type === 'history-state') {
            updateHistoryButtons(!!data.canUndo, !!data.canRedo);
        }

        if (data.type === 'element-selected') {
            window.selectedElementId = data.id;
            const label = document.getElementById('selectedElement');
            if (label) label.textContent = (data.label || 'Selected element') + (data.tag ? ' · <' + data.tag + '>' : '');
            const parentBtn = document.getElementById('selectParentBtn');
            const parentLabel = document.getElementById('selectParentLabel');
            if (parentBtn) parentBtn.classList.toggle('hidden', !data.canSelectParent);
            if (parentLabel) parentLabel.textContent = data.parentLabel
                ? 'Select parent: ' + data.parentLabel
                : 'Select parent container';
            updateMovementControls();
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
            const parentBtn = document.getElementById('selectParentBtn');
            if (parentBtn) parentBtn.classList.add('hidden');
            const movement = document.getElementById('movementControls');
            if (movement) movement.classList.add('hidden');
        }

        if (data.type === 'customizations-changed') {
            window.templateCustomizations = data.customizations || {};
            if (window.hmsBuilder) {
                window.hmsBuilder.state.customizations = window.templateCustomizations;
                window.hmsBuilder.markDirty();
            }
            if (typeof setSaveDraftUnsaved === 'function') setSaveDraftUnsaved(true);
            const status = document.getElementById('autoSaveStatus');
            if (status) status.textContent = 'Saving…';
            // Auto-persist so teammates pick up the change on their next poll.
            // Routed through the builder so it sends this role's own row plus our
            // edits — posting the merged set here would let a stale shared key
            // overwrite a teammate's newer work.
            clearTimeout(window._hmsSiteContentSaveTimer);
            window._hmsSiteContentSaveTimer = setTimeout(async function () {
                try {
                    if (!window.hmsBuilder || typeof window.hmsBuilder.autosave !== 'function') return;
                    await window.hmsBuilder.autosave();
                    window.templateSyncVersion = window.hmsBuilder.syncVersion || window.templateSyncVersion;
                    if (typeof setSaveDraftUnsaved === 'function') setSaveDraftUnsaved(false);
                    const st = document.getElementById('autoSaveStatus');
                    if (st) st.textContent = 'Auto-saved · Ctrl+S to publish';
                } catch (e) { /* ignore — user can still manually save */ }
            }, 400);
        }

        if (data.type === 'request-save-draft') {
            if (typeof saveTemplateDraft === 'function') {
                saveTemplateDraft(false);
            } else if (window.hmsBuilder) {
                window.hmsBuilder.save(false);
            }
        }
    }

    /* ══════ HOTEL INFORMATION & BRANDING ══════
       The form writes into the live site through the editor bridge, which files
       the change under the same customizations everything else in the builder
       saves. That is deliberate: it means identity edits ride the builder's
       existing autosave (draft only) and its Submit Changes, instead of needing
       a save path of their own that could disagree with them.

       Keystrokes are debounced because every push repaints the iframe; a
       dropdown or a colour swatch pushes at once, since there is no typing to
       wait for. */

    const IDENTITY_TEXT_FIELDS = {
        identityName: 'name',
        identityTagline: 'tagline',
        identityDescription: 'description',
        identityPhone: 'phone',
        identityEmail: 'email',
        identityAddress: 'address',
        identityHours: 'hours',
    };

    /* Offered in the dropdown; mirrors HMSSiteContent.FONT_FAMILIES. Any CSS
       stack still works if one arrives from a teammate's save. */
    const BRAND_FONTS = [
        { id: '', label: 'Template default' },
        { id: "'Inter', system-ui, sans-serif", label: 'Inter' },
        { id: "'Playfair Display', Georgia, serif", label: 'Playfair Display' },
        { id: "'Montserrat', system-ui, sans-serif", label: 'Montserrat' },
        { id: "'Lora', Georgia, serif", label: 'Lora' },
        { id: "'Poppins', system-ui, sans-serif", label: 'Poppins' },
        { id: "Georgia, 'Times New Roman', serif", label: 'Georgia' },
        { id: "system-ui, -apple-system, 'Segoe UI', sans-serif", label: 'System' },
    ];

    const SOCIAL_NETWORK_OPTIONS = [
        ['facebook', 'Facebook'],
        ['instagram', 'Instagram'],
        ['x', 'X'],
        ['tiktok', 'TikTok'],
        ['youtube', 'YouTube'],
        ['linkedin', 'LinkedIn'],
        ['website', 'Website'],
    ];

    let identityPushTimer = null;
    // Blank means "the template's own size"; the slider cannot express that, so
    // it is tracked here and only becomes a real value once the slider moves.
    let brandFontSize = '';

    function identityValue(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function fillIdentityForm(data) {
        const info = (data && data.hotelInfo) || {};

        // The hotel's name is owned by whoever owns Home, like the header edit
        // it mirrors. Show the current name to everyone, but only let that role
        // change it, rather than accepting typing the server would discard.
        const nameInput = document.getElementById('identityName');
        if (nameInput) {
            const mayRename = data && data.canEditName === true;
            nameInput.disabled = !mayRename;
            nameInput.title = mayRename ? '' : 'Front Desk names the hotel for the team';
            nameInput.classList.toggle('opacity-50', !mayRename);
        }
        Object.keys(IDENTITY_TEXT_FIELDS).forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            // Never overwrite the box someone is typing in: the frame answers
            // this request after every save, including the one their own
            // keystroke just triggered.
            if (document.activeElement === el) return;
            el.value = info[IDENTITY_TEXT_FIELDS[id]] || '';
        });

        const select = document.getElementById('brandFontFamily');
        if (select && !select.options.length) {
            BRAND_FONTS.forEach((font) => {
                const option = document.createElement('option');
                option.value = font.id;
                option.textContent = font.label;
                select.appendChild(option);
            });
        }

        const type = (data && data.typography) || {};
        if (select && document.activeElement !== select) {
            // A family a teammate typed that is not in the list still has to show
            // as the current choice rather than silently reading as default.
            if (type.family && !BRAND_FONTS.some((font) => font.id === type.family)) {
                const option = document.createElement('option');
                option.value = type.family;
                option.textContent = 'Custom';
                select.appendChild(option);
            }
            select.value = type.family || '';
        }

        brandFontSize = type.size || '';
        const range = document.getElementById('brandFontSizeRange');
        const label = document.getElementById('brandFontSizeLabel');
        const px = parseInt(String(brandFontSize).replace(/[^0-9]/g, ''), 10);
        if (range && !Number.isNaN(px)) range.value = String(px);
        if (label) label.textContent = brandFontSize ? brandFontSize : 'Default';

        const bodyColor = document.getElementById('brandFontColor');
        if (bodyColor && type.color) bodyColor.value = type.color;
        const headingColor = document.getElementById('brandHeadingColor');
        if (headingColor && type.headingColor) headingColor.value = type.headingColor;

        renderIdentitySocialRows((data && data.socialLinks) || []);
    }

    function renderIdentitySocialRows(links) {
        const wrap = document.getElementById('identitySocialRows');
        if (!wrap) return;
        // Leave the rows alone while a link is being typed, for the same reason
        // the text fields are left alone above.
        if (wrap.contains(document.activeElement)) return;

        wrap.innerHTML = '';
        const list = Array.isArray(links) && links.length ? links : [{ network: 'facebook', url: '' }];
        list.forEach((link) => appendIdentitySocialRow(link.network, link.url));
    }

    function appendIdentitySocialRow(network, url) {
        const wrap = document.getElementById('identitySocialRows');
        if (!wrap) return;

        const row = document.createElement('div');
        row.className = 'flex items-center gap-1.5 identity-social-row';

        const select = document.createElement('select');
        select.className = 'w-24 shrink-0 bg-zinc-800 border border-zinc-700 rounded-lg px-1.5 py-1.5 text-[10px] text-zinc-200 focus:outline-none focus:border-amber-500/50';
        SOCIAL_NETWORK_OPTIONS.forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            select.appendChild(option);
        });
        select.value = network || 'facebook';
        select.addEventListener('change', () => queueIdentityPush(true));

        const input = document.createElement('input');
        input.type = 'text';
        input.placeholder = 'https://...';
        input.maxLength = 300;
        input.value = url || '';
        input.className = 'flex-1 min-w-0 bg-zinc-800 border border-zinc-700 rounded-lg px-2 py-1.5 text-[10px] text-zinc-200 focus:outline-none focus:border-amber-500/50';
        input.addEventListener('input', () => queueIdentityPush());

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'w-7 h-7 shrink-0 rounded-lg bg-zinc-800 border border-zinc-700 text-[10px] text-zinc-500 hover:text-rose-300 hover:border-rose-500/40 transition';
        remove.innerHTML = '<i class="fas fa-xmark"></i>';
        remove.title = 'Remove this link';
        remove.addEventListener('click', () => {
            row.remove();
            queueIdentityPush(true);
        });

        row.appendChild(select);
        row.appendChild(input);
        row.appendChild(remove);
        wrap.appendChild(row);
    }

    function addIdentitySocialRow() {
        appendIdentitySocialRow('facebook', '');
    }

    function collectIdentitySocialLinks() {
        return Array.from(document.querySelectorAll('#identitySocialRows .identity-social-row'))
            .map((row) => ({
                network: row.querySelector('select') ? row.querySelector('select').value : 'website',
                url: row.querySelector('input') ? row.querySelector('input').value.trim() : '',
            }))
            .filter((link) => link.url !== '');
    }

    function onBrandFontSizeSlide(value) {
        brandFontSize = String(value) + 'px';
        const label = document.getElementById('brandFontSizeLabel');
        if (label) label.textContent = brandFontSize;
        queueIdentityPush();
    }

    function clearBrandFontSize() {
        brandFontSize = '';
        const label = document.getElementById('brandFontSizeLabel');
        if (label) label.textContent = 'Default';
        queueIdentityPush(true);
    }

    function clearBrandColor(field) {
        pushIdentity({ [field]: '' });
    }

    /** @param {boolean} [immediate] true for a dropdown, a swatch or a removal. */
    function queueIdentityPush(immediate) {
        if (identityPushTimer) clearTimeout(identityPushTimer);
        if (immediate) {
            pushIdentity();
            return;
        }
        identityPushTimer = setTimeout(() => pushIdentity(), 450);
    }

    /**
     * Hand the whole form to the site in one message.
     *
     * @param {object} [typeOverrides] fields to force, used by the "use template
     *   default" buttons, which have to send an empty string rather than let the
     *   colour input's own value stand in for "unset".
     */
    function pushIdentity(typeOverrides) {
        if (identityPushTimer) {
            clearTimeout(identityPushTimer);
            identityPushTimer = null;
        }
        if (!window.HMS_CAN_EDIT_TEMPLATE) return;

        const hotelInfo = {};
        Object.keys(IDENTITY_TEXT_FIELDS).forEach((id) => {
            hotelInfo[IDENTITY_TEXT_FIELDS[id]] = identityValue(id);
        });

        const familySelect = document.getElementById('brandFontFamily');
        const typography = Object.assign({
            family: familySelect ? familySelect.value : '',
            size: brandFontSize,
            color: identityValue('brandFontColor'),
            headingColor: identityValue('brandHeadingColor'),
        }, typeOverrides || {});

        if (typeOverrides) {
            // Keep the cleared field cleared on the next read-back, not repainted
            // from the colour input that still holds its last swatch.
            Object.keys(typeOverrides).forEach((field) => {
                const el = field === 'color'
                    ? document.getElementById('brandFontColor')
                    : (field === 'headingColor' ? document.getElementById('brandHeadingColor') : null);
                if (el && typeOverrides[field] === '') el.value = '#f5f0e8';
            });
        }

        postToTemplate({
            type: 'set-site-identity',
            hotelInfo: hotelInfo,
            socialLinks: collectIdentitySocialLinks(),
            typography: typography,
        });
    }

    window.addEventListener('message', onTemplateMessage);
</script>