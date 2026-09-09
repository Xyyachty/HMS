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

    {{-- The Staff Tools grid on a department dashboard is content, so it
         follows. The editor tabs and the design panel beside them do not: they
         are the editing tools themselves, and tinting their inputs made the
         controls look like part of the hotel being edited. --}}
    html[data-ops-site-themed] .tool-card:hover { background: {{ $p['card'] }}; border-color: {{ $p['border'] }}; }
</style>
