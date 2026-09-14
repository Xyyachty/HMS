@php
    /* The background the team picked for their site, turned into the same
       palette the landing page derives, so the staff shell follows the hotel
       instead of staying stock near-black. Null until a team picks a colour,
       and then the shell keeps the stock palette it has always had. */
    $opsSitePalette = \App\Support\SitePalette::forBackground(
        \App\Support\SitePalette::siteBackground($templateCustomizations ?? []),
        ($selectedTemplate ?? null) === '2' ? '2' : '1'
    );
@endphp
<!DOCTYPE html>
<html lang="en" data-ops-theme="{{ ($selectedTemplate ?? null) === '2' ? '2' : '1' }}"@if($opsSitePalette) data-ops-site-themed="1"@endif>
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
                'primary' => '#0891b2',
                'primary_hover' => '#0e7490',
                'primary_glow' => 'rgba(6,182,212,0.3)',
                'outline' => '#06b6d4',
                'logo_gradient' => 'linear-gradient(135deg, #2563eb, #4f46e5)',
                'logo_shadow' => '0 0 12px rgba(37, 99, 235, 0.4)',
            ],
            'room_management' => [
                'label' => 'Room Management',
                'icon' => 'fa-bed',
                'badge_bg' => 'rgba(244, 63, 94, 0.15)',
                'badge_border' => 'rgba(244, 63, 94, 0.3)',
                'badge_color' => '#fb7185',
                'primary' => '#e11d48',
                'primary_hover' => '#be123c',
                'primary_glow' => 'rgba(244,63,94,0.35)',
                'outline' => '#f43f5e',
                'logo_gradient' => 'linear-gradient(135deg, #f43f5e, #e11d48)',
                'logo_shadow' => '0 0 12px rgba(244, 63, 94, 0.4)',
            ],
            'restaurant_management' => [
                'label' => 'Restaurant',
                'icon' => 'fa-utensils',
                'badge_bg' => 'rgba(245, 158, 11, 0.15)',
                'badge_border' => 'rgba(245, 158, 11, 0.3)',
                'badge_color' => '#fbbf24',
                'primary' => '#d97706',
                'primary_hover' => '#b45309',
                'primary_glow' => 'rgba(245,158,11,0.35)',
                'outline' => '#f59e0b',
                'logo_gradient' => 'linear-gradient(135deg, #f59e0b, #d97706)',
                'logo_shadow' => '0 0 12px rgba(245, 158, 11, 0.4)',
            ],
            'housekeeping' => [
                'label' => 'Housekeeping',
                'icon' => 'fa-broom',
                'badge_bg' => 'rgba(16, 185, 129, 0.15)',
                'badge_border' => 'rgba(16, 185, 129, 0.3)',
                'badge_color' => '#34d399',
                'primary' => '#059669',
                'primary_hover' => '#047857',
                'primary_glow' => 'rgba(16,185,129,0.35)',
                'outline' => '#10b981',
                'logo_gradient' => 'linear-gradient(135deg, #10b981, #059669)',
                'logo_shadow' => '0 0 12px rgba(16, 185, 129, 0.4)',
            ],
            'maintenance' => [
                'label' => 'Maintenance',
                'icon' => 'fa-wrench',
                'badge_bg' => 'rgba(168, 85, 247, 0.15)',
                'badge_border' => 'rgba(168, 85, 247, 0.3)',
                'badge_color' => '#c084fc',
                'primary' => '#9333ea',
                'primary_hover' => '#7e22ce',
                'primary_glow' => 'rgba(168,85,247,0.35)',
                'outline' => '#a855f7',
                'logo_gradient' => 'linear-gradient(135deg, #a855f7, #7c3aed)',
                'logo_shadow' => '0 0 12px rgba(168, 85, 247, 0.4)',
            ],
        ];
        $theme = $roleThemes[$builderRole] ?? $roleThemes['front_desk'];
        $moduleLabel = $theme['label'];
        $roleLabelFull = \App\Support\HotelTemplateBuilder::ROLES[$builderRole] ?? $moduleLabel;
    @endphp
    <title>Hotel Management System | {{ $moduleLabel }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
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

        /* ── MODULE SWITCHER (only when a member holds more than one role) ── */
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
            outline: 2px dashed {{ $theme['outline'] }};
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
            text-decoration: none;
        }
        .btn-secondary { background: #18181b; color: #d4d4d8; border: 1px solid #27272a; }
        .btn-secondary:hover { background: #27272a; color: #fff; }
        .btn-primary { background: {{ $theme['primary'] }}; color: #fff; }
        .btn-primary:hover { background: {{ $theme['primary_hover'] }}; box-shadow: 0 0 20px {{ $theme['primary_glow'] }}; }

        /* Submit Changes — the one button that leaves the draft behind and puts
           the work in front of faculty, so it does not share Publish's styling. */
        .btn-submit { background: #059669; color: #fff; border: 1px solid #047857; }
        .btn-submit:hover { background: #047857; box-shadow: 0 0 20px rgba(5, 150, 105, 0.35); }
        .btn-submit:disabled { opacity: 0.55; cursor: not-allowed; box-shadow: none; }

        /* Save state, spelled out in the status bar rather than only in a toast:
           saving, saved and submitted have to be told apart at a glance. */
        .save-hint.is-saving { color: #fbbf24; }
        .save-hint.is-saved { color: #34d399; }
        .save-hint.is-submitted { color: #34d399; font-weight: 700; }
        .save-hint.is-dirty { color: #fca5a5; }

        /* Toast */
        #toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px);
            background: #18181b; border: 1px solid #27272a; color: #fafafa; padding: 12px 24px; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
            opacity: 0; z-index: 9999;
        }
        #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        /* ── Fullscreen Redesign Mode ── */
        body.fs-redesign .topbar { height: 52px; }
        body.fs-redesign .topbar-row { height: 52px; }
        body.fs-redesign #leftSidebar { display: none !important; }
        body.fs-redesign #mainLayout { position: relative; }
        body.fs-redesign #centerCanvasWrap {
            flex: 1 1 100%;
            min-width: 0;
            padding: 0;
        }
        body.fs-redesign #centerCanvasWrap > div { padding: 0 !important; }
        body.fs-redesign .canvas-frame {
            border-radius: 0;
            border: none;
            box-shadow: none;
            height: 100%;
        }
        body.fs-redesign .browser-bar {
            background: #0a0a0c;
            border-bottom: 1px solid #27272a;
            color: #a1a1aa;
        }
        body.fs-redesign .url-pill {
            background: #18181b;
            color: #e4e4e7;
            border-color: #27272a;
        }
        body.fs-redesign #rightSidebar {
            position: fixed;
            top: 52px;
            right: 0;
            bottom: 0;
            width: 320px;
            max-width: 90vw;
            z-index: 60;
            transform: translateX(0);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: -12px 0 40px rgba(0,0,0,0.55);
            border-left: 1px solid #27272a;
        }
        body.fs-redesign.fs-panel-collapsed #rightSidebar {
            transform: translateX(100%);
        }
        body.fs-redesign #fsPanelToggle {
            display: flex !important;
        }
        #fsPanelToggle { display: none; }
        .fs-float-btn {
            position: fixed;
            z-index: 70;
            right: 16px;
            bottom: 20px;
            height: 44px;
            padding: 0 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, #06b6d4, #2563eb);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 28px rgba(6, 182, 212, 0.35);
            transition: transform 0.15s ease;
        }
        .fs-float-btn:hover { transform: scale(1.04); }
        body.fs-redesign.fs-panel-collapsed .fs-float-btn { right: 16px; }
        body.fs-redesign:not(.fs-panel-collapsed) .fs-float-btn { right: 336px; }
        .topbar-row {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            height: 56px;
            padding: 0 16px;
            box-sizing: border-box;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            min-width: 0;
        }
        .topbar-modes {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .topbar-mid {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 8px;
        }
        .topbar-mid .save-hint {
            font-size: 11px;
            color: #71717a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-shrink: 0;
            flex-wrap: nowrap;
        }
        .topbar-actions .hdr-btn {
            white-space: nowrap;
            height: 34px;
            padding: 0 12px;
        }
        .topbar-actions #profileWrapper {
            margin-left: 2px;
            flex-shrink: 0;
        }
        .topbar-actions .avatar {
            width: 26px;
            height: 26px;
            font-size: 10px;
        }
        @media (max-width: 1100px) {
            .topbar-mid .save-hint { display: none; }
            .hms-logo-text { max-width: 140px; }
        }
        @media (max-width: 900px) {
            #fsToggleLabel { display: none; }
        }

        /* ── Template 2 shell theme (cream / forest green / Cormorant + DM Sans) ──
           Scoped entirely under [data-ops-theme="2"] on <html> above, so a
           Template 1 team (or one that hasn't chosen yet) renders exactly as
           before. The canvas/dropzone itself (the live site preview) is left
           alone — it already renders the real published page. */
        html[data-ops-theme="2"] body { background: #f7f4ef; color: #1a1a1a; }
        html[data-ops-theme="2"] .topbar {
            background: rgba(247, 244, 239, 0.9);
            border-bottom: 1px solid #e2ddd5;
        }
        html[data-ops-theme="2"] .hms-logo-text {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        html[data-ops-theme="2"] .w-px.h-5.bg-zinc-800 { background: #e2ddd5; }
        html[data-ops-theme="2"] .sidebar-base { background: #ffffff; border-color: #e2ddd5; }
        html[data-ops-theme="2"] .canvas-bg {
            background: #f7f4ef;
            background-image: radial-gradient(rgba(27,67,50,0.05) 1px, transparent 1px);
        }
        html[data-ops-theme="2"] .tool-card:hover { background: rgba(27,67,50,0.05); border-color: #e2ddd5; }
        html[data-ops-theme="2"] .hdr-btn.btn-secondary { background: #ffffff; color: #1a1a1a; border: 1px solid #e2ddd5; }
        html[data-ops-theme="2"] .hdr-btn.btn-secondary:hover { background: #efe9e0; color: #1b4332; }
        html[data-ops-theme="2"] .profile-dropdown,
        html[data-ops-theme="2"] .module-menu {
            background: #ffffff; border: 1px solid #e2ddd5; box-shadow: 0 10px 40px rgba(26,26,26,0.12);
        }
        html[data-ops-theme="2"] .dd-item,
        html[data-ops-theme="2"] .module-menu a { color: #4a4642; }
        html[data-ops-theme="2"] .dd-item:hover,
        html[data-ops-theme="2"] .module-menu a:hover { background: #efe9e0; color: #1a1a1a; }
        html[data-ops-theme="2"] .dd-item i { color: #7a7570; }
        html[data-ops-theme="2"] .dd-divider { background: #e2ddd5; }
        html[data-ops-theme="2"] .module-menu-label { color: #9a958e; }
        html[data-ops-theme="2"] #toast { background: #1a1a1a; border: 1px solid #2d2d2d; color: #f7f4ef; }
        html[data-ops-theme="2"] #editorModeTabs { background: rgba(27,67,50,0.05) !important; border-color: #e2ddd5 !important; }
        html[data-ops-theme="2"] .mode-tab { color: #7a7570 !important; }
        html[data-ops-theme="2"] .mode-tab.active-tab {
            background: #ffffff !important; border: 1px solid #e2ddd5 !important; color: #1b4332 !important;
        }

        /* Right-panel design controls */
        html[data-ops-theme="2"] .settings-card { background: rgba(27,67,50,0.03); border-color: #e2ddd5; }
        html[data-ops-theme="2"] .settings-label { color: #9a958e; }
        html[data-ops-theme="2"] .settings-input,
        html[data-ops-theme="2"] .settings-select,
        html[data-ops-theme="2"] .style-input {
            background: #ffffff; border-color: #e2ddd5; color: #1a1a1a;
        }
        html[data-ops-theme="2"] .settings-input:focus,
        html[data-ops-theme="2"] .style-input:focus { border-color: #2d6a4f; }
        html[data-ops-theme="2"] .style-input option { background: #ffffff; color: #1a1a1a; }
        html[data-ops-theme="2"] .color-swatch.active { border-color: #1a1a1a; box-shadow: 0 0 0 2px #2d6a4f; }
        html[data-ops-theme="2"] #hbLayoutList > div,
        html[data-ops-theme="2"] #hbVersionList > div {
            background: rgba(27,67,50,0.03) !important; border-color: #e2ddd5 !important;
        }
        html[data-ops-theme="2"] #hbLayoutList .text-zinc-300,
        html[data-ops-theme="2"] #hbVersionList .text-zinc-200 { color: #1a1a1a !important; }
        html[data-ops-theme="2"] #hbLayoutList p.text-zinc-600,
        html[data-ops-theme="2"] #hbVersionList p.text-zinc-600,
        html[data-ops-theme="2"] #hbVersionList p.text-\[10px\] { color: #9a958e !important; }
        html[data-ops-theme="2"] #hbLayoutList button.text-zinc-500,
        html[data-ops-theme="2"] #hbVersionList button.text-zinc-500 { color: #9a958e !important; }
        html[data-ops-theme="2"] #hbLayoutList button.hover\:text-white:hover { color: #1a1a1a !important; }
        html[data-ops-theme="2"] #hbLayoutList button.hover\:text-cyan-400:hover,
        html[data-ops-theme="2"] #hbVersionList .text-cyan-400 { color: #1b4332 !important; }
        html[data-ops-theme="2"] #hbLayoutList button.hover\:text-rose-400:hover { color: #be123c !important; }

        /* Shared left-sidebar partial (member list + Staff Tools nav) — the
           partial file itself is never edited, only overridden here. */
        html[data-ops-theme="2"] #leftSidebar .text-white { color: #1a1a1a; }
        html[data-ops-theme="2"] #leftSidebar .text-zinc-200 { color: #4a4642; }
        html[data-ops-theme="2"] #leftSidebar .text-zinc-500,
        html[data-ops-theme="2"] #leftSidebar .text-zinc-600 { color: #7a7570; }
        html[data-ops-theme="2"] #leftSidebar .border-zinc-800 { border-color: #e2ddd5; }
        html[data-ops-theme="2"] #leftSidebar .bg-zinc-800 { background: #f7f4ef; }
        html[data-ops-theme="2"] #leftSidebar .border-zinc-700 { border-color: #e2ddd5; }
        html[data-ops-theme="2"] #leftSidebar a.hover\:border-emerald-500\/50:hover { border-color: #2d6a4f !important; color: #1a1a1a !important; }
        html[data-ops-theme="2"] #leftSidebar .border-emerald-500\/50 { border-color: #2d6a4f !important; }
        html[data-ops-theme="2"] #leftSidebar .text-emerald-400 { color: #1b4332; }
        html[data-ops-theme="2"] #leftSidebar .text-cyan-400 { color: #c17849; }
        html[data-ops-theme="2"] #leftSidebar .bg-zinc-950\/80 { background: #efe9e0; }
        html[data-ops-theme="2"] #leftSidebar #backToTasksBtn { background: #ffffff; color: #1a1a1a; border-color: #e2ddd5; }
        html[data-ops-theme="2"] #leftSidebar #backToTasksBtn:hover { background: #efe9e0; border-color: #2d6a4f; color: #1b4332; }

        /* Assigned Tasks: the revision pill has no zinc/cyan/emerald class to ride
           the theme overrides above, so it carries its own for both shells. */
        #leftSidebar .assigned-task-revision { color: #fbbf24; background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.35); }
        html[data-ops-theme="2"] #leftSidebar .assigned-task-revision { color: #b45309; background: rgba(180, 83, 9, 0.08); border-color: rgba(180, 83, 9, 0.3); }
        #leftSidebar .assigned-task-card.is-active { box-shadow: inset 3px 0 0 #34d399; }
        html[data-ops-theme="2"] #leftSidebar .assigned-task-card.is-active { box-shadow: inset 3px 0 0 #2d6a4f; }
    </style>
@if($opsSitePalette)
    @include('students.builder.site-theme', ['p' => $opsSitePalette])
@endif
</head>
<body class="h-screen flex flex-col overflow-hidden">

    <!-- ═══════ TOP NAVIGATION BAR ═══════ -->
    <header class="topbar topbar-row shrink-0 z-50">
        <div class="topbar-brand">
            <div class="logo-icon shrink-0">
                <i class="fas {{ $theme['icon'] }} text-white text-sm"></i>
            </div>
            <span class="hms-logo-text text-sm truncate">Hotel Management System</span>
            <div class="w-px h-5 bg-zinc-800 shrink-0"></div>
            @php
                // A member may hold several roles; give them a way to move between
                // modules. Filtered to editable ones — this switcher lives inside
                // Customize, so a role with nothing to design has no reason here.
                $myModules = \App\Support\HotelTemplateBuilder::modulesForRoles($studentRoles ?? []);
                $editorModules = array_values(array_filter($myModules, fn ($m) => $m['editable']));
            @endphp
            @if(count($editorModules) > 1)
                <div class="module-switcher" id="moduleSwitcher">
                    <span class="module-badge" onclick="toggleModuleMenu()" title="Switch module">
                        {{ $moduleLabel }}
                        <i class="fas fa-chevron-down text-[8px]" id="moduleChevron"></i>
                    </span>
                    <div class="module-menu" id="moduleMenu">
                        <p class="module-menu-label">My Modules</p>
                        @foreach($editorModules as $module)
                            <a href="{{ $module['customize_url'] }}"
                               class="{{ $module['role'] === $builderRole ? 'is-active' : '' }}"
                               @if($module['role'] !== $builderRole) onclick="return confirmLeaveBuilder(event)" @endif>
                                <i class="fas {{ $roleThemes[$module['role']]['icon'] ?? 'fa-layer-group' }}"></i>
                                {{ $module['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- The role name and template number used to sit here as static
                 badges (FRONT DESK / Template 1). Both are redundant with the
                 sidebar now — every member's role is labelled there, and the
                 template number lives above the member list — so the header
                 stays just the brand and, for a multi-role member, the module
                 switcher above. --}}
        </div>

        <div class="topbar-modes">
            <div class="flex items-center gap-1 bg-zinc-900/60 rounded-lg p-1 border border-zinc-800" id="editorModeTabs">
                @if($canEditTemplate ?? false)
                    <button onclick="setMode(this,'design')" class="mode-tab active-tab px-4 py-2 rounded-md text-xs text-white transition-all bg-zinc-800 border border-zinc-700">Design</button>
                    <button onclick="setMode(this,'preview')" class="mode-tab px-4 py-2 rounded-md text-xs font-semibold text-zinc-500 transition-all border border-transparent">Preview</button>
                @else
                    <span class="px-3 py-2 rounded-md text-xs font-semibold text-amber-300/90 bg-amber-500/10 border border-amber-500/20">View only</span>
                @endif
            </div>
        </div>

        <div class="topbar-mid">
            <span class="save-hint" id="autoSaveStatus">Ready · Ctrl+S to save</span>
        </div>

        <div class="topbar-actions">
            <button id="fsToggleBtn" class="hdr-btn btn-secondary" onclick="toggleFullscreenRedesign()" title="Fullscreen">
                <i class="fas fa-expand" id="fsToggleIcon"></i>
                <span id="fsToggleLabel">Fullscreen</span>
            </button>
            @if($canEditTemplate ?? false)
                @if(!empty($selectedTemplate))
                    <a href="{{ route('students.frontdesk.template.' . $selectedTemplate, ['role' => $builderRole]) }}" target="_blank" rel="noopener" class="hdr-btn btn-secondary" title="View live site">
                        <i class="fas fa-up-right-from-square"></i> View Live
                    </a>
                @else
                    <button type="button" class="hdr-btn btn-secondary" disabled title="Select a template first" style="opacity:.4;cursor:not-allowed;">
                        <i class="fas fa-up-right-from-square"></i> View Live
                    </button>
                @endif
                {{-- Save Draft and Publish used to live here as explicit buttons.
                     The work still saves the same way — autosave persists every
                     change, and Ctrl+S (saveTemplateDraft(false)) still forces
                     one — just without a button taking up toolbar space. --}}
                {{-- Saving and submitting are different acts, so they are different
                     buttons. Everything else here keeps a draft; this one hands the
                     work to faculty and closes the role's open tasks against a
                     snapshot of exactly what was handed in. --}}
                <button class="hdr-btn btn-submit" onclick="submitTemplateChanges()" id="submitChangesBtn"
                        title="Send this work to your faculty for review">
                    <i class="fas fa-circle-check"></i> Submit Changes
                </button>

            @endif

            <div class="relative" id="profileWrapper">
                <?php
                    $authUser = auth()->user();
                    // Single source of truth: User::initials reads first_name + last_name
                    // so a profile update is reflected here without re-derivation.
                    $initials = strtoupper((string) ($authUser?->initials ?? 'S'));
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

    <!-- ═══════ MAIN 3-COLUMN LAYOUT ═══════ -->
    <div id="mainLayout" class="flex flex-1 overflow-hidden">
        <div id="leftSidebar" class="w-72 shrink-0 sidebar-base border-r overflow-y-auto">
            @include('students.frontdesk.left-sidebar.index', ['showStaffTools' => false, 'showAssignedTasks' => true])
        </div>

        <div id="centerCanvasWrap" class="flex-1 flex flex-col min-w-0 canvas-bg">
            @include('students.frontdesk.center-canvas.index')
        </div>

        <div id="rightSidebar" class="w-80 shrink-0 sidebar-base border-l overflow-y-auto">
            @include('students.frontdesk.right-sidebar.index')
        </div>

    </div>

    <button type="button" id="fsPanelToggle" class="fs-float-btn {{ ($canEditTemplate ?? false) ? '' : 'hidden' }}" onclick="toggleDesignPanel()" title="Toggle design panel">
        <i class="fas fa-palette" id="fsPanelIcon"></i>
        <span id="fsPanelLabel">Design Panel</span>
    </button>

    <div id="toast"></div>

    <script src="{{ asset('js/hms-hotel-builder.js') }}?v={{ filemtime(public_path('js/hms-hotel-builder.js')) }}"></script>
    <script>
        window.HMS_CAN_EDIT_TEMPLATE = @json((bool) ($canEditTemplate ?? false));
        window.currentEditorMode = window.HMS_CAN_EDIT_TEMPLATE ? 'design' : 'preview';
        window.templateCustomizations = @json($templateCustomizations ?? []);
        window.templateSyncVersion = @json($templateVersion ?? 0);
        window.templateLayout = @json($templateLayout ?? []);

        window.hmsBuilder = new HMSHotelBuilder({
            role: @json($builderRole),
            canEdit: window.HMS_CAN_EDIT_TEMPLATE,
            mode: window.HMS_CAN_EDIT_TEMPLATE ? 'build' : 'preview',
            initial: Object.assign({
                customizations: window.templateCustomizations,
                layout: window.templateLayout,
                selected_template: @json($selectedTemplate),
                sync_version: window.templateSyncVersion,
            }, @json($templatePayload ?? [])),
            routes: {
                sync: @json(route('students.templates.sync', ['role' => $builderRole])),
                save: @json(route('students.templates.save', ['role' => $builderRole])),
                autosave: @json(route('students.templates.autosave', ['role' => $builderRole])),
                versions: @json(route('students.templates.versions', ['role' => $builderRole])),
                restore: @json(route('students.templates.restore', ['role' => $builderRole, 'version' => '__VERSION__'])),
            },
            onToast: function (msg) { if (typeof toast === 'function') toast(msg); },
            onChange: function (evt) {
                if (evt.type === 'dirty') {
                    setSaveDraftUnsaved(!!evt.dirty);
                }
                if (evt.type === 'saving') {
                    setSaveState('saving');
                }
                if (evt.type === 'autosaved') {
                    // Autosave keeps the draft and nothing else - it must never
                    // read as though the work has been handed in.
                    setSaveState('saved', 'Draft auto-saved \u00b7 ' + new Date().toLocaleTimeString());
                }
                if (evt.type === 'mode') {
                    const designMode = evt.mode === 'build';
                    window.currentEditorMode = designMode ? 'design' : 'preview';
                    if (typeof postToTemplate === 'function') {
                        postToTemplate({ type: 'set-mode', mode: window.currentEditorMode });
                        postToTemplate({ type: 'set-can-edit', canEdit: window.HMS_CAN_EDIT_TEMPLATE && designMode });
                    }
                    document.getElementById('hbModeBuild')?.classList.toggle('bg-zinc-800', designMode);
                    document.getElementById('hbModeBuild')?.classList.toggle('text-white', designMode);
                    document.getElementById('hbModePreview')?.classList.toggle('bg-zinc-800', !designMode);
                    document.getElementById('hbModePreview')?.classList.toggle('text-white', !designMode);
                }
                if (evt.type === 'state' || evt.type === 'layout') {
                    window.templateLayout = (evt.template && evt.template.layout) || evt.layout || window.templateLayout;
                    renderHbLayout();
                    if (evt.template && evt.template.customizations && typeof postToTemplate === 'function') {
                        postToTemplate({ type: 'load-customizations', customizations: evt.template.customizations });
                    }
                    const st = document.getElementById('hbStatus');
                    if (st && evt.template) st.textContent = 'v' + (evt.template.version || '—') + ' · synced';
                }
            }
        });

        function renderHbLayout() {
            const list = document.getElementById('hbLayoutList');
            if (!list) return;
            const layout = window.hmsBuilder.state.layout || [];
            const lib = @json(\App\Support\HotelTemplateBuilder::COMPONENT_LIBRARY);
            if (!layout.length) {
                list.innerHTML = '<p class="text-zinc-600">No sections yet. Add from the library.</p>';
                return;
            }
            list.innerHTML = layout.map(function (row, idx) {
                const label = (lib[row.id] && lib[row.id].label) || row.id;
                const vis = row.visible !== false;
                return '<div class="flex items-center gap-1 px-2 py-1.5 rounded-lg border border-zinc-800 bg-zinc-900/50">' +
                    '<span class="flex-1 text-zinc-300 truncate">' + label + (vis ? '' : ' (hidden)') + '</span>' +
                    (window.HMS_CAN_EDIT_TEMPLATE
                        ? '<button type="button" class="text-zinc-500 hover:text-white px-1" data-hb-up="' + idx + '" title="Move up">↑</button>' +
                          '<button type="button" class="text-zinc-500 hover:text-white px-1" data-hb-down="' + idx + '" title="Move down">↓</button>' +
                          '<button type="button" class="text-zinc-500 hover:text-cyan-400 px-1" data-hb-toggle="' + idx + '" title="Show/Hide">👁</button>' +
                          '<button type="button" class="text-zinc-500 hover:text-rose-400 px-1" data-hb-remove="' + idx + '" title="Remove">✕</button>'
                        : '') +
                    '</div>';
            }).join('');
        }

        async function renderHbVersions() {
            const box = document.getElementById('hbVersionList');
            if (!box) return;
            try {
                const data = await window.hmsBuilder.loadVersions();
                const versions = data.versions || [];
                if (!versions.length) {
                    box.innerHTML = '<p class="text-zinc-600">No versions yet.</p>';
                    return;
                }
                box.innerHTML = versions.map(function (v) {
                    return '<div class="flex items-center gap-2 px-2 py-1.5 rounded-lg border border-zinc-800 bg-zinc-900/50">' +
                        '<div class="flex-1 min-w-0"><p class="text-zinc-200 font-semibold truncate">v' + v.version + ' · ' + (v.label || 'Snapshot') + '</p>' +
                        '<p class="text-[10px] text-zinc-600">' + (v.created_at || '') + '</p></div>' +
                        (window.HMS_CAN_EDIT_TEMPLATE
                            ? '<button type="button" class="text-[10px] text-cyan-400 hover:underline" data-hb-restore="' + v.version + '">Restore</button>'
                            : '') +
                        '</div>';
                }).join('');
            } catch (e) {
                box.innerHTML = '<p class="text-rose-400">Could not load versions</p>';
            }
        }

        document.addEventListener('click', function (e) {
            const add = e.target.closest('.hb-add-component');
            if (add) window.hmsBuilder.addComponent(add.getAttribute('data-component'));
            const up = e.target.closest('[data-hb-up]');
            if (up) window.hmsBuilder.moveComponent(parseInt(up.getAttribute('data-hb-up'), 10), -1);
            const down = e.target.closest('[data-hb-down]');
            if (down) window.hmsBuilder.moveComponent(parseInt(down.getAttribute('data-hb-down'), 10), 1);
            const tog = e.target.closest('[data-hb-toggle]');
            if (tog) window.hmsBuilder.toggleComponent(parseInt(tog.getAttribute('data-hb-toggle'), 10));
            const rem = e.target.closest('[data-hb-remove]');
            if (rem) window.hmsBuilder.removeComponent(parseInt(rem.getAttribute('data-hb-remove'), 10));
            const rst = e.target.closest('[data-hb-restore]');
            if (rst) window.hmsBuilder.restoreVersion(parseInt(rst.getAttribute('data-hb-restore'), 10)).then(renderHbVersions);
        });

        document.getElementById('hbModeBuild')?.addEventListener('click', function () { window.hmsBuilder.setMode('build'); });
        document.getElementById('hbModePreview')?.addEventListener('click', function () { window.hmsBuilder.setMode('preview'); });
        document.getElementById('hbSaveBtn')?.addEventListener('click', function () { window.hmsBuilder.save(false).then(renderHbVersions); });
        document.getElementById('hbPublishBtn')?.addEventListener('click', function () { window.hmsBuilder.save(true).then(renderHbVersions); });
        document.getElementById('hbRefreshVersions')?.addEventListener('click', renderHbVersions);

        renderHbLayout();
        renderHbVersions();
        // 12s rather than 4s: the database is now remote, and with a class of ~44
        // students a 4s poll is roughly 11 sync queries a second against shared
        // free-tier compute, all day, for updates that are rarely that urgent.
        window.hmsBuilder.startPolling(12000);
        window.hmsBuilder.startAutoSave(7000);
    </script>
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
        function updateCount() { const n = dropzone.querySelectorAll('.dropped-block').length; const el = document.getElementById('blockCount'); if (el) el.textContent = n + ' block' + (n===1?'':'s') + ' on canvas'; }

        function toggleDropdown() { const dd = document.getElementById('profileDropdown'); const ch = document.getElementById('chevron'); if (!dd) return; dd.classList.toggle('show'); if (ch) ch.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : ''; }
        document.addEventListener('click', function(e) { const w = document.getElementById('profileWrapper'); if (w && !w.contains(e.target)) { document.getElementById('profileDropdown')?.classList.remove('show'); const ch = document.getElementById('chevron'); if (ch) ch.style.transform = ''; } });

        // Module switcher — only present when the member holds more than one role.
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
        function handleLogout() { document.getElementById('profileDropdown').classList.remove('show'); toast('Logging out…'); }

        function syncFullscreenUi(on) {
            const icon = document.getElementById('fsToggleIcon');
            const label = document.getElementById('fsToggleLabel');
            const cIcon = document.getElementById('fsCanvasIcon');
            const cLabel = document.getElementById('fsCanvasLabel');
            if (icon) icon.className = on ? 'fas fa-compress' : 'fas fa-expand';
            if (label) label.textContent = on ? 'Exit Fullscreen' : 'Fullscreen';
            if (cIcon) cIcon.className = on ? 'fas fa-compress' : 'fas fa-expand';
            if (cLabel) cLabel.textContent = on ? 'Exit' : 'Fullscreen';
            const panelLabel = document.getElementById('fsPanelLabel');
            const panelIcon = document.getElementById('fsPanelIcon');
            const collapsed = document.body.classList.contains('fs-panel-collapsed');
            if (panelLabel) panelLabel.textContent = collapsed ? 'Design Panel' : 'Hide Panel';
            if (panelIcon) panelIcon.className = collapsed ? 'fas fa-palette' : 'fas fa-chevron-right';
        }

        function toggleFullscreenRedesign(force) {
            const body = document.body;
            const entering = typeof force === 'boolean' ? force : !body.classList.contains('fs-redesign');
            body.classList.toggle('fs-redesign', entering);
            if (entering) {
                body.classList.remove('fs-panel-collapsed');
                // Enter Design mode so redesign works immediately
                const designBtn = document.querySelector('.mode-tab[onclick*="design"]');
                if (designBtn) setMode(designBtn, 'design');
                toast('Fullscreen redesign — click elements to edit');
            } else {
                body.classList.remove('fs-panel-collapsed');
                toast('Exited fullscreen');
            }
            syncFullscreenUi(entering);
        }

        function toggleDesignPanel() {
            if (!document.body.classList.contains('fs-redesign')) {
                toggleFullscreenRedesign(true);
                return;
            }
            document.body.classList.toggle('fs-panel-collapsed');
            syncFullscreenUi(true);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('fs-redesign')) {
                e.preventDefault();
                toggleFullscreenRedesign(false);
            }
            // Ctrl/Cmd + S — manual save draft
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey && (e.key === 's' || e.key === 'S')) {
                e.preventDefault();
                if (typeof saveTemplateDraft === 'function') saveTemplateDraft(false);
                return;
            }
            // Ctrl/Cmd + Shift + F for fullscreen redesign
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'f' || e.key === 'F')) {
                e.preventDefault();
                toggleFullscreenRedesign();
            }
        });

        function setMode(btn, mode) {
            if (!window.HMS_CAN_EDIT_TEMPLATE && (mode === 'design' || mode === 'build')) {
                toast('View only — faculty must assign {{ $roleLabelFull }} role to edit');
                return;
            }
            document.querySelectorAll('.mode-tab').forEach(t => {
                t.style.background = 'transparent';
                t.style.border = '1px solid transparent';
                t.style.color = '#71717a';
                t.classList.remove('active-tab');
            });
            if (btn) {
                btn.style.background = '#27272a';
                btn.style.border = '1px solid #3f3f46';
                btn.style.color = '#fff';
                btn.classList.add('active-tab');
            }
            const isBuild = mode === 'design' || mode === 'build';
            window.currentEditorMode = isBuild ? 'design' : 'preview';
            document.getElementById('rightSidebar')?.classList.toggle('hidden', !isBuild);
            if (window.hmsBuilder) {
                window.hmsBuilder.setMode(isBuild ? 'build' : 'preview');
            }
            if (typeof postToTemplate === 'function') {
                postToTemplate({ type: 'set-mode', mode: window.currentEditorMode });
                postToTemplate({ type: 'set-can-edit', canEdit: window.HMS_CAN_EDIT_TEMPLATE && isBuild });
            }
            if (isBuild) toast('Build mode — click, drag, and resize on the template');
            else toast('Preview mode — live hotel website (editing off)');
        }

        async function saveTemplateDraft(publish) {
            if (window.hmsBuilder) {
                try {
                    await window.hmsBuilder.save(!!publish);
                    if (typeof renderHbVersions === 'function') renderHbVersions();
                    setSaveState('saved', (publish ? 'Published \u00b7 ' : 'Draft saved \u00b7 ') + new Date().toLocaleTimeString());
                    setSaveDraftUnsaved(false);
                } catch (e) { /* toast already shown */ }
                return;
            }
            if (!window.HMS_CAN_EDIT_TEMPLATE) {
                toast('View only — {{ $roleLabelFull }} role required to save');
                return;
            }
            if (typeof postToTemplate === 'function') {
                postToTemplate({ type: 'request-customizations' });
            }
            await new Promise(r => setTimeout(r, 150));
            const customizations = window.templateCustomizations || {};
            try {
                const res = await fetch(@json(route('students.frontdesk.template.customizations')), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ customizations: customizations, publish: !!publish })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || data.message || 'Save failed');
                if (data.version) window.templateSyncVersion = data.version;
                toast(publish ? 'Page published \u2014 teammates will see updates' : 'Draft saved \u2014 teammates will see updates');
                setSaveState('saved', (publish ? 'Published \u00b7 ' : 'Draft saved \u00b7 ') + new Date().toLocaleTimeString());
                setSaveDraftUnsaved(false);
            } catch (err) {
                console.error(err);
                toast(err.message || 'Could not save template');
            }
        }

        /**
         * The single place that writes the save indicator.
         *
         * @param {'saving'|'saved'|'submitting'|'submitted'|'dirty'|'idle'} state
         * @param {string} [text] overrides the default wording for that state.
         */
        function setSaveState(state, text) {
            const status = document.getElementById('autoSaveStatus');
            if (!status) return;
            const stamp = new Date().toLocaleTimeString();
            const wording = {
                saving: 'Saving\u2026',
                saved: 'All changes saved \u00b7 ' + stamp,
                submitting: 'Submitting\u2026',
                submitted: 'Submitted for review \u00b7 ' + stamp,
                dirty: 'Unsaved changes \u2014 Ctrl+S to save',
                idle: 'Ready \u00b7 Ctrl+S to save',
            };
            status.textContent = text || wording[state] || wording.idle;
            status.classList.remove('is-saving', 'is-saved', 'is-submitted', 'is-dirty');
            if (state === 'saving' || state === 'submitting') status.classList.add('is-saving');
            else if (state === 'saved') status.classList.add('is-saved');
            else if (state === 'submitted') status.classList.add('is-submitted');
            else if (state === 'dirty') status.classList.add('is-dirty');
        }

        /**
         * Ask before something that cannot be taken back quietly.
         *
         * window.confirm paints the deployment's hostname over the page a student
         * is designing - "hms-9ojw.onrender.com says" above the question - which
         * reads as the browser interrupting rather than as the app asking. This is
         * the same question in the builder's own panel, and it falls back to the
         * browser dialog only if the bundle failed to load: a question that never
         * appears is worse than an ugly one.
         */
        async function askConfirm(title, text, confirmText) {
            if (!window.Swal) return window.confirm(title + '\n\n' + text);

            const result = await window.Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                iconColor: '#22d3ee',
                background: '#18181b',
                color: '#fafafa',
                showCancelButton: true,
                confirmButtonText: confirmText || 'Continue',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0891b2',
                cancelButtonColor: '#3f3f46',
                reverseButtons: true,
                focusCancel: true,
            });

            return result.isConfirmed;
        }

        /**
         * Hand this module's website work to faculty.
         *
         * Kept apart from saveTemplateDraft() on purpose: a draft save is a
         * private checkpoint, while this closes the role's open tasks and
         * notifies the faculty, so it asks first and then says plainly what
         * happened.
         */
        async function submitTemplateChanges() {
            if (!window.HMS_CAN_EDIT_TEMPLATE) {
                toast('View only \u2014 {{ $roleLabelFull }} role required to submit');
                return;
            }

            const ok = await askConfirm(
                'Submit this work for review?',
                'Your open tasks for this module will be handed in as they look right now. '
                + 'You can keep editing afterwards, but faculty reviews what you submit now.',
                'Submit for review'
            );
            if (!ok) return;

            const btn = document.getElementById('submitChangesBtn');
            if (btn) btn.disabled = true;
            setSaveState('submitting');

            try {
                // Flush what is on screen first, so faculty sees what the student
                // is looking at rather than the last autosave.
                if (typeof postToTemplate === 'function') {
                    postToTemplate({ type: 'request-customizations' });
                    await new Promise((r) => setTimeout(r, 200));
                }

                const res = await fetch(@json(route('students.templates.submit', ['role' => $builderRole])), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        customizations: window.templateCustomizations || {},
                        layout: window.templateLayout || [],
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || data.message || 'Could not submit');

                setSaveDraftUnsaved(false);
                setSaveState('submitted');
                toast(data.message || 'Submitted for faculty review');
                if (typeof renderHbVersions === 'function') renderHbVersions();
            } catch (err) {
                console.error(err);
                setSaveState('dirty', 'Not submitted \u2014 ' + (err.message || 'try again'));
                toast(err.message || 'Could not submit your changes');
            } finally {
                if (btn) btn.disabled = false;
            }
        }

        function setSaveDraftUnsaved(dirty) {
            // The Save Draft button that used to live in the toolbar is gone,
            // but the status text it drove is still the only "unsaved" signal
            // on the page, so it keeps working independent of the button.
            const btn = document.getElementById('saveDraftBtn');
            if (btn) {
                btn.classList.toggle('has-unsaved', !!dirty);
                btn.title = dirty ? 'Unsaved changes — click to save draft' : 'Save draft';
            }
            if (dirty) setSaveState('dirty');
        }

        function confirmLeaveBuilder(event) {
            const dirty = !!(window.hmsBuilder && window.hmsBuilder.isDirty && window.hmsBuilder.isDirty())
                || !!(document.getElementById('saveDraftBtn')?.classList.contains('has-unsaved'));
            if (!dirty) return true;

            // The builder auto-saves continuously, so there is nothing to actually
            // lose — flush the pending edit and leave instead of asking the student
            // to confirm losing work the system already persists on its own.
            const link = event && event.currentTarget;
            const href = link && link.getAttribute('href');
            if (event) event.preventDefault();

            const flush = (window.hmsBuilder && typeof window.hmsBuilder.save === 'function')
                ? window.hmsBuilder.save(false)
                : Promise.resolve();

            Promise.resolve(flush).catch(() => {}).finally(() => {
                if (href) window.location.href = href;
            });

            return false;
        }

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

        async function syncTemplateFromServer() {
            // Never replace a student's unsaved design buffer with a polling update.
            if (window.hmsBuilder && window.hmsBuilder._dirty && window.currentEditorMode === 'design') {
                return;
            }
            try {
                const res = await fetch(@json(route('students.templates.sync', ['role' => $builderRole])), {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const version = data.sync_version || data.version || 0;
                if (version && version !== window.templateSyncVersion) {
                    window.templateSyncVersion = version;
                    window.templateCustomizations = data.customizations || {};
                    if (data.layout) window.templateLayout = data.layout;
                    if (typeof postToTemplate === 'function') {
                        postToTemplate({ type: 'load-customizations', customizations: window.templateCustomizations });
                    }
                    const status = document.getElementById('autoSaveStatus');
                    if (status && !window.HMS_CAN_EDIT_TEMPLATE) {
                        status.textContent = 'Synced · ' + new Date().toLocaleTimeString();
                    }
                }
                if (typeof data.can_edit === 'boolean' && data.can_edit !== window.HMS_CAN_EDIT_TEMPLATE) {
                    window.location.reload();
                }
            } catch (e) { /* ignore */ }
        }

        // ── Assigned Tasks (left sidebar) ──
        // The team's tasks from faculty's Set Task, drawn from the seed the server
        // rendered and refreshed by syncAssignedTasks(). Clicking one opens the
        // page of the Default Template it is done on and brings its section into
        // view; a task in another module this student holds jumps to that editor,
        // where it can actually be edited.
        const ASSIGNED_TASKS_URL = @json(route('students.tasks.assigned'));
        const EDITOR_EDITABLE_PAGES = @json(array_values($editablePages ?? []));
        const TASK_PAGE_LABELS = { home: 'Home', rooms: 'Rooms', restaurant: 'Restaurant', amenities: 'Amenities', experience: 'Experience' };
        // Four cards a screen, the rest behind Prev/Next — the sidebar itself must
        // not grow its own scrollbar under a long list.
        const ASSIGNED_TASKS_PAGE_SIZE = 4;
        let assignedTasks = [];
        let assignedTaskPage = 0;
        let activeAssignedTaskId = null;
        let assignedTasksSignature = '';

        function readAssignedTaskSeed() {
            try {
                return JSON.parse(document.getElementById('assignedTaskSeed')?.textContent || '[]') || [];
            } catch (e) {
                return [];
            }
        }

        function assignedTaskStatusClasses(status) {
            if (status === 'completed') return 'text-emerald-400 bg-zinc-800 border-emerald-500/50';
            if (status === 'needs_revision') return 'assigned-task-revision';
            return 'text-cyan-400 bg-zinc-800 border-zinc-700';
        }

        function assignedTaskPageCount() {
            return Math.max(1, Math.ceil(assignedTasks.length / ASSIGNED_TASKS_PAGE_SIZE));
        }

        function renderAssignedTasks(tasks) {
            assignedTasks = Array.isArray(tasks) ? tasks : [];
            // A poll that shrinks the list (a task completed elsewhere) must not
            // leave the page pointed past the end.
            assignedTaskPage = Math.min(assignedTaskPage, assignedTaskPageCount() - 1);
            renderAssignedTaskPage();
        }

        function goToAssignedTaskPage(page) {
            assignedTaskPage = Math.max(0, Math.min(page, assignedTaskPageCount() - 1));
            renderAssignedTaskPage();
        }

        function renderAssignedTaskPage() {
            const list = document.getElementById('assignedTaskList');
            const pager = document.getElementById('assignedTaskPager');
            if (!list) return;

            list.replaceChildren();

            if (assignedTasks.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'text-[11px] text-zinc-500';
                empty.textContent = 'No assigned tasks yet.';
                list.appendChild(empty);
                if (pager) pager.classList.add('hidden');
                return;
            }

            const totalPages = assignedTaskPageCount();
            const start = assignedTaskPage * ASSIGNED_TASKS_PAGE_SIZE;
            const pageTasks = assignedTasks.slice(start, start + ASSIGNED_TASKS_PAGE_SIZE);

            pageTasks.forEach(function (task) {
                const card = document.createElement('button');
                card.type = 'button';
                card.dataset.taskId = task.id;
                card.className = 'assigned-task-card w-full text-left px-3 py-2.5 rounded-lg bg-zinc-800 border transition hover:border-emerald-500/50 '
                    + (task.id === activeAssignedTaskId ? 'border-emerald-500/50 is-active' : 'border-zinc-700');
                card.addEventListener('click', function (event) { openAssignedTask(task, event); });

                const top = document.createElement('div');
                top.className = 'flex items-center justify-between gap-2';
                const step = document.createElement('span');
                step.className = 'text-[10px] font-bold tracking-wider text-cyan-400';
                step.textContent = task.step_label || 'TASK';
                const pill = document.createElement('span');
                pill.className = 'shrink-0 px-1.5 py-0.5 rounded border text-[9px] font-bold uppercase tracking-wide ' + assignedTaskStatusClasses(task.status);
                pill.textContent = task.status_label;
                top.append(step, pill);

                const title = document.createElement('p');
                title.className = 'text-[12px] font-bold text-white leading-snug mt-1';
                title.textContent = task.title;

                const meta = document.createElement('p');
                meta.className = 'text-[10px] text-zinc-500 mt-0.5';
                meta.textContent = task.role_label + (task.due ? ' · Due: ' + task.due : '');

                card.append(top, title, meta);
                list.appendChild(card);
            });

            if (!pager) return;
            pager.classList.toggle('hidden', totalPages <= 1);
            if (totalPages <= 1) return;

            pager.replaceChildren();
            const prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'w-7 h-7 rounded-md border border-zinc-700 text-zinc-400 hover:text-white hover:border-emerald-500/50 disabled:opacity-30 disabled:hover:text-zinc-400 disabled:hover:border-zinc-700 flex items-center justify-center';
            prev.innerHTML = '<i class="fas fa-chevron-left text-[10px]"></i>';
            prev.disabled = assignedTaskPage === 0;
            prev.addEventListener('click', function () { goToAssignedTaskPage(assignedTaskPage - 1); });

            const label = document.createElement('span');
            label.className = 'text-[10px] font-semibold text-zinc-500';
            label.textContent = (assignedTaskPage + 1) + ' of ' + totalPages;

            const next = document.createElement('button');
            next.type = 'button';
            next.className = prev.className;
            next.innerHTML = '<i class="fas fa-chevron-right text-[10px]"></i>';
            next.disabled = assignedTaskPage >= totalPages - 1;
            next.addEventListener('click', function () { goToAssignedTaskPage(assignedTaskPage + 1); });

            pager.append(prev, label, next);
        }

        function markActiveAssignedTask(id) {
            activeAssignedTaskId = id;
            // The task being focused may sit on a different page of the list —
            // jump there so the highlighted card is the one actually visible.
            const index = assignedTasks.findIndex(function (t) { return t.id === id; });
            if (index !== -1) {
                const targetPage = Math.floor(index / ASSIGNED_TASKS_PAGE_SIZE);
                if (targetPage !== assignedTaskPage) {
                    assignedTaskPage = targetPage;
                    renderAssignedTaskPage();
                }
            }
            document.querySelectorAll('.assigned-task-card').forEach(function (card) {
                const active = Number(card.dataset.taskId) === id;
                card.classList.toggle('border-emerald-500/50', active);
                card.classList.toggle('border-zinc-700', !active);
                card.classList.toggle('is-active', active);
            });
        }

        function focusTaskArea(task) {
            if (!task || !task.page || typeof postToTemplate !== 'function') return;
            markActiveAssignedTask(task.id);
            postToTemplate({ type: 'focus-task-area', page: task.page, section: task.section || null });
            const pageLabel = TASK_PAGE_LABELS[task.page] || task.page;
            const viewOnly = !EDITOR_EDITABLE_PAGES.includes(task.page);
            toast('Opened ' + pageLabel + ' — ' + task.title + (viewOnly ? ' (view only here)' : ''));
        }

        function openAssignedTask(task, event) {
            // The concept is written on the dashboard, not in the template.
            if (task.is_concept) {
                if (task.url) window.location.href = task.url;
                return;
            }
            // Another module this student holds: open it there so it is editable.
            if (task.role !== @json($builderRole) && task.url) {
                const link = document.createElement('a');
                link.setAttribute('href', task.url);
                const fakeEvent = { currentTarget: link, preventDefault: function () {} };
                if (confirmLeaveBuilder(fakeEvent)) window.location.href = task.url;
                return;
            }
            focusTaskArea(task);
        }

        async function syncAssignedTasks() {
            if (!document.getElementById('assignedTaskList')) return;
            try {
                const res = await fetch(ASSIGNED_TASKS_URL, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                const tasks = Array.isArray(data.tasks) ? data.tasks : [];
                // Redraw only when something changed, so a hover or the active
                // mark is not reset every poll for nothing.
                const signature = JSON.stringify(tasks);
                if (signature === assignedTasksSignature) return;
                assignedTasksSignature = signature;
                renderAssignedTasks(tasks);
            } catch (e) { /* a missed poll is retried on the next one */ }
        }

        // First paint from the server's seed. Drawn now rather than on
        // DOMContentLoaded: the sidebar is already above this script, and the
        // ?focus handler below needs the list before the template reports ready.
        (function () {
            const seed = readAssignedTaskSeed();
            assignedTasksSignature = JSON.stringify(seed);
            renderAssignedTasks(seed);
        })();

        // ?focus=<task id> — arrived from another editor's sidebar. Focus once the
        // template reports it is ready, since navigation before then is dropped;
        // a timer covers a ready message that landed before this listener did.
        (function () {
            const focusId = Number(new URLSearchParams(window.location.search).get('focus'));
            if (!focusId) return;
            let done = false;
            const run = function (delay) {
                if (done) return;
                const task = assignedTasks.find(function (t) { return t.id === focusId; });
                if (!task) return;
                done = true;
                setTimeout(function () { focusTaskArea(task); }, delay);
            };
            window.addEventListener('message', function (event) {
                const data = event.data || {};
                if (data.source === 'hms-template' && data.type === 'editor-ready') run(300);
            });
            setTimeout(function () { run(0); }, 4000);
        })();

        /* These cost a database connection each time, and Supabase's pooler has a
           fixed number to hand out across everyone on the site — see render.yaml.
           A backgrounded tab polls nothing; returning to it syncs at once, so the
           numbers are still right the moment anybody looks. The task list changes
           only when faculty set work, so it polls the least often. */
        setInterval(function () { if (!document.hidden) syncGroupPresence(); }, 20000);
        setInterval(function () { if (!document.hidden) syncTemplateFromServer(); }, 15000);
        setInterval(function () { if (!document.hidden) syncAssignedTasks(); }, 30000);
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) return;
            syncGroupPresence();
            syncTemplateFromServer();
            syncAssignedTasks();
        });
        document.addEventListener('DOMContentLoaded', function () {
            syncGroupPresence();
            syncTemplateFromServer();
            if (!window.HMS_CAN_EDIT_TEMPLATE && typeof postToTemplate === 'function') {
                postToTemplate({ type: 'set-mode', mode: 'preview' });
                postToTemplate({ type: 'set-can-edit', canEdit: false });
            }
        });

        let toastTimer;
        function toast(msg) { const t = document.getElementById('toast'); t.textContent = msg; t.classList.add('show'); clearTimeout(toastTimer); toastTimer = setTimeout(()=>t.classList.remove('show'), 2000); }
    </script>
</body>
</html>