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
            background: rgba(6, 182, 212, 0.15);
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
            width: 100%;
            height: 100%;
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
        .btn-primary { background: #0891b2; color: #fff; }
        .btn-primary:hover { background: #0e7490; box-shadow: 0 0 20px rgba(6,182,212,0.3); }

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

        <div class="flex items-center gap-3">
            <button class="hdr-btn btn-secondary" onclick="toast('Draft saved')"><i class="fas fa-save"></i> Save Draft</button>
            <button class="hdr-btn btn-primary" onclick="toast('Page published successfully!')"><i class="fas fa-paper-plane"></i> Publish</button>

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
    </div>

    <div id="toast"></div>

    <script>
        function toggleDropdown() {
            const dd = document.getElementById('profileDropdown');
            const ch = document.getElementById('chevron');
            dd.classList.toggle('show');
            ch.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : '';
        }
        document.addEventListener('click', function(e) {
            if (!document.getElementById('profileWrapper').contains(e.target)) {
                document.getElementById('profileDropdown').classList.remove('show');
                document.getElementById('chevron').style.transform = '';
            }
        });

        let toastTimer;
        function toast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.style.position = 'fixed';
            t.style.bottom = '24px';
            t.style.left = '50%';
            t.style.transform = 'translateX(-50%) translateY(80px)';
            t.style.background = '#18181b';
            t.style.border = '1px solid #27272a';
            t.style.color = '#fafafa';
            t.style.padding = '12px 24px';
            t.style.borderRadius = '8px';
            t.style.fontSize = '13px';
            t.style.fontFamily = "'Inter', sans-serif";
            t.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
            t.style.transition = 'transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s';
            t.style.opacity = '1';
            t.style.zIndex = '9999';
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(80px)'; }, 2000);
        }
    </script>
</body>
</html>
