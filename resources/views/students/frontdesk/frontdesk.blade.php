<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS-Learn | Front Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #050507; color: #fff; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #0a0a0c; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }

        /* ── TOPBAR ── */
        .topbar {
            background: rgba(10, 10, 12, 0.8);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        
        .hms-logo-text {
            font-weight: 800;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #fff 0%, #a1a1aa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 12px rgba(37, 99, 235, 0.4);
        }
        
        .module-badge {
            background: rgba(6, 182, 212, 0.15); /* Cyan tint for Front Desk */
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #22d3ee;
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ── SIDEBARS ── */
        .sidebar-base {
            background: #09090b;
            border-color: rgba(255,255,255,0.05);
        }
        
        .tool-card {
            margin: 2px 12px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: transparent;
            display: flex; align-items: center; gap: 12px;
            cursor: grab; transition: all 0.2s;
        }
        .tool-card:hover { 
            background: rgba(39, 39, 42, 0.5); 
            border-color: rgba(255,255,255,0.1); 
        }
        .tool-card:active { cursor: grabbing; }
        
        .tool-icon {
            width: 40px; height: 40px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 16px;
            color: #fff;
            transition: transform 0.2s;
        }
        .tool-card:hover .tool-icon { transform: scale(1.05); }

        /* ── CANVAS AREA ── */
        .canvas-bg {
            background: #18181b;
            background-image: radial-gradient(rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        
        .canvas-frame {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
            border: 1px solid #27272a;
            display: flex;
            flex-direction: column;
            width: 100%; /* Make frame take full width of the main area */
            height: 100%; /* Make frame take full height */
        }
        
        .browser-bar { 
            background: #f4f4f5; 
            border-bottom: 1px solid #e4e4e7; 
            padding: 0 12px;
            height: 40px;
            min-height: 40px;
        }
        .browser-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .browser-btn {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            color: #71717a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        .url-pill { 
            background: #ffffff; 
            border: 1px solid #e4e4e7; 
            color: #71717a; 
            font-size: 11px; 
            border-radius: 4px; 
            padding: 4px 12px;
        }

        .dropzone {
            position: relative; background: #ffffff;
            transition: all 0.2s;
            flex: 1; /* Take remaining height in the frame */
            overflow-y: auto;
        }
        .dropzone.drag-active {
            background: rgba(239, 246, 255, 1);
            outline: 2px dashed #06b6d4; /* Cyan dashed for Front Desk */
            outline-offset: -8px;
        }
        
        .empty-state {
            position: absolute; inset: 0;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            pointer-events: none;
        }
        
        .empty-icon-ring {
            width: 80px; height: 80px; border-radius: 50%;
            border: 2px dashed #d4d4d8;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px; color: #a1a1aa; font-size: 28px;
            animation: pulse-ring 3s ease-in-out infinite;
        }
        @keyframes pulse-ring { 0%,100%{border-color:#d4d4d8; color:#a1a1aa;} 50%{border-color:#06b6d4; color:#06b6d4;} }

        /* ── DROPPED BLOCKS ── */
        .dropped-block {
            position: relative; animation: blockIn 0.3s ease-out;
            border: 2px solid transparent; transition: border-color 0.2s, box-shadow 0.2s;
        }
        @keyframes blockIn { from{opacity:0;transform:translateY(-10px);} to{opacity:1;transform:translateY(0);} }
        
        .dropped-block:hover { 
            border-color: #06b6d4; 
            border-radius: 12px; 
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
        }
        
        .block-toolbar {
            position: absolute; top: -16px; right: 10px;
            display: none; gap: 4px; z-index: 20;
        }
        .dropped-block:hover .block-toolbar { display: flex; }
        
        .tb-btn {
            height: 28px; padding: 0 10px; border-radius: 6px;
            font-size: 10px; font-weight: 600; border: none; cursor: pointer;
            display: flex; align-items: center; gap: 4px;
            font-family: 'Inter', sans-serif; transition: all 0.15s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tb-move { background: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc; }
        .tb-move:hover { background: #cffafe; }
        .tb-del { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .tb-del:hover { background: #fee2e2; }

        /* ── UI COMPONENTS ── */
        .block-heading {
            font-size: 14px; font-weight: 600; color: #18181b;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 16px;
        }

        .hms-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .hms-table th { background: #f4f4f5; color: #52525b; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 10px 14px; text-align: left; border-bottom: 1px solid #e4e4e7; }
        .hms-table td { padding: 12px 14px; border-bottom: 1px solid #f4f4f5; color: #27272a; }
        .hms-table tr:last-child td { border-bottom: none; }
        .hms-table tr:hover td { background: #fafafa; }
        
        .status-pill { padding: 4px 10px; border-radius: 9999px; font-size: 10px; font-weight: 600; display: inline-block; }

        .stat-card {
            background: #fff; border-radius: 12px; padding: 16px;
            border-top: 3px solid;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .hk-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 14px; border-radius: 8px; background: #fafafa;
            border: 1px solid #f4f4f5; font-size: 12px; transition: all 0.15s;
        }
        .hk-row:hover { border-color: #d4d4d8; background: #fff; }

        /* ── SETTINGS PANEL ── */
        .settings-card {
            background: rgba(24, 24, 27, 0.5);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .settings-label {
            font-size: 10px; font-weight: 600; color: #71717a;
            letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 8px; display: block;
        }
        .settings-input, .settings-select {
            width: 100%; padding: 8px 10px; border-radius: 6px;
            background: #09090b; border: 1px solid #27272a;
            color: #d4d4d8; font-size: 12px; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s; outline: none;
        }
        .settings-input:focus, .settings-select:focus { border-color: #06b6d4; }

        .style-input {
            width: 100%; padding: 8px 10px; border-radius: 6px;
            background: #09090b; border: 1px solid #27272a;
            color: #d4d4d8; font-size: 12px; font-family: 'Inter', sans-serif;
            transition: border-color 0.2s; outline: none;
        }
        .style-input:focus { border-color: #06b6d4; }
        .style-input option { background: #18181b; color: #d4d4d8; }
        
        .color-swatch {
            width: 24px; height: 24px; border-radius: 6px; cursor: pointer;
            border: 2px solid transparent; transition: all 0.15s;
        }
        .color-swatch:hover { transform: scale(1.15); }
        .color-swatch.active { border-color: #fff; box-shadow: 0 0 0 2px #06b6d4; }

        /* ── PROFILE DROPDOWN ── */
        .profile-trigger {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 12px 6px 8px; border-radius: 8px;
            border: 1px solid #27272a; background: #0a0a0c;
            cursor: pointer; transition: all 0.2s;
        }
        .profile-trigger:hover { border-color: #3f3f46; background: #18181b; }
        
        .avatar {
            width: 32px; height: 32px; border-radius: 6px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
        }
        
        .profile-dropdown {
            position: absolute; top: calc(100% + 12px); right: 0; width: 220px;
            background: #18181b; border: 1px solid #27272a; border-radius: 12px;
            padding: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 999;
            display: none; animation: dropIn 0.2s ease-out;
        }
        @keyframes dropIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .profile-dropdown.show { display: block; }
        
        .dd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 6px;
            color: #a1a1aa; font-size: 13px; cursor: pointer;
            transition: all 0.15s;
        }
        .dd-item:hover { background: #27272a; color: #fafafa; }
        .dd-item i { width: 16px; font-size: 14px; color: #71717a; }
        .dd-divider { height: 1px; background: #27272a; margin: 6px 0; }
        .dd-logout { color: #ef4444 !important; }
        .dd-logout i { color: #ef4444 !important; }

        /* ── ACTION BUTTONS ── */
        .hdr-btn {
            height: 36px; padding: 0 16px; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', sans-serif; border: none;
            display: flex; align-items: center; gap: 6px; transition: all 0.2s;
        }
        .btn-secondary { background: #18181b; color: #d4d4d8; border: 1px solid #27272a; }
        .btn-secondary:hover { background: #27272a; color: #fff; }
        .btn-primary { background: #0891b2; color: #fff; } /* Cyan primary for Front Desk */
        .btn-primary:hover { background: #0e7490; box-shadow: 0 0 20px rgba(6,182,212,0.3); }

        /* Toast */
        #toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px);
            background: #18181b; border: 1px solid #27272a; color: #fafafa; padding: 12px 24px; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
            opacity: 0; z-index: 9999;
        }
        #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .status-bar { background: #09090b; border-top: 1px solid #18181b; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

    <!-- ═══════ TOP NAVIGATION BAR ═══════ -->
    <header class="topbar h-16 flex items-center justify-between px-6 shrink-0 z-50">
        
        <div class="flex items-center gap-3">
            <div class="logo-icon">
                <i class="fas fa-bell-concierge text-white text-sm"></i>
            </div>
            <div class="flex flex-col leading-none">
                <span class="hms-logo-text text-xl">HMS<span style="-webkit-text-fill-color:#71717a;background:none;color:#71717a;">-Learn</span></span>
            </div>
            <div class="w-px h-5 bg-zinc-800 mx-2"></div>
            <span class="module-badge">Front Desk</span>
        </div>

        <div class="flex items-center gap-1 bg-zinc-900/60 rounded-lg p-1 border border-zinc-800">
            <button onclick="setMode(this,'design')" class="mode-tab px-4 py-2 rounded-md text-xs font-semibold text-zinc-500 transition-all">Design</button>
            <button onclick="setMode(this,'build')" class="mode-tab active-tab px-4 py-2 rounded-md text-xs text-white transition-all bg-zinc-800 border border-zinc-700">Build</button>
            <button onclick="setMode(this,'preview')" class="mode-tab px-4 py-2 rounded-md text-xs font-semibold text-zinc-500 transition-all">Preview</button>
        </div>

        <div class="flex items-center gap-3">
            <button class="hdr-btn btn-secondary" onclick="toast('Draft saved ✓')"><i class="fas fa-save"></i> Save Draft</button>
            <button class="hdr-btn btn-primary" onclick="toast('🎉 Page published successfully!')"><i class="fas fa-paper-plane"></i> Publish</button>

            <div class="relative" id="profileWrapper">
                <?php
                    $authUser = auth()->user();
                    $profileName = $authUser?->name ?? 'Student';
                    $nameParts = preg_split('/\s+/', trim($profileName));
                    $initials = strtoupper(($nameParts[0][0] ?? 'S') . (count($nameParts) > 1 ? substr(end($nameParts), 0, 1) : ''));
                ?>
                <div class="profile-trigger" onclick="toggleDropdown()">
                    <div class="avatar"><?= $initials ?></div>
                    <div class="leading-none">
                        <p class="text-xs font-semibold text-white"><?= e($profileName) ?></p>
                        <p class="text-[10px] text-zinc-500 mt-0.5">Student</p>
                    </div>
                    <i class="fas fa-chevron-down text-zinc-600 text-[9px] ml-1" id="chevron"></i>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dd-item" onclick="toast('Opening profile...')"><i class="fas fa-user-circle"></i> My Profile</div>
                    <div class="dd-item" onclick="toast('Opening settings...')"><i class="fas fa-cog"></i> Settings</div>
                    <div class="dd-divider"></div>
                    <form method="POST" action="<?php echo route('logout'); ?>">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="dd-item dd-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- ═══════ MAIN 3-COLUMN LAYOUT ═══════ -->
    <div class="flex flex-1 overflow-hidden">
        <div class="w-72 shrink-0 sidebar-base border-r overflow-y-auto">
            @include('students.frontdesk.left-sidebar.index')
        </div>

        <div class="flex-1 flex flex-col min-w-0 canvas-bg">
            @include('students.frontdesk.center-canvas.index')
        </div>

        <div class="w-80 shrink-0 sidebar-base border-l overflow-y-auto">
            @include('students.frontdesk.right-sidebar.index')
        </div>

    </div>

    <!-- ═══════ STATUS BAR ═══════ -->
    <div class="status-bar h-8 flex items-center px-6 gap-4 shrink-0 text-[10px] text-zinc-600">
        <div class="flex items-center gap-2 text-green-500"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Auto-saved</div>
        <span class="text-zinc-800">|</span>
        <span id="blockCount">0 blocks on canvas</span>
    </div>

    <div id="toast"></div>

    <script>
        const dropzone = document.getElementById('dropzone');
        const emptyState = document.getElementById('emptyState');
        let blockNum = 0;

        document.querySelectorAll('.tool-card').forEach(card => {
            card.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', card.dataset.type);
                card.style.opacity = '0.5';
            });
            card.addEventListener('dragend', e => { card.style.opacity = '1'; });
        });

        function onDragOver(e) { e.preventDefault(); dropzone.classList.add('drag-active'); }
        function onDragLeave(e) { dropzone.classList.remove('drag-active'); }
        function onDrop(e) {
            e.preventDefault();
            dropzone.classList.remove('drag-active');
            const type = e.dataTransfer.getData('text/plain');
            if (!type) return;
            emptyState.style.display = 'none';
            insertBlock(type);
        }

        function insertBlock(type) {
            blockNum++;
            const id = 'block-' + blockNum;
            const wrapper = document.createElement('div');
            wrapper.className = 'dropped-block mb-4';
            wrapper.id = id;
            wrapper.innerHTML = `
                <div class="block-toolbar">
                    <button class="tb-btn tb-move" onclick="moveUp('${id}')"><i class="fas fa-arrow-up"></i></button>
                    <button class="tb-btn tb-move" onclick="moveDown('${id}')"><i class="fas fa-arrow-down"></i></button>
                    <button class="tb-btn tb-del" onclick="removeBlock('${id}')"><i class="fas fa-trash"></i></button>
                </div>
                ${getBlockHTML(type)}
            `;
            dropzone.appendChild(wrapper);
            updateCount();
            toast(getLabel(type) + ' added');
        }

        function getLabel(t) {
            const labels = { 
                'checkin-form': 'Check-In Form',
                'arrivals-list': 'Arrivals List',
                'departures-list': 'Departures List',
                'guest-messages': 'Guest Messages',
                'billing-counter': 'Billing Counter',
                'key-card': 'Key Card Status'
            };
            return labels[t] || 'Block';
        }

        function getBlockHTML(type) {
            switch(type) {
                case 'checkin-form': return `
                <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-right-to-bracket text-green-500 mr-2"></i>Guest Check-In</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" placeholder="Full Name" class="col-span-2 border border-zinc-200 rounded-lg p-2.5 text-sm">
                        <input type="text" placeholder="ID / Passport" class="border border-zinc-200 rounded-lg p-2.5 text-sm">
                        <input type="text" placeholder="Room Number" class="border border-zinc-200 rounded-lg p-2.5 text-sm">
                        <button class="col-span-2 bg-green-600 text-white p-2.5 rounded-lg text-sm font-semibold hover:bg-green-700 transition">Check In Guest</button>
                    </div>
                </div>`;

                case 'arrivals-list': return `
                <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-plane-arrival text-blue-500 mr-2"></i>Today's Arrivals</h3>
                    <table class="hms-table">
                        <thead><tr><th>Guest</th><th>Room</th><th>Time</th><th>Status</th></tr></thead>
                        <tbody>
                            <tr><td class="font-bold">Juan Dela Cruz</td><td>305</td><td>2:00 PM</td><td><span class="status-pill bg-yellow-100 text-yellow-700">Due In</span></td></tr>
                            <tr><td class="font-bold">Maria Santos</td><td>412</td><td>4:30 PM</td><td><span class="status-pill bg-green-100 text-green-700">Arrived</span></td></tr>
                        </tbody>
                    </table>
                </div>`;

                case 'departures-list': return `
                <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-plane-departure text-orange-500 mr-2"></i>Expected Departures</h3>
                     <div class="space-y-2">
                        <div class="hk-row">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-bold text-xs">J</div>
                                <div><p class="font-bold text-zinc-800">John Doe</p><p class="text-[10px] text-zinc-400">Room 101</p></div>
                            </div>
                            <div class="text-right"><span class="text-xs font-bold text-orange-600">₱4,500 Balance</span></div>
                        </div>
                        <div class="hk-row">
                             <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs">A</div>
                                <div><p class="font-bold text-zinc-800">Ana Reyes</p><p class="text-[10px] text-zinc-400">Room 204</p></div>
                            </div>
                            <div class="text-right"><span class="text-xs font-bold text-green-600">Paid</span></div>
                        </div>
                    </div>
                </div>`;

                case 'guest-messages': return `
                <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-envelope-open-text text-purple-500 mr-2"></i>Guest Messages</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg text-xs text-zinc-700">
                            <p class="font-bold text-zinc-900">Room 201</p>
                            <p>Need extra towels please.</p>
                        </div>
                        <div class="p-3 bg-purple-50 border-l-4 border-purple-500 rounded-r-lg text-xs text-zinc-700">
                            <p class="font-bold text-zinc-900">Room 105</p>
                            <p>Is breakfast included?</p>
                        </div>
                    </div>
                </div>`;

                case 'billing-counter': return `
                 <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-cash-register text-red-500 mr-2"></i>Quick Billing</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm"><span class="text-zinc-500">Room 101</span><span class="font-bold">₱12,450.00</span></div>
                        <div class="flex justify-between items-center text-sm"><span class="text-zinc-500">Room 305</span><span class="font-bold">₱8,200.00</span></div>
                        <div class="border-t border-zinc-100 pt-2 flex justify-between items-center text-sm text-red-600 font-bold">
                            <span>Total Pending</span><span>₱20,650.00</span>
                        </div>
                    </div>
                </div>`;

                case 'key-card': return `
                 <div class="p-5 bg-white rounded-xl border border-zinc-100">
                    <h3 class="block-heading"><i class="fas fa-key text-cyan-500 mr-2"></i>Key Card Status</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 bg-green-50 rounded-lg text-center border border-green-100">
                            <p class="text-2xl font-bold text-green-700">24</p>
                            <p class="text-[10px] text-green-600 uppercase font-bold">Active</p>
                        </div>
                        <div class="p-3 bg-zinc-50 rounded-lg text-center border border-zinc-100">
                            <p class="text-2xl font-bold text-zinc-700">8</p>
                            <p class="text-[10px] text-zinc-500 uppercase font-bold">Expired</p>
                        </div>
                    </div>
                </div>`;

                default: return '<div class="p-4">Block</div>';
            }
        }

        function removeBlock(id) { document.getElementById(id)?.remove(); updateCount(); if(dropzone.querySelectorAll('.dropped-block').length === 0) emptyState.style.display = 'flex'; toast('Block removed'); }
        function moveUp(id) { const el = document.getElementById(id); const prev = el?.previousElementSibling; if(prev && prev !== emptyState) { el.parentNode.insertBefore(el, prev); toast('Moved up'); } }
        function moveDown(id) { const el = document.getElementById(id); const next = el?.nextElementSibling; if(next) { el.parentNode.insertBefore(next, el); toast('Moved down'); } }
        function updateCount() { const n = dropzone.querySelectorAll('.dropped-block').length; document.getElementById('blockCount').textContent = n + ' block' + (n===1?'':'s') + ' on canvas'; }

        function toggleDropdown() { const dd = document.getElementById('profileDropdown'); const ch = document.getElementById('chevron'); dd.classList.toggle('show'); ch.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : ''; }
        document.addEventListener('click', function(e) { if(!document.getElementById('profileWrapper').contains(e.target)) { document.getElementById('profileDropdown').classList.remove('show'); document.getElementById('chevron').style.transform = ''; } });
        function handleLogout() { document.getElementById('profileDropdown').classList.remove('show'); toast('Logging out…'); }
        function setMode(btn, mode) { document.querySelectorAll('.mode-tab').forEach(t => { t.style.background = 'transparent'; t.style.border = 'none'; t.style.color = '#71717a'; t.classList.remove('active-tab'); }); btn.style.background = '#27272a'; btn.style.border = '1px solid #3f3f46'; btn.style.color = '#fff'; btn.classList.add('active-tab'); }

        let toastTimer;
        function toast(msg) { const t = document.getElementById('toast'); t.textContent = msg; t.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(()=>t.classList.remove('show'), 2000); }
    </script>
</body>
</html>