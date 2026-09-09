{{-- Repaints a staff shell in the background colour the team picked for their
     hotel site. $p is the palette App\Support\SitePalette derives — the same
     steps the landing page derives in JavaScript, so both sides land on one
     colour scheme instead of the site following and the desks staying black.

     Included last in <head> (after each page's own styles) and scoped to the
     [data-ops-site-themed] attribute on <html>, which keeps every rule here one
     step more specific than the stock rule and the Template 2 theme it replaces,
     and more specific than the :root palettes the pages declare. Selectors that
     only exist in one of the two shells are harmless in the other.

     The top bar and the left sidebar are deliberately left out: they are the
     system's own chrome, the same on every team's screen, and recolouring them
     made the hotel's colour read as the application's. Only the working area
     below and beside them follows the hotel. --}}
<style>
    html[data-ops-site-themed] {
        --bg: {{ $p['bg'] }};
        --bg-warm: {{ $p['warm'] }};
        --bg-alt: {{ $p['warm'] }};
        --card: {{ $p['card'] }};
        --border: {{ $p['border'] }};
        --fg: {{ $p['fg'] }};
        --fg-muted: {{ $p['muted'] }};
        --accent: {{ $p['accent'] }};
    }
    html[data-ops-site-themed] body { background: {{ $p['bg'] }}; color: {{ $p['fg'] }}; }
    html[data-ops-site-themed] .content-bg,
    html[data-ops-site-themed] .canvas-bg {
        background: {{ $p['bg'] }};
        background-image: radial-gradient({{ $p['border'] }} 1px, transparent 1px);
    }
    html[data-ops-site-themed] .status-bar {
        background: {{ $p['warm'] }};
        border-top: 1px solid {{ $p['border'] }};
        color: {{ $p['muted'] }};
    }
    html[data-ops-site-themed] ::-webkit-scrollbar-track { background: {{ $p['warm'] }}; }
    html[data-ops-site-themed] ::-webkit-scrollbar-thumb { background: {{ $p['border'] }}; }

    {{-- Department shell only: the tool grid, the editor tabs and the design
         panel down the right-hand side. --}}
    html[data-ops-site-themed] .tool-card:hover { background: {{ $p['card'] }}; border-color: {{ $p['border'] }}; }
    html[data-ops-site-themed] #editorModeTabs { background: {{ $p['warm'] }} !important; border-color: {{ $p['border'] }} !important; }
    html[data-ops-site-themed] .mode-tab { color: {{ $p['muted'] }} !important; }
    html[data-ops-site-themed] .mode-tab.active-tab {
        background: {{ $p['card'] }} !important;
        border: 1px solid {{ $p['border'] }} !important;
        color: {{ $p['fg'] }} !important;
    }
    html[data-ops-site-themed] .settings-card { background: {{ $p['card'] }}; border-color: {{ $p['border'] }}; }
    html[data-ops-site-themed] .settings-label { color: {{ $p['muted'] }}; }
    html[data-ops-site-themed] .settings-input,
    html[data-ops-site-themed] .settings-select,
    html[data-ops-site-themed] .style-input {
        background: {{ $p['card'] }}; border-color: {{ $p['border'] }}; color: {{ $p['fg'] }};
    }
    html[data-ops-site-themed] .settings-input:focus,
    html[data-ops-site-themed] .style-input:focus { border-color: {{ $p['accent'] }}; }
    html[data-ops-site-themed] .style-input option { background: {{ $p['card'] }}; color: {{ $p['fg'] }}; }
    html[data-ops-site-themed] #hbLayoutList > div,
    html[data-ops-site-themed] #hbVersionList > div {
        background: {{ $p['card'] }} !important; border-color: {{ $p['border'] }} !important;
    }
    html[data-ops-site-themed] #hbLayoutList .text-zinc-300,
    html[data-ops-site-themed] #hbVersionList .text-zinc-200 { color: {{ $p['fg'] }} !important; }
    html[data-ops-site-themed] #hbLayoutList p.text-zinc-600,
    html[data-ops-site-themed] #hbVersionList p.text-zinc-600 { color: {{ $p['muted'] }} !important; }
    html[data-ops-site-themed] #hbLayoutList button.text-zinc-500,
    html[data-ops-site-themed] #hbVersionList button.text-zinc-500 { color: {{ $p['muted'] }} !important; }
</style>
