<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $builderRole = $builderRole ?? 'front_desk';
        $roleThemes = [
            'front_desk' => [
                'label' => 'Front Desk',
                'icon' => 'fa-bell-concierge',
                'badge_bg' => 'rgba(6, 182, 212, 0.15)',
                'badge_border' => 'rgba(6, 182, 212, 0.3)',
                'badge_color' => '#22d3ee',
                'logo_gradient' => 'linear-gradient(135deg, #2563eb, #4f46e5)',
                'logo_shadow' => '0 0 12px rgba(37, 99, 235, 0.4)',
            ],
            'room_management' => [
                'label' => 'Room Management',
                'icon' => 'fa-bed',
                'badge_bg' => 'rgba(244, 63, 94, 0.15)',
                'badge_border' => 'rgba(244, 63, 94, 0.3)',
                'badge_color' => '#fb7185',
                'logo_gradient' => 'linear-gradient(135deg, #f43f5e, #e11d48)',
                'logo_shadow' => '0 0 12px rgba(244, 63, 94, 0.4)',
            ],
            'restaurant_management' => [
                'label' => 'Restaurant',
                'icon' => 'fa-utensils',
                'badge_bg' => 'rgba(245, 158, 11, 0.15)',
                'badge_border' => 'rgba(245, 158, 11, 0.3)',
                'badge_color' => '#fbbf24',
                'logo_gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)',
                'logo_shadow' => '0 0 12px rgba(245, 158, 11, 0.4)',
            ],
            'housekeeping' => [
                'label' => 'Housekeeping',
                'icon' => 'fa-broom',
                'badge_bg' => 'rgba(16, 185, 129, 0.15)',
                'badge_border' => 'rgba(16, 185, 129, 0.3)',
                'badge_color' => '#34d399',
                'logo_gradient' => 'linear-gradient(135deg, #10b981, #059669)',
                'logo_shadow' => '0 0 12px rgba(16, 185, 129, 0.4)',
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'icon' => 'fa-wrench',
                'badge_bg' => 'rgba(168, 85, 247, 0.15)',
                'badge_border' => 'rgba(168, 85, 247, 0.3)',
                'badge_color' => '#c084fc',
                'logo_gradient' => 'linear-gradient(135deg, #a855f7, #7c3aed)',
                'logo_shadow' => '0 0 12px rgba(168, 85, 247, 0.4)',
            ],
        ];
        $theme = $roleThemes[$builderRole] ?? $roleThemes['front_desk'];
        $moduleLabel = $theme['label'];
        $roleLabelFull = \App\Support\HotelTemplateBuilder::ROLES[$builderRole] ?? $moduleLabel;
    @endphp
    <title>Hotel Management System | @yield('page-title', $moduleLabel)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #050507; color: #fff; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: #0a0a0c; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }

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
            background: {{ $theme['logo_gradient'] }};
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: {{ $theme['logo_shadow'] }};
        }

        .module-badge {
            background: {{ $theme['badge_bg'] }};
            border: 1px solid {{ $theme['badge_border'] }};
            color: {{ $theme['badge_color'] }};
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .module-switcher { position: relative; flex-shrink: 0; }
        .module-switcher .module-badge {
            display: inline-flex; align-items: center; gap: 6px;
            cursor: pointer; transition: filter 0.15s;
        }
        .module-switcher .module-badge:hover { filter: brightness(1.25); }
        .module-menu {
            position: absolute; top: calc(100% + 10px); left: 0; width: 230px;
            background: #18181b; border: 1px solid #27272a; border-radius: 12px;
            padding: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 999;
            display: none; animation: dropIn 0.2s ease-out;
        }
        .module-menu.show { display: block; }
        .module-menu-label {
            padding: 4px 12px 8px; font-size: 10px; letter-spacing: 0.12em;
            text-transform: uppercase; color: #52525b; font-weight: 700;
        }
        .module-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 6px;
            color: #a1a1aa; font-size: 13px; text-decoration: none;
            transition: all 0.15s;
        }
        .module-menu a:hover { background: #27272a; color: #fafafa; }
        .module-menu a.is-active {
            background: {{ $theme['badge_bg'] }};
            color: {{ $theme['badge_color'] }};
        }
        .module-menu a i { width: 16px; font-size: 13px; }
        .module-menu .view-only-chip {
            margin-left: auto; font-size: 9px; letter-spacing: 0.06em;
            text-transform: uppercase; font-weight: 700;
            color: #fbbf24; background: rgba(245,158,11,0.12);
            border: 1px solid rgba(245,158,11,0.25);
            padding: 2px 6px; border-radius: 4px;
        }

        .sidebar-base {
            background: #09090b;
            border-color: rgba(255,255,255,0.05);
        }

        .content-bg {
            background: #18181b;
            background-image: radial-gradient(rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 24px 24px;
        }

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

        .hdr-btn {
            height: 36px; padding: 0 16px; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', sans-serif; border: none;
            display: flex; align-items: center; gap: 6px; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-secondary { background: #18181b; color: #d4d4d8; border: 1px solid #27272a; }
        .btn-secondary:hover { background: #27272a; color: #fff; }

        #toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px);
            background: #18181b; border: 1px solid #27272a; color: #fafafa; padding: 12px 24px; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
            opacity: 0; z-index: 9999;
        }
        #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .status-bar { background: #09090b; border-top: 1px solid #18181b; }

        .topbar-row {
            display: flex; align-items: center; gap: 12px;
            width: 100%; height: 56px; padding: 0 16px; box-sizing: border-box;
        }
        .topbar-brand { display: flex; align-items: center; gap: 10px; flex-shrink: 0; min-width: 0; }
        .topbar-mid { flex: 1 1 auto; min-width: 0; }
        .topbar-actions {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 8px; flex-shrink: 0; flex-wrap: nowrap;
        }
        .topbar-actions #profileWrapper { margin-left: 2px; flex-shrink: 0; }
        .topbar-actions .avatar { width: 26px; height: 26px; font-size: 10px; }
    </style>
    @yield('head-extra')
</head>
<body class="h-screen flex flex-col overflow-hidden">

    <header class="topbar topbar-row shrink-0 z-50">
        <div class="topbar-brand">
            <div class="logo-icon shrink-0">
                <i class="fas {{ $theme['icon'] }} text-white text-sm"></i>
            </div>
            <span class="hms-logo-text text-sm truncate">Hotel Management System</span>
            <div class="w-px h-5 bg-zinc-800 shrink-0"></div>
            @php
                $myModules = \App\Support\HotelTemplateBuilder::modulesForRoles($studentRoles ?? []);
            @endphp
            @if(count($myModules) > 1)
                <div class="module-switcher" id="moduleSwitcher">
                    <span class="module-badge" onclick="toggleModuleMenu()" title="Switch module">
                        {{ $moduleLabel }}
                        <i class="fas fa-chevron-down text-[8px]" id="moduleChevron"></i>
                    </span>
                    <div class="module-menu" id="moduleMenu">
                        <p class="module-menu-label">My Modules</p>
                        @foreach($myModules as $module)
                            <a href="{{ route($module['route']) }}"
                               class="{{ $module['role'] === $builderRole ? 'is-active' : '' }}">
                                <i class="fas {{ $roleThemes[$module['role']]['icon'] ?? 'fa-layer-group' }}"></i>
                                {{ $module['label'] }}
                                @if(!$module['editable'])
                                    <span class="view-only-chip">View only</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <span class="module-badge shrink-0">{{ $moduleLabel }}</span>
            @endif
            <span class="module-badge shrink-0" style="background:rgba(255,255,255,0.06); border-color:rgba(255,255,255,0.1); color:#d4d4d8;">@yield('page-title', 'Staff Tool')</span>
        </div>

        <div class="topbar-mid"></div>

        <div class="topbar-actions">
            <a href="{{ route($backRoute ?? 'students.dashboard') }}" class="hdr-btn btn-secondary" title="Back to builder">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <div class="relative" id="profileWrapper">
                <?php
                    $authUser = auth()->user();
                    $profileName = $authUser?->name ?? 'Student';
                    $nameParts = preg_split('/\s+/', trim($profileName));
                    $initials = strtoupper(($nameParts[0][0] ?? 'S') . (count($nameParts) > 1 ? substr(end($nameParts), 0, 1) : ''));
                ?>
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

    <div id="mainLayout" class="flex flex-1 overflow-hidden">
        <div id="leftSidebar" class="w-72 shrink-0 sidebar-base border-r overflow-y-auto">
            @include('students.frontdesk.left-sidebar.index')
        </div>

        <div id="opsContentWrap" class="flex-1 min-w-0 content-bg overflow-y-auto">
            @yield('content')
        </div>
    </div>

    <div class="status-bar h-8 flex items-center px-6 gap-4 shrink-0 text-[10px] text-zinc-600">
        <div class="flex items-center gap-2 text-green-500"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> <span>Online</span></div>
    </div>

    <div id="toast"></div>

    <script>
        function toggleDropdown() { const dd = document.getElementById('profileDropdown'); const ch = document.getElementById('chevron'); dd.classList.toggle('show'); ch.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : ''; }
        document.addEventListener('click', function(e) { const w = document.getElementById('profileWrapper'); if (w && !w.contains(e.target)) { document.getElementById('profileDropdown')?.classList.remove('show'); const ch = document.getElementById('chevron'); if (ch) ch.style.transform = ''; } });

        function toggleModuleMenu() {
            const menu = document.getElementById('moduleMenu');
            const chevron = document.getElementById('moduleChevron');
            if (!menu) return;
            menu.classList.toggle('show');
            if (chevron) chevron.style.transform = menu.classList.contains('show') ? 'rotate(180deg)' : '';
        }
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('moduleSwitcher');
            if (!wrapper || wrapper.contains(e.target)) return;
            document.getElementById('moduleMenu')?.classList.remove('show');
            const chevron = document.getElementById('moduleChevron');
            if (chevron) chevron.style.transform = '';
        });

        let toastTimer;
        function toast(msg) { const t = document.getElementById('toast'); t.textContent = msg; t.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(()=>t.classList.remove('show'), 2000); }

        async function syncGroupPresence() {
            try {
                const res = await fetch(@json(route('students.group.presence')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: '{}'
                });
                if (!res.ok) return;
                const data = await res.json();
                (data.members || []).forEach(function (m) {
                    const el = document.querySelector('[data-member-online="' + m.id + '"]');
                    if (!el) return;
                    el.classList.toggle('bg-emerald-400', !!m.online);
                    el.classList.toggle('bg-zinc-600', !m.online);
                    const row = el.closest('.items-start');
                    const label = row && row.querySelector('[data-member-online-label]');
                    if (label) {
                        label.textContent = m.online ? 'Online' : 'Offline';
                        label.classList.toggle('text-emerald-400', !!m.online);
                        label.classList.toggle('text-zinc-600', !m.online);
                    }
                });
            } catch (e) { /* ignore */ }
        }
        setInterval(syncGroupPresence, 8000);
        document.addEventListener('DOMContentLoaded', syncGroupPresence);
    </script>
    @yield('scripts')
</body>
</html>
