{{-- ══════════════════════════════════════════════════════════════
     DESIGN PANEL

     Written for students who have never used a design tool, so the panel
     is organised the way the work actually happens rather than the way CSS
     is grouped:

       1. Hotel details   - the facts of the hotel, the same on every page.
       2. What's selected - one card, pinned to the top while you scroll, so
                            it is always clear what the controls below act on.
       3. Style it        - the controls themselves, common ones open, the
                            occasional ones folded away until asked for.

     Two colours carry that split (amber = the whole hotel, cyan = the one
     thing you clicked) instead of a different colour per section, which read
     as decoration rather than meaning.

     Every control still calls exactly the same function it always did; the
     element ids the template bridge and the builder shell look up are
     unchanged. --}}
<aside id="hmsDesignPanel" class="no-selection h-full flex flex-col text-zinc-200 overflow-hidden bg-zinc-900">

    <!-- ══════ Panel Header ══════ -->
    <div class="px-5 py-4 border-b border-zinc-800 shrink-0 bg-zinc-900">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20 shrink-0">
                    <i class="fas fa-palette text-white text-xs"></i>
                </div>
                @php
                    /* Which part of the site this student may actually write. The
                       canvas opens on it, and saying so here stops the panel reading
                       as a set of controls over the whole site when it is not. */
                    $panelRole = $builderRole ?? 'front_desk';
                    $panelPages = \App\Support\HotelTemplateBuilder::editablePagesForRole($panelRole);
                    $panelPageLabels = [
                        'home' => 'Home', 'rooms' => 'Rooms', 'restaurant' => 'Restaurant',
                        'amenities' => 'Amenities', 'experience' => 'Highlights',
                    ];
                    $panelScope = implode(' & ', array_map(
                        fn ($page) => $panelPageLabels[$page] ?? ucfirst($page),
                        $panelPages
                    ));
                @endphp
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-white tracking-wide">Design Panel</h2>
                    <p class="text-[0.6875rem] text-zinc-500 mt-0.5 truncate">
                        {{ $panelScope !== '' ? 'Editing ' . $panelScope : 'Style & customize elements' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- The four things you reach for when something goes wrong, kept
             together and labelled, rather than four unlabelled icons. --}}
        <div class="flex items-center gap-1.5 mt-3">
            <div class="flex items-center gap-1 p-1 rounded-xl bg-zinc-800/70 border border-zinc-700/60">
                <button id="undoEditorBtn" class="panel-tool-btn" title="Undo (Ctrl+Z)" onclick="undoEditorChange()" disabled>
                    <i class="fas fa-rotate-left"></i>
                </button>
                <button id="redoEditorBtn" class="panel-tool-btn" title="Redo (Ctrl+Y)" onclick="redoEditorChange()" disabled>
                    <i class="fas fa-rotate-right"></i>
                </button>
            </div>
            <button class="panel-undo-label" title="Undo the last change you made" onclick="undoEditorChange()">Undo</button>
            <div class="flex-1"></div>
            {{-- Words, not bare icons: an eraser and a circular arrow told a
                 non-designer nothing, and one of them wipes the whole page. --}}
            <button class="panel-text-btn" title="Put the selected item back to how it looked originally" onclick="resetSelectedStyles()">
                <i class="fas fa-eraser"></i>Clear style
            </button>
            <button class="panel-text-btn panel-text-btn--danger" title="Put the whole page back to the original design" onclick="resetAllDesign()">
                <i class="fas fa-rotate-left"></i>Reset page
            </button>
        </div>
    </div>

    <!-- ══════ Scrollable Content ══════ -->
    <div class="flex-1 overflow-y-auto custom-scrollbar">

        @if($panelScope !== '')
        {{-- ════════ ZONE 1 · HOTEL DETAILS ════════
             Everything here is true of the hotel itself, so it shows on every
             page at once. Amber marks that: change one of these and you have
             changed the whole site, not the thing you clicked. --}}
        <div class="zone-head zone-head--hotel">
            <span class="zone-dot"></span>
            <span class="zone-title">Your hotel</span>
            <span class="zone-note">Shows on every page</span>
        </div>

        {{-- ── Hotel Information ─────────────────────────────────
             One form for what the hotel is, rather than a click-and-type edit on
             each page: the name, its words, its contact details and its social
             profiles are the same wherever they appear, so they are stored once
             for the team and every page reads that one record.

             Everything here saves as a draft the moment it is typed - the
             builder's own autosave carries it - and none of it submits anything.
             That is what Submit Changes in the toolbar is for. --}}
        <div class="design-section">
            <button onclick="toggleSection('identity')" class="section-toggle">
                <span class="section-icon section-icon--hotel"><i class="fas fa-hotel"></i></span>
                <span class="section-label">
                    Hotel information
                    <span class="section-sub">Name, story, contact details</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-identity" style="transform: rotate(-90deg)"></i>
            </button>
            <div class="section-body hidden" id="section-identity">
                <p class="section-hint">
                    Shown on every page. Leave a field empty to keep your approved hotel concept's own wording.
                </p>

                <label class="field-label settings-label">Hotel name</label>
                <input type="text" id="identityName" maxlength="60" placeholder="Your hotel's name"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-3">

                <label class="field-label settings-label">Tagline</label>
                <input type="text" id="identityTagline" maxlength="140" placeholder="A short line under the name"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-3">

                <label class="field-label settings-label">Description</label>
                <textarea id="identityDescription" rows="4" maxlength="1200" placeholder="What the hotel is, who it serves, what makes it different."
                          oninput="queueIdentityPush()"
                          class="style-input style-input--hotel leading-relaxed mb-4"></textarea>

                <p class="field-group-label">Contact</p>
                <input type="text" id="identityPhone" maxlength="40" placeholder="Phone"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-2">
                <input type="text" id="identityEmail" maxlength="120" placeholder="Email"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-2">
                <input type="text" id="identityAddress" maxlength="200" placeholder="Address"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-2">
                <input type="text" id="identityHours" maxlength="120" placeholder="Front desk hours"
                       oninput="queueIdentityPush()"
                       class="style-input style-input--hotel mb-4">

                <p class="field-group-label">Social links</p>
                <p class="section-hint">Each one shows in the footer as its own icon. Blank rows are ignored.</p>
                <div id="identitySocialRows" class="space-y-2 mb-2"></div>
                <button type="button" onclick="addIdentitySocialRow()" class="ghost-btn ghost-btn--hotel">
                    <i class="fas fa-plus"></i>Add social link
                </button>
            </div>
        </div>

        {{-- ── Hotel Branding ──────────────────────────────────
             Type is set for the whole site here, as CSS custom properties, so it
             reaches text nobody has selected. The logo, the slider images and the
             background palette already have their own tools on the canvas, so
             this section points at those rather than owning a second copy that
             could disagree with them. --}}
        <div class="design-section">
            <button onclick="toggleSection('branding')" class="section-toggle">
                <span class="section-icon section-icon--hotel"><i class="fas fa-swatchbook"></i></span>
                <span class="section-label">
                    Fonts &amp; colours
                    <span class="section-sub">The look of the whole site</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-branding" style="transform: rotate(-90deg)"></i>
            </button>
            <div class="section-body hidden" id="section-branding">
                <label class="field-label settings-label">Font family</label>
                <select id="brandFontFamily" onchange="queueIdentityPush(true)"
                        class="style-input style-input--hotel mb-4"></select>

                <label class="field-label settings-label">Base text size</label>
                <div class="flex items-center gap-2.5 mb-2">
                    <input type="range" id="brandFontSizeRange" min="12" max="22" step="1" value="16"
                           oninput="onBrandFontSizeSlide(this.value)" class="flex-1 accent-amber-400">
                    <span id="brandFontSizeLabel" class="value-pill">Default</span>
                </div>
                <button type="button" onclick="clearBrandFontSize()" class="ghost-btn mb-4">Use template size</button>

                <label class="field-label settings-label">Body text colour</label>
                <div class="flex items-center gap-2 mb-4">
                    <input type="color" id="brandFontColor" value="#f5f0e8" onchange="queueIdentityPush(true)"
                           class="color-swatch">
                    <button type="button" onclick="clearBrandColor('color')" class="ghost-btn flex-1">Use template colour</button>
                </div>

                <label class="field-label settings-label">Heading colour</label>
                <div class="flex items-center gap-2 mb-4">
                    <input type="color" id="brandHeadingColor" value="#f5f0e8" onchange="queueIdentityPush(true)"
                           class="color-swatch">
                    <button type="button" onclick="clearBrandColor('headingColor')" class="ghost-btn flex-1">Use template colour</button>
                </div>

                {{-- The palette used to be reachable only through a pill floating over
                     the canvas, which meant scrolling to find it before any colour
                     could be picked. Same dialog, one click, from where the rest of
                     the site-wide design already lives.

                     Front Desk only: the background colours and the logo are the
                     whole site's, not one page's, so the role that owns the site's
                     name and navigation owns these too. Every other role still sees
                     them applied — it just cannot change them from here. --}}
                @if($panelRole === 'front_desk')
                <button type="button" onclick="openSiteColours()" class="feature-btn feature-btn--hotel mb-3">
                    <i class="fas fa-palette"></i>
                    Background colours
                </button>

                <div class="note-box">
                    <i class="fas fa-circle-info note-box-icon"></i>
                    <p>
                        The logo and the slider highlights are edited on the page itself: click the
                        logo in the header to replace it, and use the pencil on the hero slider for
                        its images. Both apply to every page, as the colours above do.
                    </p>
                </div>
                @else
                <div class="note-box">
                    <i class="fas fa-circle-info note-box-icon"></i>
                    <p>
                        The hotel logo and the site's background colours are set by Front Desk, for
                        the whole site at once. Ask them for a change; the fonts and text colours
                        above are still yours.
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ════════ ZONE 2 · THE THING YOU CLICKED ════════ --}}
        <div class="zone-head zone-head--element">
            <span class="zone-dot"></span>
            <span class="zone-title">This page</span>
            <span class="zone-note">Click something first</span>
        </div>

        {{-- ── What's selected ──
             Pinned while the rest of the panel scrolls: every control below only
             acts on whatever this card names, and a student who has scrolled past
             it otherwise has no way to remember what that is. --}}
        <div class="target-card-wrap">
            <div class="target-card">
                <div class="target-row">
                    <div class="target-icon"><i class="fas fa-mouse-pointer"></i></div>
                    <div class="min-w-0 flex-1">
                        <p class="target-eyebrow">Now editing</p>
                        <span class="target-name" id="selectedElement">Select an element to style</span>
                    </div>
                </div>

                <p id="selectionHint" class="target-hint">
                    Click any words, button or picture on the page to change it here.
                </p>

                <button id="selectParentBtn" type="button" onclick="selectParentElement()" class="hidden parent-btn">
                    <i class="fas fa-arrow-up"></i><span id="selectParentLabel">Select the box around it</span>
                </button>

                {{-- The Top / Middle / Bottom and Left / Centre / Right buttons that
                     sat here slid a template item to the edge of its section, onto
                     whatever was there, and it stayed there on phones. Text
                     alignment lives under Text style; moving is the drag handle. --}}
                <div id="movementControls" class="hidden move-box">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-up-down-left-right move-icon" id="movementIcon"></i>
                        <div class="min-w-0 flex-1">
                            <p id="movementStatus" class="move-title">Moving it</p>
                            <p id="movementHelp" class="move-help">Drag the “Move” tab above it on the page. Pull a corner dot to make it bigger or smaller.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-1.5 mt-2.5">
                    <button type="button" onclick="duplicateSelectedElement()" class="ghost-btn">
                        <i class="fas fa-copy"></i>Duplicate
                    </button>
                    <button type="button" onclick="deleteSelectedElement()" class="ghost-btn ghost-btn--danger">
                        <i class="fas fa-trash"></i>Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Add to page ── -->
        <div class="design-section">
            <button onclick="toggleSection('add')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-plus"></i></span>
                <span class="section-label">
                    Add something new
                    <span class="section-sub">Text, buttons, images, cards</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-add" style="transform: rotate(-90deg)"></i>
            </button>
            <div class="section-body hidden" id="section-add">
                <p class="section-hint">Puts it on the page where you are looking. Drag the “Move” tab above it to place it.</p>
                {{-- Text field, Icon and Container are gone: a typing box a guest
                     cannot send anywhere, an icon only changeable by its code name,
                     and an empty dashed box did nothing for a hotel page. --}}
                <div class="grid grid-cols-2 gap-1.5">
                    <button type="button" onclick="addCanvasElement('text')" class="add-el-btn"><i class="fas fa-font"></i>Words</button>
                    <button type="button" onclick="addCanvasElement('button')" class="add-el-btn"><i class="fas fa-square"></i>Button</button>
                    <button type="button" onclick="addCanvasElement('image')" class="add-el-btn"><i class="fas fa-image"></i>Picture</button>
                    <button type="button" onclick="addCanvasElement('card')" class="add-el-btn"><i class="fas fa-id-card"></i>Card</button>
                </div>
            </div>
        </div>

        <!-- ── Text & icon ── -->
        <div class="design-section needs-selection">
            <button onclick="toggleSection('content')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-align-left"></i></span>
                <span class="section-label">
                    Words
                    <span class="section-sub">Change what it says</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-content"></i>
            </button>
            {{-- The Font Awesome icon-name box and the H1-H6 heading buttons are
                 gone. The first needed a code name typed in; the second rebuilt the
                 element under a new tag, which was never saved, so the change was
                 lost on the next reload. Size is under Text style. --}}
            <div class="section-body" id="section-content">
                <textarea id="elementText" rows="3" class="style-input resize-y min-h-[4.5rem]" placeholder="Click some words on the page, then type the new words here"
                    oninput="applyTextContent(this.value)"></textarea>
                <p class="section-hint mt-1.5">Tip: you can also double-click the words on the page and type straight into them.</p>
            </div>
        </div>

        <!-- ── Font style ── -->
        <div class="design-section needs-selection">
            <button onclick="toggleSection('typography')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-font"></i></span>
                <span class="section-label">
                    Text style
                    <span class="section-sub">Font, size, bold, alignment</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-typography"></i>
            </button>
            <div class="section-body" id="section-typography">
                <!-- Font Family: only fonts the hotel site actually loads. Manrope,
                     SF Pro Display and Helvetica Neue were offered before and are not
                     on the site, so picking one quietly showed a different font. -->
                <div class="mb-4">
                    <label class="field-label settings-label">Font</label>
                    <div class="relative">
                        <select id="fontFamily" class="style-input appearance-none pr-8 cursor-pointer" onchange="applyStyle('font-family', this.value)">
                            <option value="inherit">Same as the rest of the site</option>
                            <option value="'Playfair Display', Georgia, serif">Playfair Display (elegant)</option>
                            <option value="'Lora', Georgia, serif">Lora (classic)</option>
                            <option value="Georgia, 'Times New Roman', serif">Georgia (classic)</option>
                            <option value="'Montserrat', system-ui, sans-serif">Montserrat (modern)</option>
                            <option value="'Poppins', system-ui, sans-serif">Poppins (friendly)</option>
                            <option value="'Inter', system-ui, sans-serif">Inter (clean)</option>
                        </select>
                        <i class="fas fa-chevron-down select-caret"></i>
                    </div>
                </div>

                <!-- Font Weight -->
                <div class="mb-4">
                    <label class="field-label settings-label">Thickness</label>
                    <div class="relative">
                        <select id="fontWeight" class="style-input appearance-none pr-8 cursor-pointer" onchange="applyStyle('font-weight', this.value)">
                            <option value="300">Light</option>
                            <option value="400">Regular</option>
                            <option value="500">Medium</option>
                            <option value="600">Semi Bold</option>
                            <option value="700" selected>Bold</option>
                            <option value="800">Extra Bold</option>
                        </select>
                        <i class="fas fa-chevron-down select-caret"></i>
                    </div>
                </div>

                <!-- Font Size -->
                <div class="mb-4">
                    <label class="field-label settings-label">Size</label>
                    <div class="flex items-center gap-2">
                        <input id="fontSizeRange" type="range" min="10" max="96" value="16" class="flex-1 accent-cyan-500"
                            oninput="document.getElementById('fontSizeVal').value = this.value; applyStyle('font-size', this.value + 'px')">
                        <input id="fontSizeVal" type="number" min="8" max="200" value="16" class="style-input w-16 text-center"
                            onchange="document.getElementById('fontSizeRange').value = this.value; applyStyle('font-size', this.value + 'px')">
                    </div>
                </div>

                <!-- Alignment -->
                <div class="mb-4">
                    <label class="field-label settings-label">Alignment</label>
                    <div class="flex gap-1.5">
                        <button type="button" onclick="applyStyle('text-align','left')" class="style-toggle-btn" title="Left"><i class="fas fa-align-left"></i></button>
                        <button type="button" onclick="applyStyle('text-align','center')" class="style-toggle-btn" title="Center"><i class="fas fa-align-center"></i></button>
                        <button type="button" onclick="applyStyle('text-align','right')" class="style-toggle-btn" title="Right"><i class="fas fa-align-right"></i></button>
                        <button type="button" onclick="applyStyle('text-align','justify')" class="style-toggle-btn" title="Justify"><i class="fas fa-align-justify"></i></button>
                    </div>
                </div>

                <!-- Style Toggles -->
                <div>
                    <label class="field-label settings-label">Emphasis</label>
                    <div class="flex gap-1.5">
                        <button onclick="toggleInlineStyle('font-style', 'italic')" class="style-toggle-btn" title="Italic">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button onclick="toggleInlineStyle('text-decoration', 'underline')" class="style-toggle-btn" title="Underline">
                            <i class="fas fa-underline"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Colours ── -->
        <div class="design-section needs-selection">
            <button onclick="toggleSection('colors')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-droplet"></i></span>
                <span class="section-label">
                    Colours
                    <span class="section-sub">Text and background</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-colors"></i>
            </button>
            <div class="section-body" id="section-colors">
                {{-- Pick from the swatches or the colour box. The hex-code fields
                     that sat beside the box are gone: a colour is chosen by looking,
                     not by typing #e4e4e7. The swatches are hotel tones (cream, gold,
                     wine, forest) rather than the admin-panel greys they used to be. --}}
                <!-- Text Color -->
                <div class="mb-4">
                    <label class="field-label settings-label">Colour of the words</label>
                    <div class="swatch-row">
                        <button onclick="setColor('text','#ffffff')" class="color-preset-swatch" style="background:#ffffff" title="White"></button>
                        <button onclick="setColor('text','#f5f0e8')" class="color-preset-swatch" style="background:#f5f0e8" title="Cream"></button>
                        <button onclick="setColor('text','#1a1a1a')" class="color-preset-swatch" style="background:#1a1a1a" title="Black"></button>
                        <button onclick="setColor('text','#6b7280')" class="color-preset-swatch" style="background:#6b7280" title="Grey"></button>
                        <button onclick="setColor('text','#c9a84c')" class="color-preset-swatch" style="background:#c9a84c" title="Gold"></button>
                        <button onclick="setColor('text','#7f1d1d')" class="color-preset-swatch" style="background:#7f1d1d" title="Wine red"></button>
                        <button onclick="setColor('text','#1b4332')" class="color-preset-swatch" style="background:#1b4332" title="Forest green"></button>
                        <button onclick="setColor('text','#1e3a8a')" class="color-preset-swatch" style="background:#1e3a8a" title="Navy blue"></button>
                        <label class="color-more" title="Pick any colour">
                            <input type="color" id="textColor" value="#f5f0e8" class="color-swatch" onchange="applyStyle('color', this.value)">
                            <span>More</span>
                        </label>
                    </div>
                </div>

                <!-- Background Color -->
                <div>
                    <label class="field-label settings-label">Colour behind it</label>
                    <div class="swatch-row">
                        <button onclick="setColor('bg','transparent')" class="color-preset-swatch swatch-transparent" title="None (see-through)"></button>
                        <button onclick="setColor('bg','#ffffff')" class="color-preset-swatch" style="background:#ffffff" title="White"></button>
                        <button onclick="setColor('bg','#f5f0e8')" class="color-preset-swatch" style="background:#f5f0e8" title="Cream"></button>
                        <button onclick="setColor('bg','#1a1a1a')" class="color-preset-swatch" style="background:#1a1a1a" title="Black"></button>
                        <button onclick="setColor('bg','#c9a84c')" class="color-preset-swatch" style="background:#c9a84c" title="Gold"></button>
                        <button onclick="setColor('bg','#7f1d1d')" class="color-preset-swatch" style="background:#7f1d1d" title="Wine red"></button>
                        <button onclick="setColor('bg','#1b4332')" class="color-preset-swatch" style="background:#1b4332" title="Forest green"></button>
                        <button onclick="setColor('bg','#1e3a8a')" class="color-preset-swatch" style="background:#1e3a8a" title="Navy blue"></button>
                        <label class="color-more" title="Pick any colour">
                            <input type="color" id="bgColor" value="#1a1a1a" class="color-swatch" onchange="applyStyle('background-color', this.value)">
                            <span>More</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Logo & images ── -->
        <div class="design-section needs-selection">
            <button onclick="toggleSection('media')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-image"></i></span>
                <span class="section-label">
                    Pictures
                    <span class="section-sub">Swap a photo or the logo</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-media" style="transform: rotate(-90deg)"></i>
            </button>
            <div class="section-body hidden" id="section-media">
                <p class="section-hint">Click the picture, logo, or hero background on the page first, then upload the one you want in its place.</p>
                <input type="file" id="designImageInput" accept="image/*" class="hidden" onchange="uploadSelectedImage(this)">
                <button type="button" onclick="document.getElementById('designImageInput').click()" class="feature-btn mb-3">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload a picture
                </button>
                <input id="imageUrlInput" type="url" class="style-input" placeholder="Or paste a picture link (https://...)"
                    onchange="applyImageUrl(this.value)">
            </div>
        </div>

        <!-- ── Size & spacing ── -->
        <div class="design-section needs-selection">
            <button onclick="toggleSection('spacing')" class="section-toggle">
                <span class="section-icon"><i class="fas fa-shapes"></i></span>
                <span class="section-label">
                    Corners &amp; fading
                    <span class="section-sub">Round the corners, make it lighter</span>
                </span>
                <i class="fas fa-chevron-down section-chevron" id="chevron-spacing" style="transform: rotate(-90deg)"></i>
            </button>
            {{-- Was "Size & spacing": typed CSS for padding, margin, width and
                 height. A fixed width or height set here held on phones and pushed
                 the page sideways or cut text off, and a mistyped value did
                 nothing at all. Size is the corner dots on the page now; what is
                 left are the two safe ones, as buttons and a slider.

                 "Front or back" (layers) is gone too: it lifted a template item
                 out of the page flow to stack it, so the content around it closed
                 up over the gap. --}}
            <div class="section-body hidden" id="section-spacing">
                <label class="field-label settings-label">Corners</label>
                <div class="grid grid-cols-4 gap-1.5 mb-4">
                    <button type="button" onclick="applyStyle('border-radius', '0px')" class="corner-btn" title="Square corners"><span style="border-radius:0"></span>Square</button>
                    <button type="button" onclick="applyStyle('border-radius', '8px')" class="corner-btn" title="Slightly rounded"><span style="border-radius:3px"></span>Soft</button>
                    <button type="button" onclick="applyStyle('border-radius', '18px')" class="corner-btn" title="Rounded"><span style="border-radius:6px"></span>Round</button>
                    <button type="button" onclick="applyStyle('border-radius', '9999px')" class="corner-btn" title="Pill or circle"><span style="border-radius:999px"></span>Pill</button>
                </div>

                <label class="field-label settings-label">Fading</label>
                <div class="flex items-center gap-2.5">
                    <input id="opacityInput" type="range" min="0.2" max="1" step="0.05" value="1" class="flex-1 accent-cyan-500"
                        oninput="onOpacitySlide(this.value)" onchange="applyStyle('opacity', this.value)">
                    <span id="opacityLabel" class="value-pill">Solid</span>
                </div>
            </div>
        </div>

        <!-- Bottom spacer -->
        <div class="h-6"></div>
    </div>

</aside>

<style>
    /* ══════════════════════════════════════════════════════════
       DESIGN PANEL

       Two accents carry the whole panel: amber for what belongs to the
       hotel (every page at once) and cyan for what belongs to the one
       element the student clicked. Everything else is neutral, which is
       what lets those two mean something.

       .style-input, .settings-label and .color-swatch keep their names on
       purpose: the builder shell restyles them for the second site theme
       by those exact selectors, so renaming them here would quietly drop
       the panel out of that theme.
       ══════════════════════════════════════════════════════════ */

    /* ── Scrollbar ── */
    .custom-scrollbar::-webkit-scrollbar { width: 0.25rem; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 0.625rem; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #52525b; }

    /* ── Header tools ── */
    .panel-tool-btn {
        width: 1.75rem; height: 1.75rem; border-radius: 0.5rem;
        display: inline-flex; align-items: center; justify-content: center;
        background: transparent; border: 1px solid transparent;
        color: #a1a1aa; font-size: 0.6875rem; cursor: pointer;
        transition: all 0.15s ease;
    }
    .panel-tool-btn:hover:not(:disabled) { background: #3f3f46; color: #fff; }
    .panel-tool-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .panel-tool-btn--bare { border-color: #3f3f46; background: #27272a; }
    .panel-tool-btn--danger { border-color: #3f3f46; background: #27272a; }
    .panel-tool-btn--danger:hover { background: rgba(136, 19, 55, 0.6); border-color: rgba(244, 63, 94, 0.5); color: #fda4af; }
    .panel-undo-label {
        font-size: 0.6875rem; font-weight: 600; color: #71717a;
        background: none; border: none; cursor: pointer; padding: 0 2px;
        transition: color 0.15s ease;
    }
    .panel-undo-label:hover { color: #d4d4d8; }
    .panel-text-btn {
        display: inline-flex; align-items: center; gap: 0.3125rem;
        height: 1.75rem; padding: 0 0.5625rem; border-radius: 0.5rem;
        background: #27272a; border: 1px solid #3f3f46;
        color: #d4d4d8; font-size: 0.6562rem; font-weight: 600; cursor: pointer;
        transition: all 0.15s ease; white-space: nowrap;
    }
    .panel-text-btn i { font-size: 0.5938rem; }
    .panel-text-btn:hover { background: #3f3f46; color: #fff; }
    .panel-text-btn--danger { color: #fda4af; }
    .panel-text-btn--danger:hover { background: rgba(136, 19, 55, 0.6); border-color: rgba(244, 63, 94, 0.5); color: #fecdd3; }

    /* ── Zone headings ──
       Not buttons and not sections: a label that says which of the two
       things below it act on, so the sections underneath do not have to
       repeat it one by one. */
    .zone-head {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.875rem 1.25rem 0.5rem;
        font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
    }
    .zone-dot { width: 0.375rem; height: 0.375rem; border-radius: 9999px; flex-shrink: 0; }
    .zone-head--hotel .zone-dot { background: #f59e0b; }
    .zone-head--hotel .zone-title { color: #fbbf24; }
    .zone-head--element .zone-dot { background: #22d3ee; }
    .zone-head--element .zone-title { color: #67e8f9; }
    .zone-note {
        margin-left: auto; font-size: 0.625rem; font-weight: 500;
        letter-spacing: 0.02em; text-transform: none; color: #52525b;
    }

    /* ── Sections ── */
    .design-section { border-bottom: 1px solid rgba(39, 39, 42, 0.8); }
    .section-toggle {
        width: 100%; display: flex; align-items: center; gap: 0.625rem;
        padding: 0.75rem 1.25rem; background: none; border: none;
        text-align: left; cursor: pointer; user-select: none;
        transition: background 0.15s ease;
    }
    .section-toggle:hover { background: rgba(39, 39, 42, 0.5); }
    .section-toggle:active { background: rgba(39, 39, 42, 0.8); }
    .section-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 0.5625rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(6, 182, 212, 0.12); color: #22d3ee; font-size: 0.6875rem;
    }
    .section-icon--hotel { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
    .section-label {
        flex: 1; min-width: 0; display: block;
        font-size: 0.7812rem; font-weight: 600; color: #e4e4e7; line-height: 1.3;
    }
    .section-sub {
        display: block; font-size: 0.6562rem; font-weight: 400;
        color: #71717a; margin-top: 2px; line-height: 1.3;
    }
    .section-chevron {
        font-size: 0.5625rem; color: #52525b; flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .section-body { padding: 0 1.25rem 1rem; }
    .section-hint {
        font-size: 0.6875rem; line-height: 1.55; color: #71717a; margin-bottom: 0.625rem;
    }

    /* Nothing is selected, so the controls that need one are muted. They
       still answer a click - the toast that says "click an element first"
       is better teaching than a dead control. */
    #hmsDesignPanel.no-selection .needs-selection .section-body { opacity: 0.45; }
    .needs-selection .section-body { transition: opacity 0.2s ease; }

    /* ── What's selected ──
       Sticky, because every control below it acts on whatever it names. */
    .target-card-wrap {
        position: sticky; top: 0; z-index: 20;
        padding: 0.25rem 1rem 0.75rem; background: #18181b;
        border-bottom: 1px solid rgba(39, 39, 42, 0.8);
    }
    .target-card {
        border-radius: 0.875rem; padding: 0.75rem;
        background: linear-gradient(180deg, rgba(8, 145, 178, 0.10), rgba(24, 24, 27, 0.4));
        border: 1px solid rgba(34, 211, 238, 0.22);
    }
    .target-row { display: flex; align-items: center; gap: 0.625rem; }
    .target-icon {
        width: 1.875rem; height: 1.875rem; border-radius: 0.625rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(6, 182, 212, 0.18); color: #22d3ee; font-size: 0.6875rem;
    }
    .target-eyebrow {
        font-size: 0.5938rem; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: #52525b;
    }
    .target-name {
        display: block; font-size: 0.7812rem; font-weight: 600; color: #e4e4e7;
        line-height: 1.35; margin-top: 1px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    #hmsDesignPanel.no-selection .target-name { color: #a1a1aa; font-weight: 500; }
    .target-hint { font-size: 0.6562rem; line-height: 1.5; color: #71717a; margin-top: 0.5rem; }
    #hmsDesignPanel:not(.no-selection) .target-hint { display: none; }

    .parent-btn {
        width: 100%; margin-top: 0.5rem; padding: 0.375rem 0.5rem; border-radius: 0.5625rem;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
        background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.25);
        color: #67e8f9; font-size: 0.6875rem; font-weight: 600; cursor: pointer;
        transition: background 0.15s ease;
    }
    .parent-btn:hover { background: rgba(6, 182, 212, 0.18); }
    /* This panel's own display rules sit later in the document than Tailwind's
       .hidden, so anything the script hides has to say so at a specificity the
       layout rule cannot outrank. */
    .parent-btn.hidden, .move-box.hidden, .section-body.hidden { display: none; }

    .move-box {
        margin-top: 0.5rem; padding: 0.625rem; border-radius: 0.625rem;
        background: rgba(24, 24, 27, 0.6); border: 1px solid rgba(63, 63, 70, 0.7);
    }
    .move-icon { font-size: 0.625rem; color: #22d3ee; margin-top: 2px; }
    .move-title { font-size: 0.6875rem; font-weight: 600; color: #d4d4d8; }
    .move-help { font-size: 0.6562rem; line-height: 1.5; color: #71717a; margin-top: 2px; }

    /* ── Labels ── */
    .field-label {
        display: block; font-size: 0.6562rem; font-weight: 600; color: #a1a1aa;
        letter-spacing: 0.03em; margin-bottom: 0.3125rem;
    }
    .field-group-label {
        font-size: 0.625rem; font-weight: 700; color: #71717a;
        letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.375rem;
    }
    /* Kept for the builder shell's own theme rules, which target it by name. */
    .settings-label {
        display: block; font-size: 0.6562rem; font-weight: 600; color: #a1a1aa;
        letter-spacing: 0.03em; margin-bottom: 0.3125rem; text-transform: none;
    }
    .value-pill {
        min-width: 3.25rem; text-align: center; padding: 0.1875rem 0.5rem; border-radius: 0.4375rem;
        background: #27272a; border: 1px solid #3f3f46;
        font-size: 0.6562rem; font-weight: 600; color: #d4d4d8;
    }

    /* ── Inputs ── */
    .style-input {
        width: 100%; padding: 0.5rem 0.625rem; border-radius: 0.5625rem;
        background: #18181b; border: 1px solid #3f3f46;
        color: #e4e4e7; font-size: 0.7188rem; font-family: 'Inter', sans-serif;
        transition: all 0.15s ease; outline: none;
    }
    .style-input::placeholder { color: #52525b; }
    .style-input:hover { border-color: #52525b; }
    .style-input:focus {
        border-color: #06b6d4; background: #1c1c1f;
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.12);
    }
    .style-input option { background: #18181b; color: #d4d4d8; padding: 0.375rem; }
    .style-input--hotel:focus {
        border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.12);
    }
    .select-caret {
        position: absolute; right: 0.6875rem; top: 50%; transform: translateY(-50%);
        font-size: 0.5625rem; color: #71717a; pointer-events: none;
    }

    /* ── Buttons ── */
    .ghost-btn {
        width: 100%; padding: 0.4375rem 0.5rem; border-radius: 0.5625rem;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
        background: #27272a; border: 1px solid #3f3f46;
        color: #d4d4d8; font-size: 0.6875rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s ease;
    }
    .ghost-btn:hover { background: #3f3f46; color: #fff; border-color: #52525b; }
    .ghost-btn i { font-size: 0.625rem; }
    .ghost-btn--hotel:hover { border-color: rgba(245, 158, 11, 0.5); }
    .ghost-btn--danger { color: #fda4af; }
    .ghost-btn--danger:hover {
        background: rgba(136, 19, 55, 0.45); border-color: rgba(244, 63, 94, 0.5); color: #fecdd3;
    }

    .feature-btn {
        width: 100%; height: 2.375rem; border-radius: 0.6875rem;
        display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
        background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3);
        color: #a5f3fc; font-size: 0.75rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s ease;
    }
    .feature-btn:hover { background: rgba(6, 182, 212, 0.18); color: #fff; }
    .feature-btn i { font-size: 0.6875rem; }
    .feature-btn--hotel {
        background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3); color: #fcd34d;
    }
    .feature-btn--hotel:hover { background: rgba(245, 158, 11, 0.18); color: #fff; }

    .add-el-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
        padding: 0.5625rem 0.375rem; border-radius: 0.5625rem;
        background: #18181b; border: 1px solid #3f3f46;
        color: #d4d4d8; font-size: 0.6875rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s ease;
    }
    .add-el-btn:hover { border-color: rgba(6, 182, 212, 0.5); color: #fff; background: #27272a; }
    .add-el-btn i { font-size: 0.625rem; color: #22d3ee; }

    /* ── Corner buttons: a little box drawn with the corner it gives ── */
    .corner-btn {
        display: flex; flex-direction: column; align-items: center; gap: 0.3125rem;
        padding: 0.5rem 0.25rem; border-radius: 0.5625rem;
        background: #18181b; border: 1px solid #3f3f46;
        color: #a1a1aa; font-size: 0.6094rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s ease;
    }
    .corner-btn span { width: 1.25rem; height: 0.875rem; border: 2px solid #22d3ee; }
    .corner-btn:hover { background: #27272a; border-color: rgba(6, 182, 212, 0.45); color: #fff; }

    /* ── Toggle buttons (align, italic, underline, strikethrough) ── */
    .style-toggle-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.125rem; height: 2.125rem; border-radius: 0.5625rem;
        background: #18181b; border: 1px solid #3f3f46;
        color: #a1a1aa; font-size: 0.6875rem;
        cursor: pointer; transition: all 0.15s ease;
    }
    .style-toggle-btn:hover { background: #27272a; color: #fff; border-color: #52525b; }
    .style-toggle-btn.active {
        background: rgba(6, 182, 212, 0.14);
        border-color: rgba(6, 182, 212, 0.5);
        color: #67e8f9;
    }

    /* ── Colour swatches ── */
    .swatch-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; }
    .color-preset-swatch {
        width: 1.5rem; height: 1.5rem; border-radius: 0.4375rem; cursor: pointer;
        /* A faint edge so the black swatch still shows on the dark panel. */
        border: 1px solid rgba(255, 255, 255, 0.2); flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .color-preset-swatch:hover { transform: scale(1.15); border-color: rgba(255, 255, 255, 0.3); }
    .swatch-transparent {
        background-image: repeating-conic-gradient(#3f3f46 0% 25%, #27272a 0% 50%);
        background-size: 8px 8px;
    }
    /* Every colour, for the one the swatches do not have: the browser's own picker
       behind a small labelled box, so there is no code to type. */
    .color-more {
        display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer;
        font-size: 0.6094rem; font-weight: 600; color: #a1a1aa;
    }
    .color-more .color-swatch { width: 1.5rem; height: 1.5rem; border-radius: 0.4375rem; }
    .color-more:hover { color: #fff; }

    /* Kept for the builder shell's own theme rules, which target it by name. */
    .color-swatch {
        width: 2.125rem; height: 2.125rem; border-radius: 0.5625rem; cursor: pointer;
        border: 1px solid #3f3f46; background: #18181b;
        padding: 0; flex-shrink: 0; transition: all 0.15s ease;
    }
    .color-swatch:hover { border-color: #52525b; transform: scale(1.06); }
    .color-swatch::-webkit-color-swatch-wrapper { padding: 0.1875rem; }
    .color-swatch::-webkit-color-swatch { border-radius: 0.375rem; border: none; }
    .swatch-glyph {
        position: absolute; inset: 0; margin: auto;
        width: 0.625rem; height: 0.625rem;
        font-size: 0.5rem; color: rgba(255, 255, 255, 0.75);
        pointer-events: none; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6);
    }

    /* ── Note box ── */
    .note-box {
        display: flex; gap: 0.5rem; padding: 0.625rem; border-radius: 0.625rem;
        background: rgba(24, 24, 27, 0.6); border: 1px solid rgba(63, 63, 70, 0.7);
    }
    .note-box p { font-size: 0.6562rem; line-height: 1.6; color: #a1a1aa; }
    .note-box-icon { font-size: 0.625rem; color: #fbbf24; margin-top: 2px; flex-shrink: 0; }
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

    /* The palette dialog lives in the template, because that is where the colours
       are applied and previewed. The panel only asks for it. */
    function openSiteColours() {
        if (window.currentEditorMode === 'preview') {
            if (typeof toast === 'function') toast('Switch to Design to change the colours');
            return;
        }
        postToTemplate({ type: 'open-site-colors' });
    }

    function toggleSection(id) {
        const body = document.getElementById('section-' + id);
        const chevron = document.getElementById('chevron-' + id);
        if (!body) return;
        const hidden = body.classList.toggle('hidden');
        if (chevron) chevron.style.transform = hidden ? 'rotate(-90deg)' : '';
    }

    /* Mutes the sections that only do something once an element is picked.
       Nothing is disabled - a click still raises the toast that explains what
       to do, which teaches better than a control that ignores you. */
    function setPanelSelectionState(hasSelection) {
        const panel = document.getElementById('hmsDesignPanel');
        if (panel) panel.classList.toggle('no-selection', !hasSelection);
    }

    function requireSelection() {
        if (!window.selectedElementId) {
            if (typeof toast === 'function') toast('Click an element in the template first');
            return false;
        }
        return true;
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
        const picker = document.getElementById(kind === 'text' ? 'textColor' : 'bgColor');
        if (picker && value !== 'transparent') picker.value = value;
        applyStyle(kind === 'text' ? 'color' : 'background-color', value);
    }

    // The slider's own words, so "0.45" never has to mean anything to anyone.
    function onOpacitySlide(value) {
        const label = document.getElementById('opacityLabel');
        const n = Number(value);
        if (label) label.textContent = n >= 1 ? 'Solid' : Math.round(n * 100) + '%';
    }

    function applyTextContent(value) {
        if (!requireSelection()) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, text: value });
    }

    function applyImageUrl(url) {
        if (!requireSelection() || !url) return;
        postToTemplate({ type: 'apply-edit', id: window.selectedElementId, src: url });
    }

    async function uploadSelectedImage(input) {
        if (!requireSelection()) { input.value = ''; return; }
        // Checked before the upload, so a heading picked by mistake does not
        // store a file nobody uses and then report "Image updated".
        if (window.selectedElementIsText) {
            input.value = '';
            if (typeof toast === 'function') toast('Pick the picture you want to replace first, then upload.');
            return;
        }
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

    async function resetAllDesign() {
        /* Asked in the app's own dialog rather than the browser's, which prints the
           deployment's hostname over the site being designed. askConfirm lives in the
           shell this panel is loaded into. */
        const ok = typeof askConfirm === 'function'
            ? await askConfirm(
                'Reset all design customizations?',
                'This restores the page to its original default appearance. Anything styled by hand is lost.',
                'Reset everything'
              )
            : confirm('Reset all design customizations? This will restore the page to its original default appearance.');
        if (!ok) return;
        postToTemplate({ type: 'reset-all' });
        window.selectedElementId = null;
        window.templateCustomizations = {};
        const label = document.getElementById('selectedElement');
        if (label) label.textContent = 'Select an element to style';
        const parentBtn = document.getElementById('selectParentBtn');
        if (parentBtn) parentBtn.classList.add('hidden');
        const movement = document.getElementById('movementControls');
        if (movement) movement.classList.add('hidden');
        setPanelSelectionState(false);

        /* The editor can only put back what it recorded during this visit, so a
           hidden block or a moved element's container could stay changed until the
           student refreshed. Save the reset first, then redraw the frame from the
           defaults. Marked dirty here because the frame's own change notice may not
           have arrived yet, and a flush with nothing dirty saves nothing. */
        if (window.hmsBuilder) {
            window.hmsBuilder.markDirty();
            try {
                await window.hmsBuilder.flush();
            } catch (e) {
                return; // the save error is already on the status line
            }
        }
        const frame = document.getElementById('templateFrame');
        if (frame && frame.contentWindow) frame.contentWindow.location.reload();
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
        const names = { text: 'Words', button: 'A button', image: 'A picture', card: 'A card' };
        if (typeof toast === 'function') toast((names[type] || 'It') + ' added. Drag the “Move” tab to place it.');
    }

    /* Asked first: a deleted section is gone from the live site too, and Undo
       only reaches back through this visit. */
    async function deleteSelectedElement() {
        if (!requireSelection()) return;
        const ok = typeof askConfirm === 'function'
            ? await askConfirm(
                'Remove this from the page?',
                'Guests will no longer see it. Undo brings it back while this page is open.',
                'Remove'
              )
            : confirm('Remove this from the page?');
        if (!ok || !window.selectedElementId) return;
        postToTemplate({ type: 'delete-element' });
        window.selectedElementId = null;
        const label = document.getElementById('selectedElement');
        if (label) label.textContent = 'Select an element to style';
        setPanelSelectionState(false);
    }

    function duplicateSelectedElement() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'duplicate-element' });
    }

    function selectParentElement() {
        if (!requireSelection()) return;
        postToTemplate({ type: 'select-parent' });
    }

    function updateMovementControls() {
        const controls = document.getElementById('movementControls');
        if (!controls) return;
        controls.classList.remove('hidden');
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
            window.selectedElementIsText = !data.isImage && /^(h[1-6]|p|span|em|strong|small|label|a|li|i)$/.test(data.tag || '');
            const label = document.getElementById('selectedElement');
            // The plain name only: "· <h2>" after it was code to a non-designer.
            if (label) label.textContent = data.label || 'Selected item';
            const parentBtn = document.getElementById('selectParentBtn');
            const parentLabel = document.getElementById('selectParentLabel');
            if (parentBtn) parentBtn.classList.toggle('hidden', !data.canSelectParent);
            if (parentLabel) parentLabel.textContent = data.parentLabel
                ? 'Select the box around it: ' + data.parentLabel
                : 'Select the box around it';
            setPanelSelectionState(true);
            updateMovementControls();
            const textArea = document.getElementById('elementText');
            if (textArea) textArea.value = data.text || '';
            const urlInput = document.getElementById('imageUrlInput');
            if (urlInput) urlInput.value = data.src || '';
            if (data.styles) {
                if (data.styles.color && /^#[0-9a-f]{6}$/i.test(data.styles.color)) {
                    const tc = document.getElementById('textColor');
                    if (tc) tc.value = data.styles.color;
                }
                if (data.styles['background-color'] && /^#[0-9a-f]{6}$/i.test(data.styles['background-color'])) {
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
                const opacity = document.getElementById('opacityInput');
                const op = data.styles.opacity ? Number(data.styles.opacity) : 1;
                if (opacity) opacity.value = isNaN(op) ? 1 : op;
                onOpacitySlide(isNaN(op) ? 1 : op);
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
            setPanelSelectionState(false);
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
                    // A failed save has already said why on the status line;
                    // saying "Auto-saved" over it would be the silent failure.
                    if (!(await window.hmsBuilder.autosave())) return;
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
        select.className = 'style-input style-input--hotel w-24 shrink-0';
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
        input.className = 'style-input style-input--hotel flex-1 min-w-0';
        input.addEventListener('input', () => queueIdentityPush());

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'style-toggle-btn shrink-0';
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
