{{-- The guest pages' fonts, styles and the team's own colours (reservation and My Stay). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
@if ($template === '2')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
@else
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
@endif
{{-- The site-wide fonts the Design Panel offers, so a team's pick shows here too. --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Lora:wght@400;600;700&family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@400;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  /* Each template's own ground, ink and accent, so the page reads as part of the site. */
  :root {
@if ($template === '2')
    --bg: #f7f4ef; --card: #ffffff; --border: #e2ddd5; --fg: #1a1a1a; --fg-muted: #6f6a64;
    --accent: #1b4332; --accent-soft: rgba(27, 67, 50, 0.08);
    --body-font: 'DM Sans', sans-serif; --display-font: 'Cormorant Garamond', serif;
    --ok: #1f7a4d; --ok-soft: rgba(31, 122, 77, 0.1); --bad: #b4233c; --bad-soft: rgba(180, 35, 60, 0.08);
    --warn: #a15c0b; --warn-soft: rgba(161, 92, 11, 0.1);
@else
    --bg: #0c0b09; --card: #181714; --border: #2a2621; --fg: #f5f0e8; --fg-muted: #a39c90;
    --accent: #c9a84c; --accent-soft: rgba(201, 168, 76, 0.12);
    --body-font: 'Outfit', sans-serif; --display-font: 'Playfair Display', serif;
    --ok: #6fcf97; --ok-soft: rgba(111, 207, 151, 0.12); --bad: #fb7185; --bad-soft: rgba(251, 113, 133, 0.12);
    --warn: #f2b45a; --warn-soft: rgba(242, 180, 90, 0.12);
@endif
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: var(--bg); color: var(--fg); font-family: var(--body-font); line-height: 1.55; overflow-wrap: anywhere; }
  .wrap { max-width: 960px; margin: 0 auto; padding: 0 16px; }
  header.top { border-bottom: 1px solid var(--border); }
  header.top .wrap { display: flex; align-items: center; justify-content: space-between; gap: 1rem; min-height: 64px; }
  .brand { display: inline-flex; align-items: center; gap: 0.6rem; min-width: 0; font-family: var(--display-font); font-size: 1.35rem; font-weight: 700; color: var(--fg); text-decoration: none; }
  .brand-logo { width: 34px; height: 34px; object-fit: contain; flex: none; }
  .back { color: var(--fg-muted); font-size: 0.85rem; text-decoration: none; white-space: nowrap; }
  .back:hover { color: var(--accent); }
  main.wrap { padding-top: 2.25rem; padding-bottom: 4rem; }
  .eyebrow { color: var(--accent); font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; font-weight: 600; }
  h1, h2 { font-family: var(--display-font); font-weight: 700; line-height: 1.15; }
  h1 { font-size: clamp(1.7rem, 4vw, 2.3rem); margin-top: 0.35rem; }
  h2 { font-size: 1.45rem; }
  .muted { color: var(--fg-muted); }
  .section { margin-top: 2.25rem; }
  .section-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .panel { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 1.4rem; }

  .details { display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.25rem; }
  .table-hero { display: flex; flex-direction: column; justify-content: center; gap: 0.35rem; background: var(--accent-soft); border-radius: 12px; padding: 1.25rem; }
  .table-hero b { font-family: var(--display-font); font-size: clamp(2rem, 6vw, 2.8rem); line-height: 1; color: var(--accent); }
  dl.facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem 1.25rem; }
  dl.facts dt { font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted); }
  dl.facts dd { font-weight: 500; margin-top: 0.15rem; }

  .badge { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 999px; padding: 0.3rem 0.75rem; font-size: 0.78rem; font-weight: 600; }
  .badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .s-confirmed { color: var(--accent); background: var(--accent-soft); }
  .s-seated { color: var(--ok); background: var(--ok-soft); }
  .s-completed, .s-closed { color: var(--fg-muted); background: var(--accent-soft); }
  .s-cancelled { color: var(--bad); background: var(--bad-soft); }
  .s-no-show { color: var(--warn); background: var(--warn-soft); }
  .s-seated::before { animation: pulse 2s ease-in-out infinite; }
  @keyframes pulse { 50% { opacity: 0.35; } }

  .status-row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
  .status-note { margin-top: 0.75rem; color: var(--fg-muted); font-size: 0.92rem; max-width: 60ch; }
  ol.steps { list-style: none; display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 1.25rem; counter-reset: s; }
  ol.steps li { position: relative; padding-top: 1.1rem; font-size: 0.82rem; color: var(--fg-muted); }
  ol.steps li::before { content: ''; position: absolute; top: 0; left: 0; right: 4px; height: 4px; border-radius: 4px; background: var(--border); }
  ol.steps li.done { color: var(--fg); }
  ol.steps li.done::before { background: var(--accent); }
  .updated { font-size: 0.75rem; color: var(--fg-muted); }

  .tabs { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .tab { border: 1px solid var(--border); background: transparent; color: var(--fg-muted); border-radius: 999px; padding: 0.4rem 0.9rem; font: inherit; font-size: 0.82rem; cursor: pointer; }
  .tab[aria-pressed="true"] { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
  .tab:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .menu { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
  .dish { display: grid; grid-template-columns: 84px minmax(0, 1fr); gap: 0.9rem; align-items: start; background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 0.75rem; }
  .dish img, .dish .noimg { width: 84px; height: 84px; border-radius: 9px; object-fit: cover; background: var(--accent-soft); display: flex; align-items: center; justify-content: center; color: var(--accent); }
  .dish-top { display: flex; justify-content: space-between; gap: 0.75rem; align-items: baseline; }
  .dish-name { font-weight: 600; }
  .dish-price { color: var(--accent); font-weight: 600; white-space: nowrap; font-variant-numeric: tabular-nums; }
  .dish-sub { color: var(--fg-muted); font-size: 0.84rem; margin-top: 0.2rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .dish.is-out { opacity: 0.6; }
  .dish .badge { margin-top: 0.45rem; padding: 0.15rem 0.55rem; font-size: 0.7rem; }

  .skeleton { background: linear-gradient(90deg, var(--card), var(--accent-soft), var(--card)); background-size: 200% 100%; animation: shimmer 1.4s linear infinite; border-radius: 14px; }
  @keyframes shimmer { to { background-position: -200% 0; } }
  .empty { text-align: center; padding: 3rem 1.25rem; }
  .empty i { font-size: 2rem; color: var(--accent); opacity: 0.6; margin-bottom: 0.9rem; }
  .empty p { max-width: 46ch; margin: 0.5rem auto 0; color: var(--fg-muted); }
  .btn { display: inline-flex; align-items: center; gap: 0.45rem; margin-top: 1.25rem; padding: 0.65rem 1.2rem; border-radius: 10px; background: var(--accent); color: var(--bg); text-decoration: none; font-weight: 600; font-size: 0.88rem; }
  .btn:active { transform: translateY(1px); }

  @media (max-width: 720px) {
    .details, .menu { grid-template-columns: 1fr; }
    main.wrap { padding-top: 1.5rem; }
  }
  @media (prefers-reduced-motion: reduce) { .s-seated::before, .skeleton { animation: none; } }
</style>
<script>
/* The team's own design on top of the template's defaults above: the site and header
   colours, the menu card colour and the site-wide type, as the builder saved them.
   A chosen background becomes a whole palette (card, border, text, a readable accent)
   by the same rules as the templates' sitePalette() / cardPalette() / siteTypeCss(),
   so the page matches the site. Anything that does not parse is left at the default. */
(function () {
  var t = @json($theme);

  function rgbOf(v) {
    v = String(v || '').trim();
    var m = /^#([0-9a-f]{3})$/i.exec(v);
    if (m) v = '#' + m[1].split('').map(function (c) { return c + c; }).join('');
    m = /^#([0-9a-f]{6})([0-9a-f]{2})?$/i.exec(v);
    if (m) return { r: parseInt(m[1].slice(0, 2), 16), g: parseInt(m[1].slice(2, 4), 16), b: parseInt(m[1].slice(4, 6), 16) };
    m = /^rgba?\(([^)]+)\)$/i.exec(v);
    if (m) {
      var p = m[1].split(',').map(parseFloat);
      if (p.length >= 3 && p.slice(0, 3).every(function (x) { return !isNaN(x); })) return { r: p[0], g: p[1], b: p[2] };
    }
    return null;
  }
  function lum(c) {
    var f = function (x) { x /= 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); };
    return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b);
  }
  function mix(a, b, k) {
    var c = function (x, y) { return Math.round(x + (y - x) * k); };
    return { r: c(a.r, b.r), g: c(a.g, b.g), b: c(a.b, b.b) };
  }
  function css(c, alpha) {
    return alpha == null ? 'rgb(' + c.r + ',' + c.g + ',' + c.b + ')' : 'rgba(' + c.r + ',' + c.g + ',' + c.b + ',' + alpha + ')';
  }

  var siteAccent = rgbOf(getComputedStyle(document.documentElement).getPropertyValue('--accent'));

  // The site's accent, darkened or lightened toward the text until it reads at 3:1.
  function readableAccent(bgLum, fg) {
    if (!siteAccent) return null;
    for (var k = 0; k <= 0.91; k += 0.13) {
      var c = mix(siteAccent, fg, k);
      var l = lum(c);
      if ((Math.max(l, bgLum) + 0.05) / (Math.min(l, bgLum) + 0.05) >= 3) return c;
    }
    return null;
  }

  function palette(bg) {
    var rgb = rgbOf(bg);
    if (!rgb) return null;
    var L = lum(rgb);
    var fg = L > 0.4 ? { r: 20, g: 17, b: 12 } : { r: 245, g: 240, b: 232 };
    var accent = readableAccent(L, fg) || fg;
    // The status colours follow the ground too: the light template's on a pale
    // background, the dark template's on a dark one.
    var status = L > 0.4
      ? { '--ok': '#1f7a4d', '--ok-soft': 'rgba(31,122,77,0.1)', '--bad': '#b4233c', '--bad-soft': 'rgba(180,35,60,0.08)', '--warn': '#a15c0b', '--warn-soft': 'rgba(161,92,11,0.1)' }
      : { '--ok': '#6fcf97', '--ok-soft': 'rgba(111,207,151,0.12)', '--bad': '#fb7185', '--bad-soft': 'rgba(251,113,133,0.12)', '--warn': '#f2b45a', '--warn-soft': 'rgba(242,180,90,0.12)' };
    var vars = {
      '--bg': css(rgb), '--card': css(mix(rgb, fg, 0.07)), '--border': css(mix(rgb, fg, 0.18)),
      '--fg': css(fg), '--fg-muted': css(fg, 0.62), '--accent': css(accent), '--accent-soft': css(accent, 0.12),
    };
    for (var k in status) vars[k] = status[k];
    return vars;
  }

  function rule(selector, vars, extra) {
    var out = selector + '{';
    for (var k in vars) out += k + ':' + vars[k] + ';';
    return out + (extra || '') + '}';
  }

  // Free text from the builder goes into a stylesheet: keep it to one declaration.
  function clean(v) { return String(v || '').replace(/[{}<>;\\]/g, '').trim(); }

  var out = '';
  var p = palette(t.site);
  if (p) out += rule(':root', p);
  p = palette(t.header);
  if (p) out += rule('header.top', p, 'background:var(--bg);color:var(--fg);');
  p = palette(t.menuCard);
  if (p) { p['--card'] = p['--bg']; out += rule('.dish', p, 'color:var(--fg);'); }

  var family = clean(t.family), size = clean(t.size), color = clean(t.color), heading = clean(t.headingColor);
  var body = (family ? 'font-family:' + family + ';' : '') + (size ? 'font-size:' + size + ';' : '') + (color ? 'color:' + color + ';' : '');
  if (body) out += 'body{' + body + '}';
  if (family) out += 'h1,h2,h3,.brand,.table-hero b,button{font-family:' + family + ';}';
  if (heading) out += 'h1,h2,h3{color:' + heading + ';}';

  if (out) {
    var style = document.createElement('style');
    style.textContent = out;
    document.head.appendChild(style);
  }
})();
</script>
