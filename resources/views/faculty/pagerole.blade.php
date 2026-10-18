@extends('faculty.layout.app')

@section('page_title', 'List of Teams Information')
@section('role_active', 'active')

@section('content')

<style>
    /* Save buttons turn green on hover. Literal hex: faculty-palette.css remaps the emerald/green utilities to gold. */
    .hover-green:hover { background: #16a34a !important; box-shadow: 0 10px 15px -3px rgba(22, 163, 74, .3) !important; }
    /* ── Responsive shim ──
       The frozen build ships no breakpoint utilities, so the sm:/lg: classes
       this page has always used resolved to nothing. Emitted here for the set
       the markup actually references. */
    @media (min-width: 640px) {
        .sm\:flex-row { flex-direction: row; }
        .sm\:flex-none { flex: none; }
        .sm\:items-center { align-items: center; }
        .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sm\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .sm\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
        .sm\:min-w-\[7rem\] { min-width: 7rem; }
        .sm\:ml-auto { margin-left: auto; }
        .sm\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .sm\:text-\[30px\], .sm\:text-\[1\.875rem\] { font-size: 1.875rem; line-height: 1.15; }
        .sm\:w-40 { width: 10rem; }
        .sm\:w-44 { width: 11rem; }
        .sm\:w-auto { width: auto; }
    }
    @media (min-width: 1024px) {
        .lg\:flex-row { flex-direction: row; }
        .lg\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .lg\:col-span-2 { grid-column: span 2 / span 2; }
        .lg\:border-r { border-right-width: 1px; }
        .lg\:border-b-0 { border-bottom-width: 0; }
        .lg\:w-80 { width: 20rem; }
    }

    .manage-head-tools { display: flex; align-items: center; gap: .75rem; margin-top: .5rem; }
    .manage-grid { grid-template-columns: 280px minmax(0, 1fr); }
    .mm-team-panel { min-width: 0; }
    .add-student-modal { max-width: 62rem; max-height: 92vh; }
    .add-student-body {
        display: grid; gap: 1.5rem;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        overflow-y: auto;
    }
    @media (max-width: 1279px) {
        .manage-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 900px) {
        .add-student-body { grid-template-columns: minmax(0, 1fr); }
        .manage-head-tools { flex-wrap: wrap; }
    }

    /* ── Manage Teams + Team Setup layout ──
       public/css/app.css is a frozen Tailwind build with no responsive
       utilities in it at all — every sm:/md:/lg:/xl: class resolves to nothing
       on the faculty pages. The layout below is written out here so it does not
       depend on that build, and the handful of spacing and size utilities the
       markup needs are shimmed at the bottom. */
    .tm-stat-grid {
        display: grid; gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .tm-toolbar {
        display: flex; align-items: flex-start; gap: .75rem; flex-wrap: nowrap;
    }
    .tm-toolbar .tm-search { flex: 1 1 auto; min-width: 0; }
    .tm-toolbar .tm-select { width: 11rem; flex: 0 0 auto; }

    /* Four cards a row, lined up under the four stat tiles; steps down to
       three, two, then one as the screen narrows (breakpoints below). */
    .tm-card-grid {
        display: grid; gap: 1rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: stretch;
    }
    .tm-card-empty { grid-column: 1 / -1; }

    /* ── Team card: compact, every card the same bands ──
       Name and headcount, approval status, approval percentage, then the two
       buttons pinned to the bottom. Everything else about a team is in View
       Team's Overview. Written out because app.css is a frozen build. */
    .tc {
        height: 100%; display: flex; flex-direction: column; gap: 1rem;
        padding: 1.25rem; border-radius: 1.1rem; background: #fff;
        border: 1px solid #E4E2E0; box-shadow: 0 1px 2px rgba(24, 24, 24, .04);
        transition: transform .3s cubic-bezier(.16, 1, .3, 1), box-shadow .3s cubic-bezier(.16, 1, .3, 1), border-color .2s;
        animation: tc-in .45s cubic-bezier(.16, 1, .3, 1) both;
        animation-delay: calc(var(--i, 0) * 45ms);
    }
    .tc:hover { transform: translateY(-2px); border-color: #D6D3D1; box-shadow: 0 14px 28px -18px rgba(74, 70, 67, .45); }
    @keyframes tc-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .tc { animation: none; transition: none; } .tc:hover { transform: none; } }

    .tc-head { display: flex; align-items: center; gap: .65rem; min-width: 0; }
    .tc-mark {
        width: 2.25rem; height: 2.25rem; flex-shrink: 0; border-radius: .75rem;
        display: inline-flex; align-items: center; justify-content: center;
        background: #F3F2F1; color: #4A4643; font-size: 1.15rem;
    }
    .tc-name { flex: 1; min-width: 0; font-size: 1rem; font-weight: 800; color: #181818; line-height: 1.25; letter-spacing: -.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tc-members {
        flex-shrink: 0; display: inline-flex; align-items: center; gap: .3rem;
        padding: .2rem .55rem; border-radius: 999px; border: 1px solid #E4E2E0; background: #fff;
        font-size: 0.75rem; font-weight: 800; color: #4A4643; font-variant-numeric: tabular-nums;
    }
    .tc-members.is-full { background: #4A4643; border-color: #4A4643; color: #fff; }

    .tc-status-row { display: flex; align-items: center; gap: .5rem; min-height: 1.6rem; }
    .tc-status {
        display: inline-flex; align-items: center; gap: .4rem; padding: .22rem .65rem; border-radius: 999px;
        font-size: 0.7188rem; font-weight: 800; letter-spacing: .01em; border: 1px solid;
    }
    .tc-status-dot { width: .45rem; height: .45rem; border-radius: 999px; background: currentColor; }
    .tc-status.is-progress .tc-status-dot { animation: tc-breathe 2.4s ease-in-out infinite; }
    @keyframes tc-breathe { 50% { opacity: .35; } }
    @media (prefers-reduced-motion: reduce) { .tc-status.is-progress .tc-status-dot { animation: none; } }

    .tc-meter-top, .ov-meter-top { display: flex; align-items: baseline; justify-content: space-between; gap: .5rem; }
    .tc-meter-top span, .ov-meter-top span { font-size: 0.75rem; font-weight: 700; color: #5F5A55; }
    .tc-meter-top b { font-size: 1.625rem; font-weight: 800; color: #181818; line-height: 1; font-variant-numeric: tabular-nums; }
    .ov-meter-top b { font-size: 0.875rem; font-weight: 800; color: #181818; font-variant-numeric: tabular-nums; }
    .tc-bar { height: 0.5rem; margin-top: .5rem; border-radius: 999px; background: #F3F2F1; overflow: hidden; }
    .tc-bar > span { display: block; height: 100%; border-radius: 999px; background: #4A4643; transition: width .6s cubic-bezier(.16, 1, .3, 1); }
    .tc-meter-sub { margin-top: .4rem; font-size: 0.7188rem; color: #8A817A; }

    .tc-actions { margin-top: auto; display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .tc-btn {
        position: relative; height: 2.5rem; border-radius: .75rem; border: 1px solid #E4E2E0; background: #fff;
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
        font-size: 0.8125rem; font-weight: 700; color: #181818;
        transition: background-color .15s, border-color .15s, transform .15s;
    }
    .tc-btn:hover { background: #F3F2F1; border-color: #D6D3D1; }
    .tc-btn:active { transform: scale(.98); }
    .tc-btn-primary { background: #4A4643; border-color: #4A4643; color: #fff; }
    .tc-btn-primary:hover { background: #2F2C2A; border-color: #2F2C2A; }
    .tc-btn .iconify { font-size: 1rem; }
    /* Submissions waiting: the review notices moved into View Team, so the card
       keeps only this dot. Driven by the notices' own hidden class, which the
       live poll already toggles. */
    .tc-alert-dot {
        display: none; position: absolute; top: -.3rem; right: -.3rem; width: .75rem; height: .75rem;
        border-radius: 999px; background: #D97706; box-shadow: 0 0 0 2px #fff;
    }
    .tc:has([data-pending-review-badge]:not(.hidden), [data-concept-pending-badge]:not(.hidden)) .tc-alert-dot { display: block; }

    /* ── View Team › Overview ── */
    .ov-attn { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1rem; }
    .ov-notice {
        display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
        padding: .75rem .9rem; border-radius: .9rem; border: 1px solid #FDE68A; background: #FFFBEB;
    }
    .ov-notice-icon {
        width: 2.1rem; height: 2.1rem; flex-shrink: 0; border-radius: .65rem;
        display: inline-flex; align-items: center; justify-content: center; background: #fff; color: #B45309; font-size: 1.1rem;
    }
    .ov-notice-title { font-size: 0.8125rem; font-weight: 800; color: #181818; }
    .ov-notice-text { font-size: 0.75rem; color: #5F5A55; margin-top: .1rem; line-height: 1.45; }
    .ov-clear { display: none; align-items: center; gap: .4rem; font-size: 0.8125rem; font-weight: 700; color: #15803D; }
    .ov-attn:not(:has(.ov-notice:not(.hidden))) .ov-clear { display: flex; }
    .ov-grid { display: grid; gap: .75rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .ov-tile { display: flex; flex-direction: column; padding: .95rem 1rem; border-radius: 1rem; border: 1px solid #E4E2E0; background: #fff; min-width: 0; }
    .ov-label { display: flex; align-items: center; gap: .35rem; font-size: 0.75rem; font-weight: 800; color: #5F5A55; margin-bottom: .45rem; }
    .ov-label .iconify { color: #4A4643; font-size: 1rem; }
    .ov-value { font-size: 0.9375rem; font-weight: 800; color: #181818; line-height: 1.3; overflow-wrap: anywhere; }
    .ov-tag {
        align-self: flex-start; margin-top: .35rem; padding: .12rem .55rem; border-radius: 999px;
        font-size: 0.6875rem; font-weight: 800; background: #F3F2F1; color: #4A4643; border: 1px solid #E4E2E0;
    }
    .ov-tag.is-good { background: #F0FDF4; color: #15803D; border-color: #BBF7D0; }
    .ov-text { font-size: 0.7812rem; color: #5F5A55; line-height: 1.5; margin-top: .4rem; }
    .ov-link {
        margin-top: auto; padding-top: .75rem; align-self: flex-start; display: inline-flex; align-items: center; gap: .3rem;
        font-size: 0.7812rem; font-weight: 800; color: #181818; background: none; border: 0; cursor: pointer;
    }
    .ov-link:hover { text-decoration: underline; text-underline-offset: 3px; }
    @media (max-width: 1100px) { .ov-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .ov-grid { grid-template-columns: minmax(0, 1fr); } }

    /* Team Setup modal */
    .setup-modal { max-width: 72rem; max-height: 92vh; }
    /* Team Setup & Members and Set New Task are white throughout: fields, the
       One Team / Multiple Teams switch, the member and task lists, headers,
       notices and tiles drop their soft fill and keep only their border. An id
       outranks the .gray-accents fills. Red stays for errors, and the gray step
       circles keep their fill so they still read as circles. Avatar and icon
       tiles get a ring, having no border of their own. */
    :is(#panel-team_setup, #panel-create_task) :is(.bg-slate-50, .bg-slate-50\/50, .bg-slate-50\/60, .bg-slate-100, .bg-amber-50, .bg-emerald-50, .bg-blue-50, .bg-brand-soft, .bg-brand-soft\/40, .bg-brand-soft\/50) { background-color: #fff; }
    :is(#panel-team_setup, #panel-create_task) :is(.bg-brand-soft, .rounded-full.bg-slate-100) { box-shadow: inset 0 0 0 1px #E4E2E0; }
    /* Front Desk's rose icon reads maroon; it goes gray like the other roles. */
    #panel-create_task :is(.iconify, svg).text-rose-500 { color: #4A4643; }
    /* A picked team reads neutral dark gray, not the warm brown-gray accent. */
    #panel-create_task .task-team-btn:has(:checked) { border-color: #374151; background-color: #F3F4F6; }
    /* A ticked task card (setTaskCardSelected adds .border-brand) fills neutral dark gray (no blue tint)
       with white text, so the picked tasks stand out from the white ones. */
    #panel-create_task .task-card.border-brand { border-color: #404040; background-color: #404040; box-shadow: none; }
    #panel-create_task .task-card.border-brand :is(p, button, button .iconify) { color: #fff; }
    #panel-create_task .task-card.border-brand p.text-slate-400 { color: #D4D4D4; }
    /* Checkboxes are native (text-brand isn't in the frozen build), so they tick
       browser blue; dark gray here, white on a filled task card. */
    #panel-create_task input[type="checkbox"] { accent-color: #374151; }
    #panel-create_task .task-card.border-brand input[type="checkbox"] { accent-color: #fff; }

    /* Teams & Students, in plain words and neutral grays. Every rule is scoped
       to the panel id so it outranks .gray-accents and .ink-all, which would
       otherwise tint these warm or force all of their text black. */
    #panel-team_setup .ts-tabs,
    #panel-team_setup .ts-modes { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    #panel-team_setup .ts-modes { margin-top: .75rem; }
    #panel-team_setup :is(.ts-tab, .ts-mode) {
        display: flex; align-items: center; gap: .8rem; width: 100%; text-align: left;
        padding: .85rem 1rem; border-radius: 1rem; border: 1.5px solid #E5E7EB; background: #fff;
        transition: border-color .25s cubic-bezier(.16, 1, .3, 1), background-color .25s cubic-bezier(.16, 1, .3, 1);
    }
    #panel-team_setup :is(.ts-tab, .ts-mode):hover { border-color: #9CA3AF; }
    #panel-team_setup :is(.ts-tab, .ts-mode):active { transform: translateY(1px); }
    #panel-team_setup :is(.ts-tab.border-brand, .ts-mode.shadow-sm) { border-color: #374151; background: #F9FAFB; box-shadow: 0 8px 20px -14px rgba(17, 24, 39, .35); }
    #panel-team_setup .ts-tab-icon {
        flex-shrink: 0; width: 2.5rem; height: 2.5rem; border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        background: #F3F4F6; color: #374151; font-size: 1.25rem;
    }
    #panel-team_setup .ts-tab.border-brand .ts-tab-icon { background: #374151; color: #fff; }
    #panel-team_setup .ts-mode-icon { flex-shrink: 0; font-size: 1.35rem; color: #6B7280; }
    #panel-team_setup .ts-mode.shadow-sm .ts-mode-icon { color: #111827; }
    #panel-team_setup :is(.ts-tab-copy, .ts-mode-copy) { min-width: 0; display: flex; flex-direction: column; }
    #panel-team_setup :is(.ts-tab-title, .ts-mode-title) { font-size: 0.9062rem; font-weight: 800; color: #111827; }
    #panel-team_setup :is(.ts-tab-sub, .ts-mode-sub) { font-size: 0.7812rem; font-weight: 500; color: #6B7280; line-height: 1.4; margin-top: .1rem; }
    #panel-team_setup .ts-tab:not(.border-brand) .ts-tab-title { color: #4B5563; }

    #panel-team_setup .ts-lead { font-size: 0.875rem; color: #374151; line-height: 1.55; }
    #panel-team_setup .ts-lead b { color: #111827; }
    #panel-team_setup .ts-link { background: none; border: 0; padding: 0; font-weight: 700; color: #111827; text-decoration: underline; text-underline-offset: 2px; cursor: pointer; }
    #panel-team_setup .ts-label { display: block; font-size: 0.8438rem; font-weight: 800; color: #111827; margin-bottom: .35rem; }
    #panel-team_setup .ts-help { font-size: 0.7812rem; color: #6B7280; margin-top: -.15rem; }

    #panel-team_setup .ts-done {
        border: 1.5px dashed #D1D5DB; border-radius: 1rem; background: #fff;
        padding: 2.5rem 1.25rem; text-align: center;
        display: flex; flex-direction: column; align-items: center;
    }
    #panel-team_setup .ts-done-icon {
        width: 3rem; height: 3rem; border-radius: 9999px; margin-bottom: .75rem;
        display: flex; align-items: center; justify-content: center;
        background: #DCFCE7; color: #15803D; font-size: 1.5rem;
    }
    #panel-team_setup .ts-done-title { font-size: 1rem; font-weight: 800; color: #111827; }
    #panel-team_setup .ts-done-sub { font-size: 0.8125rem; color: #6B7280; max-width: 34rem; margin: .35rem 0 1.1rem; line-height: 1.55; }
    #panel-team_setup .ts-btn-dark {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .65rem 1.1rem; border-radius: .75rem; border: 0; cursor: pointer;
        background: #374151; color: #fff; font-size: 0.8125rem; font-weight: 700;
        transition: background-color .2s ease;
    }
    #panel-team_setup .ts-btn-dark:hover { background: #1F2937; }
    #panel-team_setup .ts-btn-dark:active { transform: translateY(1px); }

    #panel-team_setup .ts-glance { border: 1px solid #E5E7EB; border-radius: 1rem; background: #fff; padding: 1rem; }
    #panel-team_setup .ts-glance-title { font-size: 0.875rem; font-weight: 800; color: #111827; margin-bottom: .35rem; }
    #panel-team_setup .ts-glance-row {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .55rem 0; border-top: 1px solid #F3F4F6; font-size: 0.8125rem; color: #4B5563;
    }
    #panel-team_setup .ts-glance-row:first-of-type { border-top: 0; }
    #panel-team_setup .ts-glance-row b { font-size: 0.9375rem; font-weight: 800; color: #111827; }
    #panel-team_setup .ts-tip {
        display: flex; align-items: flex-start; gap: .6rem;
        border: 1px solid #E5E7EB; border-radius: 1rem; background: #F9FAFB; padding: .85rem;
        font-size: 0.7812rem; color: #4B5563; line-height: 1.5;
    }
    #panel-team_setup .ts-tip .iconify { flex-shrink: 0; font-size: 1.1rem; color: #6B7280; margin-top: .05rem; }
    #panel-team_setup .ts-tip b { color: #111827; }

    #panel-team_setup .ts-count {
        display: inline-flex; align-items: center; padding: .35rem .75rem; border-radius: .6rem;
        background: #F3F4F6; color: #374151; font-size: 0.75rem; font-weight: 700; white-space: nowrap;
    }
    #panel-team_setup .ts-full-note {
        display: inline-flex; align-items: center; gap: .35rem; flex-shrink: 0;
        font-size: 0.75rem; font-weight: 600; color: #6B7280;
    }
    #panel-team_setup .ts-footer-note { margin-right: auto; font-size: 0.75rem; color: #6B7280; }
    /* Change existing teams hides the save button; the empty bar goes with it. */
    #panel-team_setup .ts-footer:not(:has(#createModalSubmitBtn:not(.hidden))) { display: none; }

    /* Update Team and Add Student dialogs: neutral grays, none of the warm
       wine/gold tint the palette and .gray-accents give their fills, borders,
       icons and the On this team badge. Ids outrank both. */
    #updateTeamModal :is(.bg-slate-50, .bg-slate-100, .bg-amber-50, .bg-blue-50, .bg-brand-soft, .bg-red-50) { background-color: #F5F5F5; }
    #updateTeamModal :is(.hover\:bg-slate-50, .hover\:bg-slate-200, .hover\:bg-brand-soft\/50):hover { background-color: #EEEEEE; }
    #updateTeamModal :is(.border-slate-100, .border-slate-200, .border-slate-300, .border-amber-100, .border-amber-200, .border-blue-100, .border-red-200) { border-color: #E5E5E5; }
    #updateTeamModal .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: #E5E5E5; }
    #updateTeamModal .hover\:border-brand\/40:hover { border-color: #9CA3AF; }
    #updateTeamModal :is(input, select, textarea):focus { border-color: #9CA3AF; --tw-ring-color: rgba(156, 163, 175, .3); }
    #updateTeamModal label:has(:checked):is(.has-\[\:checked\]\:border-brand) { border-color: #374151; background-color: #F3F4F6; }
    #updateTeamModal :is(.brand-gradient, .bg-amber-500) { background: #374151; }
    #updateTeamModal :is(.brand-gradient, .bg-amber-500):hover { background: #1F2937; }
    #updateTeamModal :is(.shadow-brand\/20, .shadow-amber-400\/30) { --tw-shadow-color: rgba(17, 17, 17, .15); }
    #updateTeamModal .bg-slate-900\/60 { background-color: rgba(17, 17, 17, .6); }
    #updateTeamModal :is(.iconify, svg):not(:is(.brand-gradient, .bg-amber-500, .text-white) *) { color: #4B5563; }
    #updateTeamModal :is(.text-amber-500, .text-amber-600, .text-amber-700, .text-brand, .text-slate-700, .text-slate-800, .text-slate-900, .text-red-700) { color: #111827; }
    #updateTeamModal :is(.text-slate-500, .text-slate-600) { color: #4B5563; }
    #updateTeamModal :is(.text-slate-300, .text-slate-400, .text-red-400) { color: #9CA3AF; }
    #updateTeamModal .update-on-team-badge { background: #F3F4F6; border-color: #D1D5DB; color: #374151; }
    /* Add Students: unassigned students of this block, up to the 4-member cap. */
    #updateTeamModal .ut-add { border: 1px solid #E5E5E5; border-radius: 1rem; background: #fff; padding: 1rem; }
    #updateTeamModal .ut-add-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .5rem .75rem; }
    #updateTeamModal .ut-add-title { display: flex; align-items: center; gap: .4rem; font-size: 0.75rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #374151; }
    #updateTeamModal .ut-add-sub { font-size: 0.7188rem; color: #6B7280; margin-top: .2rem; line-height: 1.45; }
    #updateTeamModal .ut-cap { font-size: 0.7188rem; font-weight: 800; padding: .3rem .75rem; border-radius: 999px; background: #F3F4F6; color: #374151; white-space: nowrap; }
    #updateTeamModal .ut-cap.is-full { background: #111827; color: #fff; }
    #updateTeamModal .ut-bar { height: 0.375rem; margin-top: .75rem; border-radius: 999px; background: #F3F4F6; overflow: hidden; }
    #updateTeamModal .ut-bar > span { display: block; height: 100%; width: 0; border-radius: 999px; background: #374151; transition: width .25s ease; }
    #updateTeamModal .ut-full { display: flex; align-items: center; gap: .5rem; margin-top: .75rem; padding: .7rem .85rem; border-radius: .75rem; border: 1px solid #D1D5DB; background: #F9FAFB; font-size: 0.7812rem; font-weight: 700; color: #111827; }
    #updateTeamModal .ut-add-list { margin-top: .75rem; display: flex; flex-direction: column; gap: .5rem; max-height: 18rem; overflow-y: auto; }
    #updateTeamModal .ut-add-row.is-locked { opacity: .5; }
    #updateTeamModal .ut-add-row.is-locked input { cursor: not-allowed; }
    /* A candidate's roles only matter once they are ticked to join. */
    #updateTeamModal .ut-add-row:not(:has(.update-student-checkbox:checked)) .update-role-row { display: none; }
    #updateTeamModal .ut-empty { margin-top: .75rem; padding: 1.25rem; text-align: center; font-size: 0.75rem; color: #9CA3AF; border: 1px dashed #E5E5E5; border-radius: .75rem; }
    #updateTeamModal :is(.ut-full, .ut-empty).hidden { display: none; }
    #updateTeamModal .update-new-badge { background: #111827; border-color: #111827; color: #fff; }

    @media (max-width: 640px) {
        #panel-team_setup .ts-tabs,
        #panel-team_setup .ts-modes { grid-template-columns: minmax(0, 1fr); }
        #panel-team_setup .ts-footer-note { display: none; }
    }
    .setup-grid {
        display: grid; gap: .75rem;
        grid-template-columns: minmax(0, 1fr) 300px;
        align-items: start;
    }
    /* Team setup footer: Create is green. */
    #createModalSubmitBtn { background-color: #15803D; box-shadow: 0 4px 6px -1px rgba(21, 128, 61, .25); }
    .setup-field-grid { display: grid; gap: 1rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .setup-role-grid { display: grid; gap: .625rem; grid-template-columns: repeat(5, minmax(0, 1fr)); }
    /* Steps 1 and 2 side by side on wide screens, roles on the right. Each card
       is half width there, so its own grid drops to two columns. */
    @media (min-width: 1024px) {
        .setup-step-pair { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: stretch; }
        .setup-step-pair > * { margin-top: 0 !important; } /* undo space-y-4, kept for the stacked layout */
        .setup-step-pair .setup-field-grid,
        .setup-step-pair .setup-role-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .setup-step-pair .setup-field-grid > :last-child { grid-column: 1 / -1; }
    }
    .manage-grid {
        display: grid; gap: .75rem;
        grid-template-columns: 280px minmax(0, 1fr);
        align-items: start;
    }

    @media (max-width: 1279px) {
        .tm-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .tm-card-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .tm-toolbar { flex-wrap: wrap; }
        .tm-toolbar .tm-search { flex-basis: 100%; }
        .tm-toolbar .tm-select { flex: 1 1 10rem; width: auto; }
        .setup-grid { grid-template-columns: minmax(0, 1fr); }
        .setup-role-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 1023px) {
        .setup-field-grid { grid-template-columns: minmax(0, 1fr); }
        .manage-grid { grid-template-columns: minmax(0, 1fr); }
        .tm-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767px) {
        .tm-stat-grid { grid-template-columns: minmax(0, 1fr); }
        .tm-card-grid { grid-template-columns: minmax(0, 1fr); }
        .setup-role-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    /* ── Utilities the frozen build never emitted ── */
    .text-\[9px\], .text-\[0\.5625rem\]  { font-size: 0.5625rem; line-height: 1.3; }
    .text-\[10px\], .text-\[0\.625rem\] { font-size: 0.625rem; line-height: 1.35; }
    .text-\[11px\], .text-\[0\.6875rem\] { font-size: 0.6875rem; line-height: 1.4; }
    .text-\[12px\], .text-\[0\.75rem\] { font-size: 0.75rem; line-height: 1.45; }
    .text-\[13px\], .text-\[0\.8125rem\] { font-size: 0.8125rem; line-height: 1.45; }
    .text-\[14px\], .text-\[0\.875rem\] { font-size: 0.875rem; line-height: 1.5; }
    .text-\[15px\], .text-\[0\.9375rem\] { font-size: 0.9375rem; line-height: 1.5; }
    .text-\[28px\], .text-\[1\.75rem\] { font-size: 1.75rem; line-height: 1.1; }
    .text-\[30px\], .text-\[1\.875rem\] { font-size: 1.875rem; line-height: 1.15; }

    .gap-1\.5 { gap: .375rem; }
    .gap-2\.5 { gap: .625rem; }
    .mt-0\.5 { margin-top: .125rem; }
    .mt-1\.5 { margin-top: .375rem; }
    .mt-2\.5 { margin-top: .625rem; }
    .mt-auto { margin-top: auto; }
    .mb-1\.5 { margin-bottom: .375rem; }
    .py-0\.5 { padding-top: .125rem; padding-bottom: .125rem; }
    .py-1\.5 { padding-top: .375rem; padding-bottom: .375rem; }
    .py-2\.5 { padding-top: .625rem; padding-bottom: .625rem; }
    .px-1\.5 { padding-left: .375rem; padding-right: .375rem; }
    .px-2\.5 { padding-left: .625rem; padding-right: .625rem; }
    .px-3\.5 { padding-left: .875rem; padding-right: .875rem; }
    .px-7 { padding-left: 1.75rem; padding-right: 1.75rem; }
    .p-0\.5 { padding: .125rem; }
    .p-3\.5 { padding: .875rem; }
    .pt-3 { padding-top: .75rem; }
    .pt-5 { padding-top: 1.25rem; }
    .pt-10 { padding-top: 2.5rem; }
    .pb-5 { padding-bottom: 1.25rem; }
    .pb-0 { padding-bottom: 0; }
    .pl-9 { padding-left: 2.25rem; }
    .pl-11 { padding-left: 2.75rem; }
    .pr-0\.5 { padding-right: .125rem; }
    .mx-5 { margin-left: 1.25rem; margin-right: 1.25rem; }

    .w-1\.5 { width: .375rem; }
    .h-1\.5 { height: .375rem; }
    .w-2\.5 { width: .625rem; }
    .h-2\.5 { height: .625rem; }
    .h-fit { height: fit-content; }
    .max-h-\[22rem\] { max-height: 22rem; }
    .leading-snug { line-height: 1.375; }
    .whitespace-pre-line { white-space: pre-line; }
    .top-1\/2 { top: 50%; }
    .left-4 { left: 1rem; }
    .left-5 { left: 1.25rem; }
    .-translate-y-1\/2 { transform: translateY(-50%); }
    .tracking-\[0\.15em\] { letter-spacing: .15em; }

    .bg-brand-soft\/40 { background-color: rgba(251,238,233, .6); }
    .bg-slate-50\/50 { background-color: rgba(243,242,241, .5); }
    .bg-slate-50\/60 { background-color: rgba(243,242,241, .6); }
    .border-brand\/10 { border-color: rgba(123,23,48, .1); }
    .border-brand\/15 { border-color: rgba(123,23,48, .15); }
    .shadow-brand\/20 { --tw-shadow-color: rgba(123,23,48, .2); }
    .placeholder\:text-slate-400::placeholder { color: #7A6068; }

    .hover\:text-brand:hover { color: #7B1730; }
    .hover\:border-brand\/40:hover { border-color: rgba(123,23,48, .4); }
    .hover\:bg-slate-50:hover { background-color: #F3F2F1; }
    .hover\:bg-slate-100:hover { background-color: #E4E2E0; }
    .hover\:opacity-95:hover { opacity: .95; }
    .hover\:opacity-90:hover { opacity: .9; }
    .focus\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
    .focus\:border-brand:focus { border-color: #7B1730; }

    /* ── Main tab bar ── */
    .main-tab-btn {
        position:relative; display:inline-flex; align-items:center; gap:.5rem;
        padding:.65rem 1.25rem; border-radius:1rem; font-size:.8rem; font-weight:700;
        color:#6B4A54; cursor:pointer; transition:all .25s ease; border:1.5px solid #E4E2E0;
        background:#fff; white-space:nowrap; user-select:none;
    }
    .main-tab-btn:hover { border-color:#8A817A; color:#181818; background:#F3F2F1; }
    .main-tab-btn.active {
        background:#5F5A55;
        color:#fff; border-color:transparent;
        box-shadow:0 8px 20px -6px rgba(24,24,24,.3);
    }
    .main-tab-btn .tab-badge {
        padding:.1rem .45rem; border-radius:9999px; font-size:.65rem; font-weight:800;
        background:rgba(255,255,255,.25); color:#fff;
    }
    .main-tab-btn:not(.active) .tab-badge { background:#F3F2F1; color:#6B4A54; }

    /* ── Tab panel animation ── */
    .tab-panel { display:none; animation:fadeInUp .3s ease; }
    .tab-panel.active { display:block; }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }

    /* ── Send Feedback (concept revision) ──
       Spelled out rather than composed from utilities: public/css/app.css is a
       frozen build, so a utility colour may resolve to nothing. Orange so it
       reads as "needs work", apart from the green Approve beside it. */
    .btn-sendback {
        background:#F97316; color:#fff; border:1px solid #EA580C;
        box-shadow:0 4px 12px -2px rgba(249,115,22,.35);
    }
    .btn-sendback:hover { background:#EA580C; }

    /* ── Task review verdict: Revise amber, Approve green ──
       Spelled out for the same reason: the frozen build has no hover shade for
       either colour. */
    .btn-revise {
        background:#F97316; color:#fff; border:1px solid #EA580C;
        box-shadow:0 4px 12px -2px rgba(249,115,22,.35);
    }
    .btn-revise:hover { background:#EA580C; }
    .btn-approve {
        background:#16A34A; color:#fff; border:1px solid #15803D;
        box-shadow:0 4px 12px -2px rgba(22,163,74,.35);
    }
    .btn-approve:hover { background:#15803D; }

    /* Shown in place of the verdict buttons once a task is approved. Not a
       display rule on the class itself, which would outrank .hidden. */
    #reviewApprovedState:not(.hidden) {
        display:flex; align-items:center; gap:.625rem;
        padding:.75rem .875rem; border-radius:.875rem;
        border:1px solid #BBF7D0; background:#F0FDF4; color:#15803D;
    }
    #reviewApprovedState .iconify { font-size:1.375rem; flex-shrink:0; }
    #reviewApprovedState .rv-approved-title { font-size:0.8125rem; font-weight:800; }
    #reviewApprovedState .rv-approved-meta { font-size:0.6875rem; color:#4A4643; margin-top:1px; }

    /* ── Student list in form ── */
    .student-row { transition:all .2s ease; }
    .student-row:hover { background:#F3F2F1 !important; }

    /* ── Role color dots ── */
    /* No reds or maroons: grays and golds, one per role. */
    .role-dot-front_desk            { background:#4A4643; }
    .role-dot-restaurant_management { background:#D9B86A; }
    .role-dot-room_management       { background:#8A817A; }
    .role-dot-maintenance           { background:#C9A45C; }
    .role-dot-housekeeping          { background:#B5AFAA; }

    /* ── Teams table layout ── */
    #teamsTable {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
    }
    #teamsTable th,
    #teamsTable td {
        vertical-align: middle;
    }
    #teamsTable .col-team { width: 16%; }
    #teamsTable .col-concept { width: 18%; }
    #teamsTable .col-count { width: 10%; }
    #teamsTable .col-roles { width: 38%; }
    #teamsTable .col-action { width: 18%; }
    #teamsTable .truncate-cell {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
        display: block;
    }
    #teamsTable .member-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        max-width: 100%;
        padding: 2px 0.5rem;
        border-radius: 9999px;
        background: #F3F2F1;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #5A3941;
        line-height: 1.35;
    }
    #teamsTable .member-chip .chip-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 7.5rem;
    }
    #teamsTable .role-mini {
        display: inline-flex;
        align-items: center;
        gap: 0.1875rem;
        color: #7A6068;
        font-weight: 500;
        white-space: nowrap;
    }

</style>
@include('partials.team-dialog-styles')

{{-- ═══════════════════════════════════════════════
     SUCCESS / ERROR FLASH
═══════════════════════════════════════════════ --}}
@if(session('success'))
<div id="flashBanner" class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-2xl text-sm font-semibold">
    <span class="iconify text-lg" data-icon="mdi:check-circle-outline"></span>
    {{ session('success') }}
    <button onclick="document.getElementById('flashBanner').remove()" class="ml-auto text-green-400 hover:text-green-600">
        <span class="iconify" data-icon="mdi:close"></span>
    </button>
</div>
@endif

@if(!empty($intakeLock))
<div class="mb-5 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-700 px-5 py-3 rounded-2xl text-sm font-semibold">
    <span class="iconify text-lg shrink-0" data-icon="mdi:alert-outline"></span>
    <span>{{ $intakeLock }} Creating teams stays switched off until then.</span>
</div>
@endif

{{-- ═══════════════════════════════════════════════
     TAB BAR
═══════════════════════════════════════════════ --}}
@php
    $activeTab = request('tab', 'teams');
    $validTabs = ['teams', 'create_task', 'team_setup'];
    if (!in_array($activeTab, $validTabs)) $activeTab = 'teams';

    // A failed create re-renders the page; land back on the setup screen
    // rather than on the teams list the faculty had already left.
    if ($errors->any() && in_array(old('_form_source'), ['create_team', 'create_teams_bulk'], true)) {
        $activeTab = 'team_setup';
    }

    // A refused Set Task comes back via back(), whose URL has no tab, so it would
    // land on Teams with the error drawn on a hidden panel.
    if ($errors->has('title') || $errors->has('role') || ($errors->any() && old('_form_source') === 'create_task')) {
        $activeTab = 'create_task';
    }
@endphp

{{-- ═══════════════════════════════════════════════
     TAB 1 — TEAMS (View all groups)
═══════════════════════════════════════════════ --}}
<div id="panel-teams" class="tab-panel {{ $activeTab === 'teams' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'activeClass' => $activeClass ?? null,
        ])

        @php
            // The four seats every team is built from (HotelTemplateBuilder::SEATS) —
            // a team with one of each is complete, full stop. A team built before the
            // seat model can still carry a literal "maintenance" row; it simply reads
            // as an extra role nobody's Role Assignment card asks about any more.
            $roleLabels = [
                'front_desk'            => 'Front Desk',
                'restaurant_management' => 'Restaurant',
                'room_management'       => 'Rooms',
                'housekeeping'          => 'Housekeeping/Maint.',
            ];
            $roleShort = [
                'front_desk'            => 'FD',
                'restaurant_management' => 'RST',
                'room_management'       => 'RM',
                'housekeeping'          => 'HK',
            ];
            $roleCardTints = [
                // One neutral chip for every role, no maroon.
                'front_desk'            => 'bg-slate-100 text-slate-700',
                'restaurant_management' => 'bg-slate-100 text-slate-700',
                'room_management'       => 'bg-slate-100 text-slate-700',
                'housekeeping'          => 'bg-slate-100 text-slate-700',
            ];
            $roleCardIcons = [
                'front_desk'            => 'mdi:desk',
                'restaurant_management' => 'mdi:silverware-fork-knife',
                'room_management'       => 'mdi:bed-outline',
                'housekeeping'          => 'mdi:spray-bottle',
            ];
            $templateLabels = [
                '1' => ['name' => 'Template 1', 'style' => 'Dark luxury'],
                '2' => ['name' => 'Template 2', 'style' => 'Light natural'],
            ];

            // Headline figures for this class, read off the rosters and the task rows
            // already in memory rather than a query per card.
            $overviewTeamCount    = ($groups ?? collect())->count();
            $overviewStudentCount = ($groups ?? collect())->sum(fn ($members) => $members->count());
            $overviewTaskTotal    = 0;
            $overviewTaskDone     = 0;
            foreach (($groups ?? collect()) as $overviewName => $overviewMembers) {
                foreach (($teamActivityByGroup[$overviewName] ?? []) as $activityRow) {
                    $overviewTaskTotal++;
                    if (($activityRow['status'] ?? null) === 'archived') {
                        $overviewTaskDone++;
                    }
                }
            }
            $overviewRate = $overviewTaskTotal > 0 ? (int) round(($overviewTaskDone / $overviewTaskTotal) * 100) : 0;
        @endphp

        <div class="px-4 sm:px-6 py-5">
            <!-- Overview figures -->
            <div class="tm-stat-grid mb-5">
                <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-brand-soft flex items-center justify-center shrink-0">
                        <span class="iconify text-brand text-2xl" data-icon="mdi:account-group-outline"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-extrabold text-slate-900 leading-none">{{ $overviewTeamCount }}</p>
                        <p class="text-[0.8125rem] text-slate-500 font-semibold mt-1">Total Teams</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                        <span class="iconify text-blue-500 text-2xl" data-icon="mdi:account-outline"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-extrabold text-slate-900 leading-none">{{ $overviewStudentCount }}</p>
                        <p class="text-[0.8125rem] text-slate-500 font-semibold mt-1">Total Students</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-violet-50 flex items-center justify-center shrink-0">
                        <span class="iconify text-violet-500 text-2xl" data-icon="mdi:clipboard-check-outline"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-extrabold text-slate-900 leading-none">{{ $overviewTaskTotal }}</p>
                        <p class="text-[0.8125rem] text-slate-500 font-semibold mt-1">Total Tasks Assigned</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0">
                        <span class="iconify text-amber-500 text-2xl" data-icon="mdi:chart-box-outline"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[1.75rem] font-extrabold text-slate-900 leading-none">{{ $overviewRate }}%</p>
                        <p class="text-[0.8125rem] text-slate-500 font-semibold mt-1">Overall Completion Rate</p>
                    </div>
                </div>
            </div>

            <!-- Team cards -->
            <div id="teamCardsGrid" class="tm-card-grid">
                @forelse($groups ?? [] as $groupName => $groupMembers)
                    @php
                        $createdAt  = optional($groupMembers->first()->created_at)->format('M d, Y');
                        $memberData = [];
                        foreach ($groupMembers as $m) {
                            $u = $m->student?->user;
                            $dn = trim(implode(' ', array_filter([$u?->last_name, $u?->first_name, $u?->middle_name])));
                            $dn = $dn !== '' ? $dn : ($u?->name ?? 'Student');
                            $memberRoles = $m->roles->pluck('role')->toArray();
                            $memberData[] = [
                                'student_id' => $m->student_id,
                                'name'       => $dn,
                                'user_id'    => $u?->user_id,
                                'roles'      => $memberRoles,
                                'role_labels' => array_map(fn($r) => $roleLabels[$r] ?? $r, $memberRoles),
                            ];
                        }
                        $memberJson = json_encode($memberData);

                        // Task Submission Indicator: submitted work sitting on this team,
                        // unreviewed. updatePendingReviewBadges() below repaints this same
                        // shape after every poll, so first paint and a live update read alike.
                        $cardPendingReview = ($teamPendingReviewByGroup ?? [])[$groupName] ?? null;

                        // Same idea, for the hotel concept: the team has submitted at least
                        // one of its two proposals and faculty has not yet answered it.
                        $cardConceptPending = ($teamConceptPendingByGroup ?? [])[$groupName] ?? null;

                        $cardRoles = $groupMembers
                            ->flatMap(fn ($m) => $m->roles->pluck('role'))
                            ->filter()
                            ->unique()
                            ->values();
                        $cardLeadRole = $cardRoles->first();

                        // The site template Front Desk picked for the team, or null
                        // while the team has not chosen one yet.
                        $cardTemplate = $templateLabels[($teamTemplateByGroup ?? [])[$groupName] ?? ''] ?? null;

                        $teamConcepts   = ($conceptsByGroup ?? collect())->get($groupName, collect());
                        $cardConcept    = $teamConcepts->first();
                        // Str::limit cuts at a raw character count, so it can slice a word
                        // in half (e.g. "...for f..."). Str::words stops on a word boundary
                        // instead - the line-clamp CSS on the card still caps it visually.
                        $cardConceptText = $cardConcept
                            ? \Illuminate\Support\Str::words($cardConcept->description ?: $cardConcept->hotel_type_label, 20, '…')
                            : 'No hotel concept proposed yet.';

                        // Progress across the task rows this team owns.
                        $cardTaskRows  = $teamActivityByGroup[$groupName] ?? [];
                        $cardTaskTotal = count($cardTaskRows);
                        $cardTaskDone  = 0;
                        foreach ($cardTaskRows as $cardTaskRow) {
                            if (($cardTaskRow['status'] ?? null) === 'archived') {
                                $cardTaskDone++;
                            }
                        }
                        $cardPercent = $cardTaskTotal > 0 ? (int) round(($cardTaskDone / $cardTaskTotal) * 100) : 0;

                        /* The same rows counted by task rather than by row: a task is
                           one step of the project and its activities are one role's
                           share each, so it is finished only once every role given an
                           activity in it has handed theirs in. */
                        $cardSteps = \App\Support\TaskStepProgress::forRows($cardTaskRows);
                    @endphp
                    @php
                        // Faculty approvals over the required customization tasks: the
                        // one figure the compact card reports. Everything else about the
                        // team lives in View Team's Overview, rendered just below.
                        $cardPhase = \App\Support\SimulationPhase::progress($groupName, (int) $groupMembers->first()->faculty_id);
                        $cardApprovalState = $cardPhase['required'] > 0 && $cardPhase['approved'] >= $cardPhase['required']
                            ? 'completed'
                            : ($cardPhase['approved'] > 0 ? 'progress' : 'idle');
                        $cardApprovalLabel = ['completed' => 'Completed', 'progress' => 'In Progress', 'idle' => 'Not Started'][$cardApprovalState];
                        $cardApproval = ($teamApprovalByGroup ?? [])[$groupName] ?? null;
                    @endphp
                    <article class="team-card tc" data-team-name="{{ $groupName }}" style="--i: {{ $loop->index }}">
                        <div class="tc-head">
                            <span class="tc-mark" aria-hidden="true"><span class="iconify" data-icon="mdi:account-group-outline"></span></span>
                            <h3 class="tc-name" title="{{ $groupName }}">{{ $groupName }}</h3>
                            <span class="tc-members {{ $groupMembers->count() >= 4 ? 'is-full' : '' }}" title="{{ $groupMembers->count() }} of 4 members">
                                <span class="iconify" data-icon="mdi:account-multiple-outline"></span>
                                {{ $groupMembers->count() }}/4
                            </span>
                        </div>

                        <div class="tc-status-row">
                            <span class="tc-status is-{{ $cardApprovalState }} status-badge-{{ ['completed' => 'completed', 'progress' => 'in_progress', 'idle' => 'not_started'][$cardApprovalState] }}">
                                <span class="tc-status-dot"></span>{{ $cardApprovalLabel }}
                            </span>
                        </div>

                        <div class="tc-meter">
                            <div class="tc-meter-top">
                                <span>Task approval</span>
                                <b>{{ $cardPhase['percent'] }}%</b>
                            </div>
                            <div class="tc-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $cardPhase['percent'] }}" aria-label="Task approval">
                                <span style="width: {{ $cardPhase['percent'] }}%"></span>
                            </div>
                            <p class="tc-meter-sub">{{ $cardPhase['approved'] }} of {{ $cardPhase['required'] }} required task{{ $cardPhase['required'] === 1 ? '' : 's' }} approved</p>
                        </div>

                        <div class="tc-actions">
                            <button type="button"
                                    onclick='openTeamModal({{ json_encode($groupName) }}, {{ $memberJson }}, {{ json_encode($createdAt) }}, {{ json_encode($teamActivityByGroup[$groupName] ?? []) }})'
                                    class="tc-btn tc-btn-primary">
                                <span class="iconify" data-icon="mdi:eye-outline"></span>
                                View Team
                                {{-- Lit by CSS while either review notice in the Overview below is showing. --}}
                                <span class="tc-alert-dot" title="Submissions awaiting your review"></span>
                            </button>
                            <button type="button"
                                    onclick='openUpdateModal(@json($groupName), @json($memberData))'
                                    class="tc-btn">
                                <span class="iconify" data-icon="mdi:pencil-outline"></span>
                                Update
                            </button>
                        </div>

                        {{-- View Team's Overview for this team. Never shown on the card:
                             openTeamModal() copies it into the dialog. The two review
                             notices stay in here so the live poll (updatePendingReviewBadges)
                             keeps finding them inside .team-card. --}}
                        <div class="hidden" data-team-overview>
                            <div class="ov">
                                <div class="ov-attn">
                                    {{-- Task Submission Indicator. --}}
                                    <div data-pending-review-badge class="ov-notice {{ !$cardPendingReview ? 'hidden' : '' }}">
                                        <span class="ov-notice-icon"><span class="iconify" data-icon="mdi:inbox-arrow-down-outline"></span></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="ov-notice-title" data-pending-review-headline>
                                                @if($cardPendingReview)
                                                    New Submission &mdash; {{ $cardPendingReview['count'] }} Awaiting Review
                                                @endif
                                            </p>
                                            <p class="ov-notice-text" data-pending-review-detail>
                                                @if($cardPendingReview && $cardPendingReview['latest'])
                                                    {{ $cardPendingReview['latest']['student_name'] }} &middot; {{ $cardPendingReview['latest']['role_label'] }} &middot;
                                                    "{{ $cardPendingReview['latest']['title'] }}" &middot; {{ $cardPendingReview['latest']['submitted_human'] }}
                                                @endif
                                            </p>
                                        </div>
                                        <button type="button" data-review-submission
                                                onclick='openTeamModalAwaitingReview({{ json_encode($groupName) }}, {{ $memberJson }}, {{ json_encode($createdAt) }}, {{ json_encode($teamActivityByGroup[$groupName] ?? []) }})'
                                                class="tm-btn tm-btn-sm tm-btn-dark">
                                            <span class="iconify" data-icon="mdi:eye-check-outline"></span> Review Submission
                                        </button>
                                    </div>

                                    {{-- Hotel Concept Submission Indicator. --}}
                                    <div data-concept-pending-badge class="ov-notice {{ !$cardConceptPending ? 'hidden' : '' }}">
                                        <span class="ov-notice-icon"><span class="iconify" data-icon="mdi:lightbulb-on-outline"></span></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="ov-notice-title" data-concept-pending-headline>
                                                @if($cardConceptPending)
                                                    Hotel Concept Submitted{{ $cardConceptPending['count'] > 1 ? ' — Both Proposals' : '' }}
                                                @endif
                                            </p>
                                            <p class="ov-notice-text" data-concept-pending-detail>
                                                @if($cardConceptPending && $cardConceptPending['latest'])
                                                    {{ $cardConceptPending['latest']['submitted_by'] }} &middot; {{ $cardConceptPending['latest']['slot_label'] }} &middot;
                                                    "{{ $cardConceptPending['latest']['title'] }}" &middot; {{ $cardConceptPending['latest']['submitted_human'] }}
                                                @endif
                                            </p>
                                        </div>
                                        <button type="button"
                                                onclick='openTeamModalConceptReview({{ json_encode($groupName) }}, {{ $memberJson }}, {{ json_encode($createdAt) }}, {{ json_encode($teamActivityByGroup[$groupName] ?? []) }})'
                                                class="tm-btn tm-btn-sm tm-btn-dark">
                                            <span class="iconify" data-icon="mdi:eye-check-outline"></span> Review Concept
                                        </button>
                                    </div>

                                    <p class="ov-clear"><span class="iconify" data-icon="mdi:check-circle-outline"></span> Nothing is waiting for your review.</p>
                                </div>

                                <div class="ov-grid">
                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:lightbulb-outline"></span> Hotel Concept</p>
                                        <p class="ov-value">{{ $cardConcept->title ?? 'No concept yet' }}</p>
                                        <p class="ov-tag">{{ $cardConcept ? \App\Support\HotelConceptDesk::statusLabel($cardConcept) : 'Front Desk proposes this' }}</p>
                                        <p class="ov-text">{{ $cardConceptText }}</p>
                                        <button type="button" class="ov-link" onclick="switchTeamModalTab('concept')">Open Hotel Concept <span class="iconify" data-icon="mdi:arrow-right"></span></button>
                                    </section>

                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:palette-outline"></span> Selected Hotel Template</p>
                                        <p class="ov-value">{{ $cardTemplate['name'] ?? 'Not chosen yet' }}</p>
                                        <p class="ov-tag {{ $cardTemplate ? 'is-good' : '' }}">{{ $cardTemplate['style'] ?? 'Front Desk picks this' }}</p>
                                    </section>

                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:progress-wrench"></span> Customization Task Progress</p>
                                        <div class="ov-meter-top"><span>Activities handed in</span><b>{{ $cardPercent }}%</b></div>
                                        <div class="tc-bar"><span style="width: {{ $cardPercent }}%"></span></div>
                                        <p class="ov-text">{{ $cardTaskDone }} of {{ $cardTaskTotal }} activit{{ $cardTaskTotal === 1 ? 'y' : 'ies' }} handed in</p>
                                        <p class="ov-text">
                                            @if($cardSteps['total'] > 0)
                                                {{ $cardSteps['finished'] }} of {{ $cardSteps['total'] }} task{{ $cardSteps['total'] === 1 ? '' : 's' }} complete
                                            @else
                                                No tasks assigned yet.
                                            @endif
                                        </p>
                                        <button type="button" class="ov-link" onclick="switchTeamModalTab('tasks')">Open Task Activity <span class="iconify" data-icon="mdi:arrow-right"></span></button>
                                    </section>

                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:check-decagram-outline"></span> Faculty Approval</p>
                                        <div class="ov-meter-top"><span>Required tasks approved</span><b>{{ $cardPhase['percent'] }}%</b></div>
                                        <div class="tc-bar"><span style="width: {{ $cardPhase['percent'] }}%"></span></div>
                                        <p class="ov-text">{{ $cardPhase['approved'] }} of {{ $cardPhase['required'] }} approved</p>
                                        <div class="mt-2">@include('partials.team-completion', ['done' => $cardPhase['approved'], 'total' => $cardPhase['required']])</div>
                                    </section>

                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:briefcase-outline"></span> Roles Assigned</p>
                                        <p class="ov-value">{{ $cardRoles->count() }} of 4 role{{ $cardRoles->count() === 1 ? '' : 's' }}</p>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @forelse($cardRoles as $cardRole)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[0.625rem] font-bold {{ $roleCardTints[$cardRole] ?? 'bg-slate-100 text-slate-500' }}">
                                                    {{ $roleLabels[$cardRole] ?? $cardRole }}
                                                </span>
                                            @empty
                                                <span class="ov-text">No roles assigned yet</span>
                                            @endforelse
                                        </div>
                                        <button type="button" class="ov-link" onclick="switchTeamModalTab('members')">Open Members &amp; Roles <span class="iconify" data-icon="mdi:arrow-right"></span></button>
                                    </section>

                                    <section class="ov-tile">
                                        <p class="ov-label"><span class="iconify" data-icon="mdi:storefront-outline"></span> Simulation</p>
                                        <p class="ov-value">Open</p>
                                        <p class="ov-tag {{ ($cardApproval['unlocked'] ?? false) ? 'is-good' : '' }}">
                                            {{ ($cardApproval['unlocked'] ?? false) ? 'All tasks approved' : 'Approval in progress' }}
                                        </p>
                                        <p class="ov-text">The Hotel Simulation is open to every team at any time. Desk assignments are under Members &amp; Roles.</p>
                                        <button type="button" class="ov-link" onclick="switchTeamModalTab('members')">Open Desk Assignments <span class="iconify" data-icon="mdi:arrow-right"></span></button>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="tm-card-empty px-5 py-12 text-center">
                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <span class="iconify text-slate-300 text-2xl" data-icon="mdi:account-group-outline"></span>
                        </div>
                        <p class="text-sm font-semibold text-slate-400">
                            @if($activeClass)
                                No teams in {{ $activeClass->name }} yet.
                            @else
                                No teams created yet.
                            @endif
                        </p>
                        <button onclick="openCreateTeamModal()"
                            class="mt-3 px-4 py-2 bg-brand text-white text-xs font-bold rounded-xl hover:scale-105 transition shadow-md shadow-brand/20">
                            Add Teams{{ $activeClass ? ' for ' . $activeClass->name : '' }}
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Team Info Modal -->
{{-- Wide on purpose: the three sections sit in a column on the left, each with a
     one-line explanation, and the chosen one fills the rest of the dialog. --}}
<div id="teamInfoModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTeamModal()"></div>
    <div class="tm-box relative bg-white shadow-2xl flex flex-col overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="modalTeamName">
        <div class="tm-head">
            <span class="tm-head-icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></span>
            <div class="min-w-0">
                <p class="tm-kicker">Team details</p>
                <h3 id="modalTeamName" class="tm-title">Team</h3>
                <p id="modalTeamSummary" class="tm-sub"></p>
            </div>
            <button type="button" onclick="closeTeamModal()" class="tm-close">
                <span class="iconify" data-icon="mdi:close"></span> Close
            </button>
        </div>

        <div class="tm-body">
            <nav class="tm-nav" aria-label="Team details sections">
                <button type="button" onclick="switchTeamModalTab('overview')" id="team-tab-overview" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Overview</span>
                        <span class="tm-tab-desc">Concept, template, progress, approvals and simulation at a glance</span>
                        <span id="teamTabCountOverview" class="tm-tab-count is-alert"></span>
                    </span>
                </button>
                <button type="button" onclick="switchTeamModalTab('members')" id="team-tab-members" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Team Members &amp; Roles</span>
                        <span class="tm-tab-desc">Who is in the team and what each one does</span>
                        <span id="teamTabCountMembers" class="tm-tab-count"></span>
                    </span>
                </button>
                <button type="button" onclick="switchTeamModalTab('concept')" id="team-tab-concept" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:lightbulb-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Hotel Concept</span>
                        <span class="tm-tab-desc">The hotel ideas the team proposed for your approval</span>
                    </span>
                </button>
                <button type="button" onclick="switchTeamModalTab('tasks')" id="team-tab-tasks" class="tm-tab">
                    <span class="tm-tab-icon"><span class="iconify" data-icon="mdi:clipboard-text-clock-outline"></span></span>
                    <span class="tm-tab-copy">
                        <span class="tm-tab-title">Team Task Activity</span>
                        <span class="tm-tab-desc">Tasks given to the team and who handed them in</span>
                        <span id="teamTabCountTasks" class="tm-tab-count"></span>
                    </span>
                </button>
            </nav>

            <div class="tm-content">
                <!-- Overview: copied from the team's card by openTeamModal() -->
                <div id="team-panel-overview">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Overview</h4>
                        <p class="tm-panel-note">Everything about this team in one place. Open a section for the full details.</p>
                    </div>
                    <div id="teamModalOverviewBody"></div>
                </div>

                <!-- Members -->
                <div id="team-panel-members" class="hidden">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Team Members &amp; Roles</h4>
                        <p class="tm-panel-note">Each card shows a student and the part of the hotel they handle. Choose See activity to read everything that student has done.</p>
                    </div>
                    <div id="teamModalMembersBody" class="tm-member-grid"></div>

                    <!-- Simulation roles: one desk per student, confirmed by faculty -->
                    <div id="teamModalSimRoles" class="tm-section mt-5"></div>

                    <!-- Selected member's centralized activity log -->
                    <div id="memberActivityPanel" class="hidden">
                        <div class="tm-card mt-5">
                            <div class="tm-card-head">
                                <p class="tm-card-title" id="memberActivityPanelTitle">Member Activity</p>
                                <button type="button" onclick="closeMemberActivityPanel()" class="tm-btn tm-btn-sm">
                                    <span class="iconify" data-icon="mdi:chevron-up"></span> Hide
                                </button>
                            </div>
                            <div id="memberActivityPanelBody" class="tm-activity-list"></div>
                        </div>
                    </div>
                </div>

                <!-- Front Desk's hotel concept (loaded when the modal opens) -->
                <div id="team-panel-concept" class="hidden">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Hotel Concept</h4>
                        <p class="tm-panel-note">The team proposes two hotel ideas. Approve the one you want them to build, or send one back with a note on what to change.</p>
                    </div>
                    <div id="teamConceptError" class="tm-alert hidden"></div>
                    <div id="teamModalConceptBody" class="tm-concept-grid">
                        <div class="tm-empty tm-span-all">Loading hotel concept…</div>
                    </div>
                </div>

                <!-- Team task activity (assignment history) -->
                <div id="team-panel-tasks" class="hidden">
                    <div class="tm-panel-head">
                        <h4 class="tm-panel-title">Team Task Activity</h4>
                        <p class="tm-panel-note">Open a task to see the student's work, then approve it or ask for changes.</p>
                    </div>
                    <div id="teamModalApproval" class="tm-section" style="margin-bottom: 1rem;"></div>
                    <div id="teamModalActivityStats" class="tm-stats"></div>
                    <div class="tm-card">
                        {{-- One tab per department and student; filled by renderTeamModalActivityPage(). --}}
                        <div id="teamModalActivityTabs" class="tm-role-tabs"></div>
                        <table class="tm-table">
                            {{-- Grouped by department (renderTeamModalActivityPage): the
                                 department and its student head each group once, not every row. --}}
                            <colgroup>
                                <col>
                                <col style="width: 10.5rem;">
                                <col style="width: 8.5rem;">
                                <col style="width: 7.5rem;">
                                <col style="width: 9rem;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Handed in</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="teamModalActivityBody"></tbody>
                        </table>
                        <div id="teamModalActivityPager" class="tm-pager hidden items-center justify-between gap-2">
                            <button type="button" id="teamModalActivityPrev" class="tm-btn tm-btn-sm">
                                <span class="iconify" data-icon="mdi:chevron-left"></span> Previous
                            </button>
                            <span class="tm-pager-label"><span id="teamModalActivityPageLabel"></span> <span id="teamModalActivityMeta"></span></span>
                            <button type="button" id="teamModalActivityNext" class="tm-btn tm-btn-sm">
                                Next <span class="iconify" data-icon="mdi:chevron-right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ═══════ STUDENT TASK DIALOG: the student's actual work + your decision ═══════ -->
<div id="taskReviewModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTaskReview()"></div>
    <div class="review-modal-box tm-box relative bg-white shadow-2xl flex flex-col overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="reviewTaskTitle">

        <div class="tm-head">
            <span class="tm-head-icon"><span class="iconify" data-icon="mdi:file-document-check-outline"></span></span>
            <div class="min-w-0 flex-1">
                <p class="tm-kicker">Student task</p>
                <h4 id="reviewTaskTitle" class="tm-title truncate">Review Submission</h4>
                <div id="reviewTaskMeta" class="rv-meta"></div>
            </div>
            <div id="reviewStatusRow" class="rv-status"></div>
            <button type="button" onclick="closeTaskReview()" class="tm-close">
                <span class="iconify" data-icon="mdi:close"></span> Close
            </button>
        </div>

        <div class="rv-body">
            <!-- The work itself: a site to look at for most tasks, the concept text
                 itself for the hotel concept, which has no page to render. -->
            <div class="review-modal-work rv-work">
                <div class="rv-toolbar">
                    <div class="min-w-0">
                        <p id="reviewWorkLabel" class="rv-work-label">The team's live site</p>
                        <p class="rv-work-hint">
                            <span id="reviewWorkHint"></span>
                            {{-- What the After preview managed to outline, reported back by hms-review-highlight.js. --}}
                            <span id="reviewHighlightStatus" class="hidden"></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Only shown once this task has a submission to anchor "After" to.
                             What the student changed is outlined in green on After itself. --}}
                        <div id="reviewCompareToggle" class="rv-seg hidden" role="group" aria-label="Compare">
                            <button type="button" data-compare="before">
                                <span class="iconify" data-icon="mdi:history"></span> Before
                            </button>
                            <button type="button" data-compare="after" class="is-on">
                                <span class="iconify" data-icon="mdi:file-check-outline"></span> After
                            </button>
                        </div>
                        <a id="reviewOpenTab" href="#" target="_blank" rel="noopener" class="tm-btn tm-btn-sm hidden">
                            <span class="iconify" data-icon="mdi:open-in-new"></span> Open in new tab
                        </a>
                    </div>
                </div>
                <div class="flex-1 min-h-0 relative">
                    <div id="reviewPreviewEmpty" class="absolute inset-0 flex items-center justify-center text-center px-6">
                        <div class="tm-empty">
                            <span class="iconify" data-icon="mdi:web-off"></span>
                            No site to show for this task yet.
                        </div>
                    </div>
                    <iframe id="reviewPreviewFrame" src="" title="Team site preview"
                            class="w-full h-full border-0 bg-white hidden"></iframe>
                    <div id="reviewConceptPane" class="absolute inset-0 overflow-y-auto bg-white p-5 hidden"></div>
                    <div id="reviewChangesPane" class="absolute inset-0 overflow-y-auto bg-white p-4 hidden"></div>
                </div>
            </div>

            <!-- Progress, past notes and the decision -->
            <div class="review-modal-side rv-side">
                <div class="rv-side-scroll">
                    <div id="reviewPrevFeedbackWrap" class="tm-section hidden">
                        <p class="tm-section-title"><span class="iconify" data-icon="mdi:message-text-outline"></span> Your earlier feedback</p>
                        <p id="reviewPrevFeedback" class="tm-note-text mt-2"></p>
                        <p id="reviewPrevFeedbackMeta" class="tm-section-count mt-2"></p>
                    </div>

                    <p id="reviewConceptHint" class="tm-section tm-panel-note hidden">
                        This team proposed two hotel concepts. Approve or send back each one on
                        its own card. Your decision on one does not change the other.
                    </p>
                </div>

                <p id="reviewError" class="rv-error hidden"></p>

                {{-- One decision for the whole task. The hotel concept is judged one
                     concept at a time instead, so this block hides and each concept
                     card in the left pane carries its own buttons. --}}
                <div id="reviewDecisionBlock" class="review-modal-decision rv-decision">
                    <p class="tm-section-title"><span class="iconify" data-icon="mdi:gavel"></span> Your decision</p>
                    <p id="reviewDecisionHint" class="tm-panel-note mt-1"></p>

                    {{-- Step 1: pick a decision. The feedback box only belongs to
                         Ask for changes, so it stays out of the way until that is chosen. --}}
                    <div id="reviewChoiceStep" class="tm-actions-row mt-3">
                        <button type="button" id="reviewReviseBtn" onclick="showReviseStep()" class="btn-revise tm-act">
                            <span class="iconify" data-icon="mdi:pencil-circle-outline"></span> Ask for changes
                        </button>
                        <button type="button" id="reviewApproveBtn" onclick="submitTaskFeedback('approve')" class="btn-approve tm-act">
                            <span class="iconify" data-icon="mdi:check-circle-outline"></span> Approve
                        </button>
                    </div>

                    {{-- In place of the buttons once approved: who approved it, and when. --}}
                    <div id="reviewApprovedState" class="hidden mt-3">
                        <span class="iconify" data-icon="mdi:check-decagram"></span>
                        <div class="min-w-0">
                            <p class="rv-approved-title">Approved</p>
                            <p id="reviewApprovedMeta" class="rv-approved-meta"></p>
                        </div>
                    </div>

                    {{-- Step 2: ask for changes only. --}}
                    <div id="reviewReviseStep" class="hidden mt-3">
                        <label id="reviewFeedbackLabel" for="reviewFeedback" class="tm-field-label">What should the student change?</label>
                        <textarea id="reviewFeedback" rows="5" maxlength="2000"
                            placeholder="What did they do well? What should change?"
                            class="tm-textarea"></textarea>
                        <p class="tm-help">Required. The student will read this.</p>
                        <div class="tm-actions-row mt-3">
                            <button type="button" onclick="hideReviseStep()" class="tm-btn">Cancel</button>
                            <button type="button" id="reviewSendFeedbackBtn" onclick="submitTaskFeedback('revise')" class="btn-revise tm-act">
                                <span class="iconify" data-icon="mdi:send-outline"></span> Send to student
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ═══════ TEAM SETUP & MEMBERS (its own screen, reached from Team Setup) ═══════ -->
<div id="panel-team_setup" class="tab-panel {{ $activeTab === 'team_setup' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Same bar as Teams and Set Task, so Create Team and Set Task stay
             in reach here too; Back to Teams sits in the header below it. --}}
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'classes' => $classes ?? collect(),
            'activeClass' => $activeClass ?? null,
            'classCapacity' => $classCapacity ?? 40,
            'teamCountsByClass' => $teamCountsByClass ?? [],
        ])

        <!-- Screen Header -->
        <div class="bg-white px-5 py-3 border-b border-slate-200 flex justify-between items-start gap-3">
            <div class="min-w-0">
                <h4 class="font-extrabold text-slate-900 text-2xl leading-tight">Create Student Teams</h4>
                <p class="text-[0.8125rem] text-slate-500 mt-0.5">Group your students into teams of 4 and give each student one role.</p>
            </div>
            <button type="button" onclick="closeCreateTeamModal()"
                class="h-10 px-4 rounded-xl border border-slate-200 text-slate-600 text-sm font-bold hover:bg-slate-50 transition inline-flex items-center gap-2 shrink-0">
                <span class="iconify text-base" data-icon="mdi:arrow-left"></span>
                Back to Teams
            </button>
        </div>

        @php
            // The four seats every team is built from. A member of a full team
            // holds one; a team short of members shares all four out. Housekeeping and Maintenance are one seat here because the
            // same student does both during website Customization — Simulation
            // splits them back out (Room Management picks up Housekeeping,
            // Maintenance runs on its own). See HotelTemplateBuilder::SEATS.
            $teamRoleOptions = collect(\App\Support\HotelTemplateBuilder::SEATS)
                ->mapWithKeys(fn ($r) => [$r => \App\Support\HotelTemplateBuilder::SEAT_LABELS[$r]])
                ->all();
            $roleBlurbs = [
                'front_desk'            => 'Guest services and check-in operations',
                'restaurant_management' => 'Food and beverage services',
                'room_management'       => 'Room assignment and guest room operations',
                'housekeeping'          => 'Room cleaning, facility upkeep and repairs',
            ];
        @endphp
        <script>
            // Role keys are all JS had (TEAM_DEFAULT_ROLES); the labels live in PHP.
            window.TEAM_ROLE_LABELS = @json($teamRoleOptions);
        </script>

        <!-- Tab Panel: Add Team (single or multiple) -->
        <div id="modal-panel-add_team">
            {{-- The old four-step bar never moved, so it read as progress that was
                 not there. The choice below says what happens instead. --}}
            <div class="setup-grid p-4">
                <div class="min-w-0">
            @php $unassignedStudentCount = ($students ?? collect())->count(); @endphp
            @if($unassignedStudentCount === 0)
                {{-- Nothing to build: say so plainly and point at where changes are made,
                     instead of a form full of disabled fields. --}}
                <div class="ts-done">
                    <span class="ts-done-icon"><span class="iconify" data-icon="mdi:check-circle-outline"></span></span>
                    <p class="ts-done-title">Every student{{ $activeClass ? ' in ' . $activeClass->name : '' }} already has a team</p>
                    <p class="ts-done-sub">All students are already in a team. To change a role or add a student, use that team's Update button.</p>
                    <button type="button" onclick="closeCreateTeamModal()" class="ts-btn-dark">
                        <span class="iconify" data-icon="mdi:arrow-left"></span> Back to Teams
                    </button>
                </div>
            @else
            <div class="px-1 pt-1">
                <p class="ts-lead">
                    <b>{{ $unassignedStudentCount }} {{ Str::plural('student', $unassignedStudentCount) }}</b>
                    {{ $unassignedStudentCount === 1 ? 'is' : 'are' }} not yet assigned to a team{{ $activeClass ? ' in ' . $activeClass->name : '' }}.
                    How would you like to create the teams?
                </p>
                {{-- switchCreateTeamMode() adds shadow-sm to the chosen one; .ts-mode reads that as on. --}}
                <div class="ts-modes">
                    <button type="button" id="createModeSingleBtn" onclick="switchCreateTeamMode('single')"
                        class="ts-mode bg-white text-brand shadow-sm border border-brand/10">
                        <span class="iconify ts-mode-icon" data-icon="mdi:hand-pointing-right"></span>
                        <span class="ts-mode-copy">
                            <span class="ts-mode-title">Choose Students Myself</span>
                            <span class="ts-mode-sub">Create one team: you choose the 4 students and their roles</span>
                        </span>
                    </button>
                    <button type="button" id="createModeMultiBtn" onclick="switchCreateTeamMode('multiple')"
                        class="ts-mode text-slate-500 hover:text-slate-700">
                        <span class="iconify ts-mode-icon" data-icon="mdi:account-group-outline"></span>
                        <span class="ts-mode-copy">
                            <span class="ts-mode-title">Automatically Create Teams</span>
                            <span class="ts-mode-sub">The system puts all students into teams of 4. You can still make changes.</span>
                        </span>
                    </button>
                </div>
            </div>
            @endif
            <div @class(['hidden' => $unassignedStudentCount === 0])>

            <form method="POST" id="createTeamFormSingle" action="{{ route('faculty.role.groups.store') }}">
                @csrf
                <input type="hidden" name="_form_source" value="create_team">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                @if($errors->any() && old('_form_source') === 'create_team')
                    <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="p-5 space-y-4">
                    <div>
                        <label class="ts-label">1. Team Name</label>
                        <input name="group_name" type="text" placeholder="{{ $nextTeamNames[0] ?? 'e.g. Team 1' }}"
                            value="{{ old('_form_source') === 'create_team' ? old('group_name') : ($nextTeamNames[0] ?? '') }}"
                            @disabled($unassignedStudentCount === 0)
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition disabled:opacity-60 disabled:cursor-not-allowed">
                    </div>

                    <div>
                        <p class="ts-label">2. Select 4 Students</p>
                        <p class="ts-help">Check a student, then check the role they will play. Or let the system select 4 students for you.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="relative flex-1">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                            <input type="text" id="createStudentSearch" placeholder="Find a student by name or ID number"
                                @disabled($unassignedStudentCount === 0)
                                class="w-full h-10 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition disabled:opacity-60 disabled:cursor-not-allowed"
                                oninput="filterTeamStudentList('create')">
                        </div>
                        <button type="button" id="createRandomizeBtn"
                            @disabled($unassignedStudentCount === 0)
                            class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:border-brand/40 hover:text-brand transition inline-flex items-center justify-center gap-1.5 shrink-0 disabled:opacity-60 disabled:cursor-not-allowed">
                            <span class="iconify" data-icon="mdi:shuffle-variant"></span> Automatically Select 4 Students
                        </button>
                        <span id="createSelectedCount" class="text-xs font-bold text-slate-500 whitespace-nowrap sm:min-w-[7rem] text-right">0 of 4 Students Selected</span>
                    </div>
                    <p id="createRandomizeNote" class="text-[0.6875rem] text-slate-400 -mt-3"></p>

                    {{-- Who is on the team right now, without scrolling the list below to
                         hunt for ticked boxes. Rendered from the checkboxes themselves, so
                         it follows a hand-made change as closely as it follows Randomize. --}}
                    <div id="createTeamSummary" class="hidden rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <p class="text-[0.625rem] font-bold uppercase tracking-wider text-slate-400 mb-2">Your team so far</p>
                        <div id="createTeamSummaryChips" class="flex flex-wrap gap-1.5"></div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[0.6875rem] font-semibold text-slate-500 mr-1">Each team needs one student for each role:</span>
                        @foreach($teamRoleOptions as $rk => $rl)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-[0.6875rem] font-semibold text-slate-600">
                                <span class="w-2 h-2 rounded-full role-dot-{{ $rk }}"></span>{{ $rl }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Nothing to tick when nobody is unassigned - the banner above already
                         says so, so the empty scroll box collapses rather than repeating it. --}}
                    @if($unassignedStudentCount > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold" style="color: #DC2626;">Students Not Yet Assigned</label>
                            <span class="text-[0.6875rem] text-slate-400">{{ ($students ?? collect())->count() }} to choose from</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="createStudentList">
                                @forelse($students ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->user_information_id;
                                        $selectedMembers = old('_form_source') === 'create_team' ? array_map('intval', old('members', [])) : [];
                                        $selectedRoles = old('_form_source') === 'create_team' ? old('member_roles.' . $sk, []) : [];
                                        if (!is_array($selectedRoles)) $selectedRoles = [$selectedRoles];
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                    @endphp
                                    <div class="team-student-card create-student-card rounded-xl bg-white border border-slate-200 p-3"
                                         data-search="{{ $searchBlob }}"
                                         data-student-name="{{ $dn }}">
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="create-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                {{ in_array($sk, $selectedMembers, true) ? 'checked' : '' }}
                                                onchange="onSingleTeamMemberToggle(this)">
                                            @include('partials.user-avatar', [
                                                'user'         => $u,
                                                'name'         => $dn,
                                                'size'         => 'w-9 h-9',
                                                'rounded'      => 'rounded-lg',
                                                'extraClasses' => 'bg-brand-soft text-brand text-xs font-bold',
                                            ])
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                <p class="text-[0.6875rem] text-slate-400 font-mono">
                                                    #{{ $student->student_number }}
                                                    @if($student->facultyClass)
                                                        · {{ $student->facultyClass->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                        <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                                            @foreach($teamRoleOptions as $rk => $rl)
                                                <label class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-[0.625rem] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="create-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        {{ in_array($rk, $selectedRoles, true) ? 'checked' : '' }}
                                                        onchange="enforceSingleRoleCheckbox(this); refreshRoleAvailability('create')">
                                                    <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $rk }} shrink-0"></span>
                                                    <span class="truncate">{{ $rl }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                        <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                        <p class="text-sm font-semibold">No unassigned students</p>
                                        <p class="text-xs text-slate-300">Add students first, or they may already be in a team.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </form>

            <form method="POST" id="createTeamForm" action="{{ route('faculty.role.groups.store') }}" class="hidden">
                @csrf
                <input type="hidden" name="_form_source" value="create_teams_bulk">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">
                <div id="bulkTeamsHiddenInputs"></div>

                @if($errors->any() && old('_form_source') === 'create_teams_bulk')
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                        <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                        {{ $errors->first() }}
                    </div>
                @endif

                @php $availableStudentCount = ($students ?? collect())->count(); @endphp

                <div class="space-y-4">
                    <div class="setup-step-pair space-y-4">
                    {{-- Step 1 — how many teams, and how big. Both figures are fixed by the
                         four-per-team rule, so they are shown rather than asked for. --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="w-8 h-8 rounded-full brand-gradient text-white text-sm font-bold flex items-center justify-center shrink-0">1</span>
                            <div class="min-w-0">
                                <p class="text-[0.9375rem] font-bold text-slate-800">Number of Teams</p>
                                <p class="text-[0.75rem] text-slate-400">Counted for you from the students not yet assigned to a team.</p>
                            </div>
                        </div>
                        <div class="setup-field-grid">
                            <div>
                                <label class="ts-label">Teams to Create</label>
                                <input type="number" id="bulkTeamCount" min="0" max="40" readonly
                                    value="{{ (int) floor($availableStudentCount / 4) }}"
                                    class="w-full h-11 px-4 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 focus:outline-none cursor-not-allowed">
                                <p class="text-[0.6875rem] text-slate-400 mt-1.5">You don't need to change this.</p>
                            </div>
                            <div>
                                <label class="ts-label">Students per Team</label>
                                <input type="text" value="4" readonly
                                    class="w-full h-11 px-4 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-600 focus:outline-none cursor-not-allowed">
                                <p class="text-[0.6875rem] text-slate-400 mt-1.5">Always 4: one for each role.</p>
                            </div>
                            <div class="rounded-xl border border-brand/15 bg-brand-soft/40 px-4 py-3 flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center shrink-0">
                                    <span class="iconify text-brand text-xl" data-icon="mdi:account-multiple-outline"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[0.75rem] font-bold" style="color: #DC2626;">Students Not Yet Assigned</p>
                                    <p class="text-2xl font-extrabold text-slate-900 leading-none mt-0.5">{{ $availableStudentCount }}</p>
                                    <p class="text-[0.6875rem] text-slate-400">ready to be put into teams</p>
                                </div>
                            </div>
                        </div>
                        <p id="bulkTeamHint" class="text-[0.6875rem] text-slate-400 mt-3">
                            {{ $availableStudentCount }} {{ Str::plural('student', $availableStudentCount) }} not yet assigned{{ $activeClass ? ' in ' . $activeClass->name : '' }}, 4 in each team.
                            @if($availableStudentCount > 0 && $availableStudentCount % 4 !== 0)
                                {{ $availableStudentCount % 4 }} {{ Str::plural('student', $availableStudentCount % 4) }} will be left over until more students join.
                            @endif
                        </p>
                    </div>

                    {{-- Step 2 — the roles a team can hold. Every simulation runs all five,
                         so they are listed rather than picked. --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="w-8 h-8 rounded-full brand-gradient text-white text-sm font-bold flex items-center justify-center shrink-0">2</span>
                            <div class="min-w-0">
                                <p class="text-[0.9375rem] font-bold text-slate-800">The 4 roles in every team</p>
                                <p class="text-[0.75rem] text-slate-400">Each student in a team gets one of these roles. Nothing to choose here.</p>
                            </div>
                        </div>
                        <div class="setup-role-grid">
                            @foreach($teamRoleOptions as $rk => $rl)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full role-dot-{{ $rk }} shrink-0"></span>
                                        <p class="text-[0.8125rem] font-bold text-slate-700 truncate">{{ $rl }}</p>
                                    </div>
                                    <p class="text-[0.6875rem] text-slate-400 mt-1 leading-snug">{{ $roleBlurbs[$rk] ?? 'Hotel simulation role' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    </div>

                    {{-- Step 3 — who lands where. Auto-Group fills every slot; the list
                         below stays editable afterwards. --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="w-8 h-8 rounded-full brand-gradient text-white text-sm font-bold flex items-center justify-center shrink-0">3</span>
                                <div class="min-w-0">
                                    <p class="text-[0.9375rem] font-bold text-slate-800">Assign Students to Teams</p>
                                    <p class="text-[0.75rem] text-slate-400">Press "Automatically Create Teams" to fill every team, then "Shuffle Teams" for a new mix. "Reset to Manual" empties the teams so you can pick each student's team and role below.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" onclick="autoGroupStudentsIntoTeamsOfFour()"
                                    class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:border-brand/40 hover:text-brand transition inline-flex items-center justify-center gap-1.5">
                                    <span class="iconify text-base" data-icon="mdi:shuffle-variant"></span> <span id="bulkAutoGroupLabel">Automatically Create Teams</span>
                                </button>
                                <button type="button" onclick="resetBulkAssignment()"
                                    class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 hover:border-brand/40 hover:text-brand transition">
                                    Reset to Manual
                                </button>
                            </div>
                        </div>

                        <div id="bulkTeamSlots" class="space-y-4"></div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label class="text-xs font-bold text-slate-600">Each student's team and role</label>
                                <div class="relative w-full max-w-xs ml-auto">
                                    <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                                    <input type="text" id="bulkStudentSearch" placeholder="Find a student"
                                        class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                        oninput="filterBulkStudentList()">
                                </div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                                <div class="p-3 space-y-2" id="bulkStudentAssignList">
                                    @forelse($students ?? [] as $student)
                                        @php
                                            $u = $student->user;
                                            $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                            $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                            $sk = $student->user_information_id;
                                            $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                        @endphp
                                        <div class="bulk-student-row rounded-xl bg-white border border-slate-200 p-3"
                                             data-student-id="{{ $sk }}"
                                             data-student-name="{{ $dn }}"
                                             data-search="{{ $searchBlob }}">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                                    @include('partials.user-avatar', [
                                                        'user'         => $u,
                                                        'name'         => $dn,
                                                        'size'         => 'w-9 h-9',
                                                        'rounded'      => 'rounded-lg',
                                                        'extraClasses' => 'bg-brand-soft text-brand text-xs font-bold',
                                                    ])
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $dn }}</p>
                                                        <p class="text-[0.6875rem] text-slate-400 font-mono">
                                                            #{{ $student->student_number }}
                                                            @if($student->facultyClass)
                                                                · {{ $student->facultyClass->name }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <select class="bulk-team-select h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition sm:w-44"
                                                    onchange="onBulkStudentTeamChange(this)">
                                                    <option value="">No team yet</option>
                                                </select>
                                            </div>
                                            <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-4 gap-1.5 bulk-role-grid">
                                                @foreach($teamRoleOptions as $rk => $rl)
                                                    <label class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-[0.625rem] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                        <input type="checkbox" value="{{ $rk }}"
                                                            class="bulk-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                            onchange="enforceSingleRoleCheckbox(this); refreshBulkTeamPreviews()">
                                                        <span class="w-1.5 h-1.5 rounded-full role-dot-{{ $rk }} shrink-0"></span>
                                                        <span class="truncate">{{ $rl }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @empty
                                        <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                            <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                            <p class="text-sm font-semibold">No unassigned students</p>
                                            <p class="text-xs text-slate-300">Add students first, or they may already be in a team.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            </div>
                </div>

                {{-- Right rail: what the save will produce, counted off the same rosters. --}}
                <aside class="space-y-3">
                    {{-- One card in plain words instead of three: who still needs a team,
                         and what pressing the button below will make. --}}
                    <div class="ts-glance">
                        <p class="ts-glance-title">Block Summary{{ $activeClass ? ' · ' . $activeClass->name : '' }}</p>
                        <div class="ts-glance-row">
                            <span style="color: #DC2626;">Students Not Yet Assigned</span>
                            <b>{{ $unassignedStudentCount }}</b>
                        </div>
                        <div class="ts-glance-row">
                            <span>Available Teams to Create</span>
                            <b>{{ (int) floor($unassignedStudentCount / 4) }}</b>
                        </div>
                        <div class="ts-glance-row">
                            <span>Students per Team</span>
                            <b>4</b>
                        </div>
                        <div class="ts-glance-row">
                            <span>Teams Created</span>
                            <b>{{ ($groups ?? collect())->count() }}</b>
                        </div>
                    </div>

                    <div class="ts-tip">
                        <span class="iconify" data-icon="mdi:lightbulb-on-outline"></span>
                        <p>Made a mistake? You can change roles later, and add students to a team that is not full, with that team's <b>Update</b> button.</p>
                    </div>
                </aside>
            </div>
        </div>

        <!-- Screen Footer -->
        {{-- Only when there is someone to put in a team. --}}
        @if($unassignedStudentCount > 0)
        <div class="ts-footer px-5 py-3 bg-white border-t border-slate-200 flex items-center justify-end gap-3">
            <p class="ts-footer-note">Nothing is saved until you press this button.</p>
            <button type="button" onclick="submitActiveModalTab()" id="createModalSubmitBtn"
                class="px-7 py-3 text-white rounded-xl font-bold text-sm hover:opacity-95 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="createModalSubmitLabel">Create</span>
                <span class="iconify text-base" data-icon="mdi:check"></span>
            </button>
        </div>
        @endif
    </div>
</div>



<!-- Update Team Modal -->
<div id="updateTeamModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeUpdateModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h4 class="font-bold text-amber-700 text-base flex items-center gap-2">
                <span class="iconify text-lg" data-icon="mdi:pencil-box-outline"></span>
                Update Team
            </h4>
            <button onclick="closeUpdateModal()" class="text-slate-400 hover:text-amber-500 hover:bg-white w-8 h-8 rounded-full transition flex items-center justify-center">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>

        <!-- Modal Content (scrollable area) -->
        <div class="flex-1 min-h-0 overflow-y-auto">
            <form method="POST" id="updateTeamForm" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_source" value="update_team">
                <input type="hidden" name="class_letter" value="{{ $activeClass->letter ?? '' }}">

                <div class="p-6 space-y-4">
                    {{-- Top row: Team Name + Editing Info --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Team Name <span class="text-red-400">*</span></label>
                            <input id="updateGroupNameInput" name="group_name" type="text" placeholder="Team name"
                                class="w-full h-10 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>

                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 flex items-center gap-3">
                            <span class="iconify text-amber-500 text-xl" data-icon="mdi:information-outline"></span>
                            <div>
                                <p class="text-[0.625rem] font-bold uppercase tracking-wider text-amber-600">Editing</p>
                                <p id="updateTeamEditingLabel" class="text-sm font-bold text-amber-700 truncate">—</p>
                            </div>
                        </div>
                    </div>

                    {{-- Role legend --}}
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2">
                        <div class="flex items-center gap-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Legend:</p>
                            <div class="flex flex-wrap gap-3">
                                @foreach($teamRoleOptions as $rk => $rl)
                                    <div class="flex items-center gap-1.5 text-[0.6875rem] text-slate-600">
                                        <span class="w-2 h-2 rounded-full role-dot-{{ $rk }} flex-shrink-0"></span>{{ $rl }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Members & Roles --}}
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Members & Roles <span class="text-red-400">*</span></label>
                            <span class="text-[0.6875rem] text-slate-400 sm:ml-auto">Current members of this team. Add more under Add Students below.</span>
                        </div>
                        <div class="relative mb-2">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" data-icon="mdi:magnify"></span>
                            <input type="text" id="updateStudentSearch" placeholder="Search members by name or ID..."
                                class="w-full h-10 pl-10 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                                oninput="filterUpdateStudentList()">
                        </div>
                        <p class="text-xs text-slate-400 mb-2">Uncheck to remove a member, or change their roles. Save to apply.</p>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 max-h-[22rem] overflow-y-auto">
                            <div class="p-3 space-y-2" id="updateMemberList">
                                @php
                                    // Unassigned students of this block: the only ones Add Students offers.
                                    $updateAddableIds = ($students ?? collect())->pluck('user_information_id')->map(fn ($id) => (int) $id)->all();
                                @endphp
                                @forelse($allStudents ?? [] as $student)
                                    @php
                                        $u = $student->user;
                                        $dn = trim(implode(' ', array_filter([$u->last_name ?? null, $u->first_name ?? null, $u->middle_name ?? null])));
                                        $dn = $dn !== '' ? $dn : ($u->name ?? 'Student');
                                        $sk = $student->user_information_id;
                                        $searchBlob = strtolower($dn . ' ' . $student->student_number);
                                        $teamNames = collect($studentTeamMap[$sk] ?? [])->values()->all();
                                    @endphp
                                    <div class="student-row update-student-row team-student-card hidden rounded-xl bg-white px-3 py-3 border border-slate-200"
                                         data-student-id="{{ $sk }}"
                                         data-student-name="{{ $dn }}"
                                         data-search="{{ $searchBlob }}"
                                         data-teams="{{ e(implode('|', $teamNames)) }}"
                                         data-addable="{{ in_array((int) $sk, $updateAddableIds, true) ? '1' : '0' }}">
                                        {{-- Student info row --}}
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="members[]" value="{{ $sk }}"
                                                class="update-student-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 shrink-0"
                                                onchange="onUpdateMemberToggle(this)">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <div class="text-sm font-semibold text-slate-700 truncate">{{ $dn }}</div>
                                                    <span class="update-on-team-badge hidden text-[0.625rem] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">On this team</span>
                                                    <span class="update-new-badge hidden text-[0.625rem] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md border">Joining</span>
                                                </div>
                                                <div class="text-[0.6875rem] text-slate-400 font-mono">
                                                    #{{ $student->student_number }}
                                                    @if($student->facultyClass)
                                                        · {{ $student->facultyClass->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Role checkboxes row --}}
                                        <div class="update-role-row flex flex-wrap gap-1.5 mt-2 ml-8">
                                            @foreach(['front_desk' => 'Front Desk', 'restaurant_management' => 'Rest. Mgmt', 'room_management' => 'Room Mgmt', 'housekeeping' => 'Housekeeping/Maint.'] as $rk => $rl)
                                                <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 text-[0.625rem] font-semibold text-slate-600 cursor-pointer hover:border-brand/40 hover:bg-brand-soft/50 transition has-[:checked]:border-brand has-[:checked]:bg-brand-soft has-[:checked]:text-brand">
                                                    <input type="checkbox" name="member_roles[{{ $sk }}][]" value="{{ $rk }}"
                                                        class="update-role-checkbox rounded border-slate-300 text-brand focus:ring-brand/30 w-3 h-3"
                                                        data-student-id="{{ $sk }}"
                                                        onchange="onUpdateRoleToggle(this)">
                                                    {{ $rl }}
                                                </label>
                                            @endforeach
                                        </div>
                                        {{-- Filled by onUpdateRoleToggle() when the picked role is someone else's. --}}
                                        <p class="update-role-warning hidden mt-2 ml-8 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[0.6875rem] font-semibold text-amber-700 leading-snug" role="alert"></p>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center gap-2 py-10 text-slate-400">
                                        <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-off-outline"></span>
                                        <p class="text-sm font-semibold">No students found</p>
                                        <p class="text-xs text-slate-300">Add students under Manage Students first.</p>
                                    </div>
                                @endforelse
                                <div id="updateNoMembersNote" class="hidden flex flex-col items-center gap-2 py-10 text-slate-400">
                                    <span class="iconify text-3xl text-slate-200" data-icon="mdi:account-group-outline"></span>
                                    <p class="text-sm font-semibold">No members on this team</p>
                                    <p class="text-xs text-slate-300">Add students under Add Students below.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Add Students. The one place a student joins an existing team:
                         openUpdateModal() moves this block's unassigned students in here,
                         and refreshUpdateCapacity() locks the list at 4 members. --}}
                    <div class="ut-add" id="updateAddSection">
                        <div class="ut-add-head">
                            <div class="min-w-0">
                                <p class="ut-add-title"><span class="iconify text-base" data-icon="mdi:account-plus-outline"></span> Add Students</p>
                                <p class="ut-add-sub">Students in this block who are not on a team yet. Tick one to add them, then choose their role.</p>
                            </div>
                            <span class="ut-cap" id="updateCapacityBadge">0 of 4 members</span>
                        </div>
                        <div class="ut-bar" aria-hidden="true"><span id="updateCapacityBar"></span></div>
                        <div class="ut-full hidden" id="updateFullNote" role="status">
                            <span class="iconify text-base shrink-0" data-icon="mdi:lock-outline"></span>
                            This team is already full. Maximum of 4 members allowed.
                        </div>
                        <div class="ut-add-list" id="updateAddList"></div>
                        <p class="ut-empty hidden" id="updateNoAddableNote">Every student in this block is already on a team. New students are enrolled under Manage Students.</p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer (always visible, fixed at bottom) -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 flex-shrink-0">
            <button type="button" onclick="closeUpdateModal()" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-200 transition font-semibold text-sm">Cancel</button>
            <button type="button" onclick="document.getElementById('updateTeamForm').submit()" class="hover-green px-6 py-2.5 bg-amber-500 text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-amber-400/30 flex items-center gap-2">
                <span class="iconify" data-icon="mdi:content-save-edit-outline"></span> Save Changes
            </button>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════
     TAB 3 — CREATE TASK
═══════════════════════════════════════════════ --}}
<div id="panel-create_task" class="tab-panel {{ $activeTab === 'create_task' ? 'active' : '' }}">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- The block tabs matter here: the team picker lists this block's teams, so
             faculty need to switch blocks without leaving the form. --}}
        @include('faculty.partials.teams-subnav', [
            'teamsSubTab' => $activeTab,
            'groups' => $groups ?? [],
            'classes' => $classes ?? collect(),
            'activeClass' => $activeClass ?? null,
            'classCapacity' => $classCapacity ?? 40,
            'teamCountsByClass' => $teamCountsByClass ?? [],
        ])

        {{-- Header --}}
        <div class="p-5 border-b border-slate-100 bg-brand-soft flex items-center gap-3">
            <div class="w-10 h-10 bg-brand/10 rounded-xl flex items-center justify-center">
                <span class="iconify text-brand text-xl" data-icon="mdi:clipboard-plus-outline"></span>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-brand text-base">Set New Task</h3>
                <p class="text-xs text-slate-400 mt-0.5">Pick a team, tick the tasks, set a due date, and assign - all on one page.</p>
            </div>
            {{-- The way out. Same button the Team Setup screen carries, in the same
                 corner, because this screen is reached the same way and had no way
                 back at all. It asks first when there is a half-filled form to lose. --}}
            <button type="button" onclick="leaveSetTaskScreen()"
                class="ml-auto h-10 px-4 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-bold hover:bg-slate-50 transition inline-flex items-center gap-2 shrink-0">
                <span class="iconify text-base" data-icon="mdi:arrow-left"></span>
                Back to Teams
            </button>
        </div>

        <form method="POST" action="{{ route('faculty.tasks.store') }}" id="taskAssignForm">
            @csrf
            <input type="hidden" name="_form_source" value="create_task">
            {{-- Rides along so the redirect lands back on the block being worked in. --}}
            <input type="hidden" name="class" value="{{ request('class', $activeClass->letter ?? '') }}">

            @if($errors->any() && $activeTab === 'create_task')
                <div class="mx-6 mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
                    <span class="iconify flex-shrink-0" data-icon="mdi:alert-circle-outline"></span>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="p-6 space-y-6">
                {{-- The assignable checklist comes from App\Support\TaskChecklist and is
                     passed in as $taskChecklist. It used to be declared here as
                     $tasksByRole, which shadowed the controller's real task rows for
                     everything below it. --}}

                {{-- ═══════ TEAMS ═══════
                     A checklist step is written for the course rather than for one
                     team, so it can be handed to any number of them at once. The
                     checkboxes are real form fields, unlike the department, which
                     reaches the server as the tasks[] key. --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-brand text-white text-[0.6875rem] font-bold flex items-center justify-center shrink-0">1</span>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-slate-700">Which teams is this task for?</h4>
                            <p class="text-xs text-slate-400">Pick one, several, or every team in this block</p>
                        </div>
                        @unless(($groups ?? collect())->isEmpty())
                            <label class="ml-auto shrink-0 inline-flex items-center gap-2 h-9 px-3 rounded-xl border border-slate-200 bg-white text-[0.75rem] font-bold text-slate-600 hover:border-brand/40 cursor-pointer transition">
                                <input type="checkbox" id="taskAllTeams" onchange="toggleAllTeams(this.checked)"
                                       class="rounded border-slate-300 text-brand focus:ring-brand/30">
                                All teams
                            </label>
                        @endunless
                    </div>

                    @if(($groups ?? collect())->isEmpty())
                        <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                            <span class="iconify text-3xl text-slate-300" data-icon="mdi:account-group-outline"></span>
                            <p class="text-sm font-bold text-slate-500 mt-2">No teams in this block yet</p>
                            <p class="text-xs text-slate-400 mt-1">Build a team first, then come back to set its tasks.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="taskTeamSelector">
                            @foreach($groups as $groupName => $members)
                                @php
                                    /* Members who are still students. A row whose student was
                                       removed leaves the team standing with nobody in it, and
                                       a task set on it is a row nobody will ever see - the
                                       server drops such teams too, so this is what the faculty
                                       sees rather than the only thing stopping it. */
                                    $liveMembers = $members->filter(fn ($member) => $member->student !== null);
                                    $hasStudents = $liveMembers->isNotEmpty();

                                    // Same five-role count the Role Assignment Indicator on
                                    // Manage Teams reads, so the two never disagree about a team.
                                    $teamAssignedRoles = $members->flatMap(fn ($m) => $m->roles->pluck('role'))->filter()->unique();
                                    $teamMissingRoleCount = count($roleLabels) - $teamAssignedRoles->count();

                                    /* Task 01 gates the rest: they open once Front Desk has
                                       submitted the concept (storeTask checks the same).
                                       Read off the concepts already loaded for the cards
                                       rather than asking the database again per team. */
                                    $teamConceptSubmitted = \App\Support\HotelConceptDesk::isSubmitted(
                                        ($conceptsByGroup ?? collect())->get($groupName, collect())
                                    );
                                @endphp
                                <label data-team="{{ $groupName }}"
                                    data-concept-submitted="{{ $teamConceptSubmitted ? '1' : '0' }}"
                                    @if(!$hasStudents) title="No students are assigned to {{ $groupName }} yet." @endif
                                    class="task-team-btn group p-4 rounded-xl border-2 transition-all flex items-start gap-3 text-left
                                    {{ $hasStudents
                                        ? 'border-slate-200 bg-white hover:border-brand/40 hover:shadow-md cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-brand-soft'
                                        : 'border-slate-200 border-dashed bg-slate-50 cursor-not-allowed opacity-70' }}">
                                    <input type="checkbox" name="group_names[]" value="{{ $groupName }}"
                                        onchange="onTeamToggled()"
                                        @disabled(!$hasStudents)
                                        {{ $hasStudents && in_array($groupName, (array) old('group_names', []), true) ? 'checked' : '' }}
                                        class="mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30 task-team-radio disabled:cursor-not-allowed">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-bold truncate {{ $hasStudents ? 'text-slate-700' : 'text-slate-400' }}">{{ $groupName }}</span>
                                        <span class="block text-[0.625rem] mt-0.5 {{ $hasStudents ? 'text-slate-400' : 'text-amber-600 font-semibold' }}">
                                            @if($hasStudents)
                                                {{ $liveMembers->count() }} {{ Str::plural('member', $liveMembers->count()) }}
                                            @else
                                                No students assigned
                                            @endif
                                        </span>
                                        @if($hasStudents && $teamMissingRoleCount > 0)
                                            <span class="block text-[0.625rem] mt-1 text-amber-600 font-semibold leading-snug">
                                                This team still has an unassigned role. Assign all required roles before setting role-specific tasks.
                                            </span>
                                        @endif
                                        @if($hasStudents && !$teamConceptSubmitted)
                                            <span class="block text-[0.625rem] mt-1 text-amber-600 font-semibold leading-snug">
                                                {{ \App\Support\HotelConceptDesk::TASK_TITLE }} only &mdash; the rest of the customization unlocks once Front Desk submits this team's hotel concept.
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ═══════ TASKS ═══════
                     One card per numbered task, and one task per department: TASK 01
                     is Front Desk's website customization, TASK 02 Room Management's,
                     and so on. Ticking a card's header hands every activity in it to
                     the team in one submit. Simulation work is not assigned here.

                     tasks[role][] carries each activity's position in that role's
                     list, and storeTask() reads the title back by the same key. --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-brand text-white text-[0.6875rem] font-bold flex items-center justify-center shrink-0">2</span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-700">Which tasks are you setting?</h4>
                                <p class="text-xs text-slate-400">Tick a task to give that department all of its website customization</p>
                            </div>
                        </div>
                        <button type="button" id="selectAllTasksBtn" onclick="selectAllVisibleTasks()" class="text-xs font-semibold text-brand hover:underline">
                            Select All
                        </button>
                    </div>

                    {{-- Why cards are greyed out, when they are — an unapproved concept
                         gating Task 02+, or work the picked teams already hold. Filled
                         by applyStepLocks() as teams are ticked. --}}
                    <p id="taskConceptLockNote"
                       class="hidden mb-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-[0.6875rem] font-semibold text-amber-700 leading-snug"></p>

                    {{-- Search + category filter, purely client-side over the cards below. --}}
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <div class="relative flex-1 min-w-[11.25rem]">
                            <span class="iconify absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm" data-icon="mdi:magnify"></span>
                            <input type="text" id="taskSearchInput" oninput="filterTaskCards()" placeholder="Search tasks..."
                                class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap" id="taskCategoryTabs">
                            <button type="button" data-filter="all" onclick="filterTaskCards('all')"
                                class="task-filter-tab px-2.5 py-1 rounded-lg text-xs font-bold transition border border-slate-200 hover:border-brand/40">All</button>
                            @foreach(collect($rolesMeta ?? [])->only(\App\Support\TaskChecklist::STEP_ROLES) as $rKey => $rMeta)
                                <button type="button" data-filter="{{ $rKey }}" onclick="filterTaskCards('{{ $rKey }}')"
                                    class="task-filter-tab px-2.5 py-1 rounded-lg text-xs font-bold transition border border-slate-200 hover:border-brand/40">{{ $rMeta['label'] }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- One card per numbered task, one department each. Ticking a card's
                         own box hands every activity in it to the team; View Details opens
                         the modal below to tick or read them one at a time. --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 max-h-[25rem] overflow-y-auto pr-1" id="taskCardGrid">
                        @foreach($taskSteps ?? [] as $step => $stepData)
                            @php
                                $stepRole = $stepData['role'];
                                $stepTasks = $stepData['tasks'];
                                $leadMeta = $rolesMeta[$stepRole] ?? null;
                                $searchBlob = Str::lower('task ' . ($step + 1) . ' ' . $stepData['label'] . ' ' . collect($stepTasks)->pluck('title')->implode(' '));
                            @endphp
                            <div id="taskCard-{{ $step }}" data-categories="{{ $stepRole }}" data-search="{{ $searchBlob }}"
                                class="task-card rounded-xl border-2 border-slate-200 bg-white p-3.5 hover:border-brand/40 hover:shadow-md transition-all flex flex-col gap-2">
                                <div class="flex items-start justify-between">
                                    <input type="checkbox" id="taskGroupCheck-{{ $step }}"
                                        onchange="toggleTaskGroupAll({{ $step }})"
                                        class="task-group-check mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30">
                                    @if($leadMeta)
                                        <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                            <span class="iconify {{ $leadMeta['color'] }}" data-icon="{{ $leadMeta['icon'] }}"></span>
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">TASK {{ str_pad($step + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                    <p class="text-[0.6875rem] font-semibold text-slate-600 leading-snug">{{ $stepData['label'] }}</p>
                                    <p class="text-[0.6875rem] text-slate-400 mt-0.5">{{ count($stepTasks) }} {{ Str::plural('Activity', count($stepTasks)) }}</p>
                                </div>
                                <button type="button" onclick="openTaskDetailsModal({{ $step }})"
                                    class="text-xs font-semibold text-brand hover:underline text-left flex items-center gap-1">
                                    View Details <span class="iconify text-sm" data-icon="mdi:arrow-right"></span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- One modal per task: its activities, opened from a card's View
                     Details button. --}}
                @foreach($taskSteps ?? [] as $step => $stepData)
                    @php
                        $rKey = $stepData['role'];
                        $rMeta = $rolesMeta[$rKey] ?? ['label' => $stepData['label'], 'icon' => 'mdi:clipboard-text-outline', 'color' => 'text-slate-500'];
                        $stepTasks = $stepData['tasks'];
                    @endphp
                    <div id="taskDetailsModal-{{ $step }}" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeTaskDetailsModal({{ $step }})"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-h-[90vh] flex flex-col" style="max-width: 32rem;">
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center rounded-t-2xl flex-shrink-0">
                                <div class="min-w-0">
                                    <p class="text-[0.625rem] font-bold uppercase tracking-wider text-slate-400">TASK {{ str_pad($step + 1, 2, '0', STR_PAD_LEFT) }} &middot; {{ count($stepTasks) }} {{ Str::plural('Activity', count($stepTasks)) }}</p>
                                    <h4 class="font-bold text-brand text-base truncate">{{ $stepData['label'] }}</h4>
                                </div>
                                <button type="button" onclick="closeTaskDetailsModal({{ $step }})"
                                    class="text-slate-400 hover:text-brand hover:bg-slate-50 w-7 h-7 rounded-full transition flex items-center justify-center shrink-0">
                                    <span class="iconify text-lg" data-icon="mdi:close"></span>
                                </button>
                            </div>

                            <div class="overflow-y-auto flex-1 divide-y divide-slate-100">
                                @foreach($stepTasks as $taskIndex => $task)
                                    @php $isConceptTask = \App\Support\TaskChecklist::isConceptTitle($task['title']); @endphp
                                    <label class="task-checkbox-card flex items-start gap-3 p-3.5 hover:bg-slate-50 transition cursor-pointer">
                                        <input type="checkbox" name="tasks[{{ $rKey }}][]" value="{{ $taskIndex }}"
                                            data-group="{{ $step }}" data-role="{{ $rKey }}"
                                            data-title="{{ Str::lower($task['title']) }}"
                                            data-concept="{{ $isConceptTask ? '1' : '0' }}"
                                            class="task-check task-step-check mt-0.5 rounded border-slate-300 text-brand focus:ring-brand/30">
                                        <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                            <span class="iconify {{ $rMeta['color'] }}" data-icon="{{ $isConceptTask ? 'mdi:lightbulb-outline' : $rMeta['icon'] }}"></span>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-bold text-slate-800">{{ $task['title'] }}</span>
                                                @if($isConceptTask)
                                                    {{-- The brief the rest of the customization follows. --}}
                                                    <span class="shrink-0 px-1.5 py-0.5 rounded-full border border-slate-200 text-[0.5625rem] font-bold uppercase tracking-wide bg-amber-50 text-amber-700">
                                                        Hotel Concept
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $task['summary'] ?? $task['description'] }}</p>
                                            @if(!empty($task['activities']))
                                                {{-- One activity is one role's share of the task. These are the
                                                     steps the student holding it works through, and the line
                                                     under them is what the work has to show before it is
                                                     handed in. --}}
                                                <p class="text-[0.5625rem] font-bold uppercase tracking-wider text-slate-400 mt-2">Steps</p>
                                                <ol class="mt-1 space-y-1 list-decimal list-inside">
                                                    @foreach($task['activities'] as $activity)
                                                        <li class="text-[0.6875rem] text-slate-500 leading-relaxed">{{ $activity }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                            @if(!empty($task['completion']))
                                                <p class="text-[0.6875rem] text-slate-500 mt-2">
                                                    <span class="font-bold uppercase tracking-wider text-[0.5625rem] text-slate-400">Done when</span>
                                                    <span class="block mt-0.5">{{ $task['completion'] }}</span>
                                                </p>
                                            @endif
                                            {{-- The headcount hint the department step used to carry. Filled by
                                                 updateRoleMemberCounts() once a team is picked. --}}
                                            <p class="text-[0.6875rem] font-semibold text-slate-400 mt-1">
                                                {{ $rMeta['label'] }}<span data-role-count="{{ $rKey }}"></span>
                                            </p>
                                        </div>
                                        <input type="hidden" name="task_titles[{{ $rKey }}][{{ $taskIndex }}]" value="{{ $task['title'] }}">
                                        <input type="hidden" name="task_descriptions[{{ $rKey }}][{{ $taskIndex }}]" value="{{ $task['description'] }}">
                                    </label>
                                @endforeach
                            </div>

                            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between rounded-b-2xl flex-shrink-0">
                                <p class="text-[0.6875rem] text-slate-400">Total Activities <span class="font-bold text-slate-600">{{ count($stepTasks) }}</span></p>
                                <button type="button" onclick="closeTaskDetailsModal({{ $step }})"
                                    class="px-4 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white transition">Close</button>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- ═══════ DUE DATE ═══════ --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-500 text-[0.6875rem] font-bold flex items-center justify-center shrink-0">3</span>
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Due date and time</h4>
                            <p class="text-xs text-slate-400">Optional - leave blank for no deadline</p>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 iconify text-slate-400" data-icon="mdi:calendar-clock"></span>
                            <input name="due_date" type="datetime-local" value="{{ old('due_date') }}"
                                class="w-full h-12 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition">
                        </div>
                        <p class="text-[0.6875rem] text-slate-400 mt-1.5">Students see this deadline in their own task list.</p>
                    </div>
                </div>

                {{-- ═══════ SUMMARY + ASSIGN ═══════
                     What the review step used to say, kept live at the foot of the form
                     so nothing has to be paged through to check it. --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 min-w-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="iconify text-emerald-500 shrink-0" data-icon="mdi:account-group-outline"></span>
                            <span class="text-xs text-slate-400 font-medium">Team</span>
                            <span class="text-sm font-bold text-slate-800 truncate" id="reviewTeamName">—</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="iconify text-brand shrink-0" data-icon="mdi:clipboard-text-outline"></span>
                            <span class="text-sm font-bold text-slate-800" id="reviewTaskCount">0 tasks</span>
                            <span class="text-xs text-slate-400" id="reviewDeptName">—</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="iconify text-blue-500 shrink-0" data-icon="mdi:calendar-outline"></span>
                            <span class="text-sm font-bold text-slate-800" id="reviewDueDate">No deadline</span>
                        </div>
                    </div>

                    <button type="submit" id="submitTasksBtn"
                        class="px-6 py-2.5 bg-brand text-white rounded-xl font-bold text-sm hover:scale-105 transition shadow-md shadow-brand/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100" disabled>
                        <span class="iconify" data-icon="mdi:check-circle-outline"></span> Set Tasks
                    </button>
                </div>


                {{-- ═══════ ACTIVE TASKS OVERVIEW ═══════ --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                    <p class="text-[0.625rem] font-bold uppercase tracking-wider text-slate-400 mb-2">Active Tasks</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rolesMeta ?? [] as $rKey => $rMeta)
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-slate-100">
                                <span class="iconify text-xs {{ $rMeta['color'] }}" data-icon="{{ $rMeta['icon'] }}"></span>
                                <span class="text-[0.625rem] font-bold text-slate-600">{{ $taskCounts[$rKey] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="resetTaskWizard()" class="ml-auto text-xs font-semibold text-slate-400 hover:text-slate-600 transition">Reset</button>
            </div>
        </form>
    </div>
</div>


{{-- ═══════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════ --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
// ── Tab switcher ────────────────────────────────
const ALL_TABS = ['teams', 'create_task'];

function switchTab(tabId) {
    ALL_TABS.forEach(id => {
        const panel = document.getElementById('panel-' + id);
        const btn   = document.getElementById('tab-btn-' + id);
        if (!panel || !btn) return;
        if (id === tabId) {
            panel.classList.add('active');
            btn.classList.add('active');
        } else {
            panel.classList.remove('active');
            btn.classList.remove('active');
        }
    });
    // Update browser URL without reloading
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    history.replaceState(null, '', url);
}

// ── Create Team Modal ──────────────────────────
const TEAM_MEMBER_MAX = 4;
let currentCreateTeamMode = 'single';

function setTeamsActionHighlight(activeKey) {
    document.querySelectorAll('.teams-action-btn').forEach((btn) => {
        const isActive = activeKey != null && btn.getAttribute('data-action-btn') === activeKey;
        btn.classList.toggle('is-active', isActive);
        btn.classList.toggle('bg-brand', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('shadow-md', isActive);
        btn.classList.toggle('shadow-brand/20', isActive);
        btn.classList.toggle('border-transparent', isActive);
        btn.classList.toggle('bg-white', !isActive);
        btn.classList.toggle('text-slate-600', !isActive);
        btn.classList.toggle('border-slate-200', !isActive);
        btn.classList.toggle('hover:border-brand/40', !isActive);
        btn.classList.toggle('hover:text-brand', !isActive);
        btn.classList.add('border');
    });
}

/* Team Setup is its own screen rather than a dialog, so opening and closing it
   is navigation. Both keep the class the faculty is looking at. */
@php
    // Built here rather than inside @json: the directive's argument parser cannot
    // read a multi-line array literal.
    $setupScreenQuery = array_filter(['class' => $activeClass->letter ?? null]);
    $teamSetupUrl = route('faculty.role', $setupScreenQuery + ['tab' => 'team_setup']);
    $teamsListUrl = route('faculty.role', $setupScreenQuery + ['tab' => 'teams']);
@endphp
const TEAM_SETUP_URL = @json($teamSetupUrl);
const TEAMS_LIST_URL = @json($teamsListUrl);

function openCreateTeamModal() {
    window.location.href = TEAM_SETUP_URL;
}

function closeCreateTeamModal() {
    window.location.href = TEAMS_LIST_URL;
}

/**
 * Leaving Set Task.
 *
 * The form is not saved anywhere until Set Tasks is pressed, so walking away
 * with tasks ticked or a deadline typed loses them. It only asks when there is
 * something to lose: a faculty who opened the screen and thought better of it
 * should not have to answer for it.
 */
function setTaskFormHasEntries() {
    const form = document.getElementById('taskAssignForm');
    if (!form) return false;

    if (form.querySelector('.task-check:checked')) return true;

    const due = form.querySelector('input[name="due_date"]');
    if (due && due.value) return true;

    // A team picked on its own is not work to lose - it is one click, and the
    // screen reopens on it anyway.
    return false;
}

function leaveSetTaskScreen() {
    if (setTaskFormHasEntries()
        && !window.confirm('Your unsaved task selections will be lost. Do you want to continue?')) {
        return;
    }

    window.location.href = TEAMS_LIST_URL;
}

// Same guard, for the block tabs above the wizard: switching block from Set
// Task also leaves the form behind, so it asks the same question before the
// tab's own href is allowed to navigate.
function confirmLeaveSetTaskForBlockSwitch() {
    return !setTaskFormHasEntries()
        || window.confirm('Your unsaved task selections will be lost. Do you want to continue?');
}

/* Everything opening the screen used to do, run on load instead. */
function initTeamSetupScreen() {
    switchCreateTeamMode(currentCreateTeamMode);
    if (currentCreateTeamMode === 'multiple') {
        autoGroupStudentsIntoTeamsOfFour();
    } else {
        updateTeamSelectedCount('create');
    }
}

function switchCreateTeamMode(mode) {
    currentCreateTeamMode = mode === 'multiple' ? 'multiple' : 'single';
    const singleForm = document.getElementById('createTeamFormSingle');
    const multiForm = document.getElementById('createTeamForm');
    const singleBtn = document.getElementById('createModeSingleBtn');
    const multiBtn = document.getElementById('createModeMultiBtn');

    if (singleForm) singleForm.classList.toggle('hidden', currentCreateTeamMode !== 'single');
    if (multiForm) multiForm.classList.toggle('hidden', currentCreateTeamMode !== 'multiple');

    const activeCls = ['bg-white', 'text-brand', 'shadow-sm', 'border', 'border-brand/10'];
    const idleCls = ['text-slate-500', 'hover:text-slate-700'];

    function styleModeBtn(btn, active) {
        if (!btn) return;
        activeCls.forEach((c) => btn.classList.toggle(c, active));
        idleCls.forEach((c) => btn.classList.toggle(c, !active));
        if (!active) btn.classList.remove('border', 'border-brand/10');
    }

    styleModeBtn(singleBtn, currentCreateTeamMode === 'single');
    styleModeBtn(multiBtn, currentCreateTeamMode === 'multiple');

    if (currentCreateTeamMode === 'multiple') {
        rebuildBulkTeamSlots(maxCompleteBulkTeams());
    } else {
        updateTeamSelectedCount('create');
    }
}

// ── Modal Tab Switcher ────────────────────────
let currentModalTab = 'add_team';

/* ── Review a submission: see the work, then give feedback ───────────────── */
const TASK_REVIEW_URL = @json(route('faculty.tasks.review', ['task' => '__ID__']));
const TASK_FEEDBACK_URL = @json(route('faculty.tasks.feedback', ['task' => '__ID__']));
let reviewTaskId = null;
// The two sides of the Before/After comparison for the open task.
let reviewPreviewUrls = { before: null, after: null };
// The Changes list for the open task — rendered into #reviewChangesPane.
let reviewChanges = [];

const REVIEW_CHANGE_STYLES = {
    added: { border: 'border-emerald-400', chip: 'bg-emerald-50 text-emerald-700 border-emerald-200', label: 'Added' },
    modified: { border: 'border-amber-400', chip: 'bg-amber-50 text-amber-700 border-amber-200', label: 'Changed' },
    removed: { border: 'border-rose-400', chip: 'bg-rose-50 text-rose-700 border-rose-200', label: 'Removed' },
};

/* One row per change, grouped by page. Clicking a row jumps the After preview
   to that element and pulses it — see the postMessage listener below and the
   'hms-diff-focus' handler in hms-review-highlight.js. */
function renderReviewChanges(changes) {
    const pane = document.getElementById('reviewChangesPane');
    if (!changes.length) {
        pane.innerHTML = '<p class="text-xs text-slate-400 text-center py-8">No changes to show for this submission.</p>';
        return;
    }

    const byPage = {};
    changes.forEach(function (c) {
        const page = c.page || 'home';
        (byPage[page] = byPage[page] || []).push(c);
    });

    let html = '';
    Object.keys(byPage).forEach(function (page) {
        html += '<p class="text-[0.625rem] font-bold uppercase tracking-wider text-slate-400 mt-3 mb-1.5 first:mt-0">' + escHtml(page) + '</p>';
        byPage[page].forEach(function (c) {
            const style = REVIEW_CHANGE_STYLES[c.type] || REVIEW_CHANGE_STYLES.modified;
            const clickableAttr = c.key ? ' data-review-change-key="' + escHtml(c.key) + '"' : '';
            const clickableClass = c.key ? ' cursor-pointer hover:bg-slate-50' : '';
            html += '<div class="border-l-2 ' + style.border + ' rounded-r-lg bg-white px-2.5 py-2 mb-1.5' + clickableClass + '"' + clickableAttr + '>'
                + '<div class="flex items-center justify-between gap-2">'
                + '<span class="text-xs font-semibold text-slate-700 truncate">' + escHtml(c.label || '') + '</span>'
                + '<span class="shrink-0 px-1.5 py-0.5 rounded-full border text-[0.5625rem] font-bold ' + style.chip + '">' + style.label + '</span>'
                + '</div>';
            (c.fields || []).forEach(function (f) {
                html += '<p class="text-[0.6875rem] text-slate-500 mt-1"><span class="font-semibold text-slate-600">' + escHtml(f.label) + ':</span> ';
                if (f.from != null && f.to != null) {
                    html += '<span class="line-through text-slate-400">' + escHtml(f.from) + '</span> <span class="text-slate-400">to</span> <span class="text-slate-700">' + escHtml(f.to) + '</span>';
                } else if (f.to != null) {
                    html += '<span class="text-slate-700">' + escHtml(f.to) + '</span>';
                } else if (f.from != null) {
                    html += '<span class="line-through text-slate-400">' + escHtml(f.from) + '</span>';
                }
                html += '</p>';
            });
            html += '</div>';
        });
    });
    pane.innerHTML = html;
}

document.addEventListener('click', function (e) {
    const row = e.target.closest ? e.target.closest('[data-review-change-key]') : null;
    if (!row) return;
    const key = row.getAttribute('data-review-change-key');
    setReviewCompareSide('after');
    const frame = document.getElementById('reviewPreviewFrame');
    try {
        frame.contentWindow.postMessage({ type: 'hms-diff-focus', key: key }, '*');
    } catch (err) { /* ignore */ }
});

/* The feedback box belongs to Revise alone, so it is revealed only on demand. */
function showReviseStep() {
    document.getElementById('reviewChoiceStep').classList.add('hidden');
    document.getElementById('reviewReviseStep').classList.remove('hidden');
    document.getElementById('reviewError').classList.add('hidden');
    document.getElementById('reviewFeedback').focus();
}

function hideReviseStep() {
    document.getElementById('reviewChoiceStep').classList.remove('hidden');
    document.getElementById('reviewReviseStep').classList.add('hidden');
    document.getElementById('reviewFeedback').value = '';
    document.getElementById('reviewError').classList.add('hidden');
}

/* Which side the iframe is showing, so the outlines go onto After only. */
let reviewFrameSide = 'after';
// The branding and Home Page tasks: After also outlines whatever differs from
// the stock template. 'branding', 'home' or null.
let reviewStockCheck = null;

/* Hand the After preview the Changes list this panel is showing, so it can
   outline each one. The preview used to work the list out again on its own and
   came up with nothing for some submissions; this copy is the one faculty see. */
let reviewHighlightTimer = null;

function setReviewHighlightStatus(text, tone) {
    const el = document.getElementById('reviewHighlightStatus');
    if (!el) return;
    el.textContent = text || '';
    el.classList.toggle('hidden', !text);
    el.style.color = tone === 'ok' ? '#16a34a' : (tone === 'warn' ? '#b45309' : '#64748b');
}

/* The preview answers with how many of the changes it outlined. */
window.addEventListener('message', function (e) {
    const frame = document.getElementById('reviewPreviewFrame');
    if (!frame || e.source !== frame.contentWindow || !e.data) return;
    if (e.data.type === 'hms-review-highlight-ready') {
        postReviewHighlight();
    } else if (e.data.type === 'hms-review-highlight-status' && reviewFrameSide === 'after') {
        clearTimeout(reviewHighlightTimer);
        const n = Number(e.data.outlined) || 0;
        const total = Number(e.data.total) || 0;
        if (n > 0) {
            setReviewHighlightStatus('● ' + n + (n === 1 ? ' change' : ' changes') + ' outlined in green', 'ok');
        } else if (total > 0) {
            setReviewHighlightStatus('None of the changes are on this page', 'muted');
        }
    }
});

function postReviewHighlight() {
    if (reviewFrameSide !== 'after') return;
    const frame = document.getElementById('reviewPreviewFrame');
    if (!frame || !frame.contentWindow) return;
    const payload = { type: 'hms-review-highlight', added: [], modified: [], stock_review: reviewStockCheck };
    reviewChanges.forEach(function (c) {
        if (!c || !c.key || (c.type !== 'added' && c.type !== 'modified')) return;
        payload[c.type].push({ key: String(c.key), hms_id: c.hms_id || null, page: c.page || 'home' });
    });
    try {
        // '*': the preview may be served from APP_URL's spelling of this host, and
        // a strict origin drops the message silently. Selectors are not secret.
        frame.contentWindow.postMessage(payload, '*');
    } catch (err) { /* ignore */ }
}

function onReviewFrameLoad() {
    clearTimeout(reviewHighlightTimer);
    if (reviewFrameSide !== 'after') {
        setReviewHighlightStatus('', null);
        return;
    }
    const hasOutlinable = reviewChanges.some(function (c) {
        return c && c.key && (c.type === 'added' || c.type === 'modified');
    });
    if (!hasOutlinable && !reviewStockCheck) {
        setReviewHighlightStatus(reviewChanges.length ? 'These changes have no single spot to outline' : '', 'muted');
        return;
    }
    setReviewHighlightStatus('Outlining changes…', 'muted');
    postReviewHighlight();
    reviewHighlightTimer = setTimeout(function () {
        setReviewHighlightStatus('Outlines did not load — refresh the page', 'warn');
    }, 15000);
}

(function bindReviewFrameLoad() {
    const frame = document.getElementById('reviewPreviewFrame');
    if (frame) frame.addEventListener('load', onReviewFrameLoad);
    else document.addEventListener('DOMContentLoaded', bindReviewFrameLoad);
})();

/* Before/After share one iframe: a full hotel site needs the width. Changes
   swaps to the list pane instead — there is nothing to render for it. */
function setReviewCompareSide(side) {
    document.querySelectorAll('#reviewCompareToggle [data-compare]').forEach(function (btn) {
        btn.classList.toggle('is-on', btn.getAttribute('data-compare') === side);
    });

    const frame = document.getElementById('reviewPreviewFrame');
    const changesPane = document.getElementById('reviewChangesPane');

    if (side === 'changes') {
        setReviewHighlightStatus('', null);
        frame.classList.add('hidden');
        changesPane.classList.remove('hidden');
        document.getElementById('reviewWorkLabel').textContent = 'Highlighted changes';
        setReviewWorkHint('');
        return;
    }

    const url = reviewPreviewUrls[side];
    if (!url) return;
    changesPane.classList.add('hidden');
    frame.classList.remove('hidden');
    reviewFrameSide = side;
    frame.src = url;
    document.getElementById('reviewOpenTab').href = url;
    document.getElementById('reviewWorkLabel').textContent = side === 'before'
        ? 'Before: the site when this task was given'
        : 'After: the site the student handed in';
    setReviewWorkHint(side === 'before'
        ? 'Switch to After to see what the student changed.'
        : 'What the student changed is outlined in green.');
}

/* The plain-language line under the work pane's title. */
function setReviewWorkHint(text) {
    const el = document.getElementById('reviewWorkHint');
    if (el) el.textContent = text || '';
}

/* A labelled chip for the dialog header: who, which team, which department. */
function reviewMetaChip(icon, label) {
    return '<span class="tm-chip"><span class="iconify" data-icon="' + icon + '"></span>' + escHtml(label) + '</span>';
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest ? e.target.closest('#reviewCompareToggle [data-compare]') : null;
    if (!btn) return;
    setReviewCompareSide(btn.getAttribute('data-compare'));
});

// Rows are rebuilt every time the modal opens, so delegate.
document.addEventListener('click', function (e) {
    const row = e.target.closest ? e.target.closest('[data-review-task]') : null;
    if (!row) return;
    openTaskReview(row.getAttribute('data-review-task'));
});

/* ?review=<task id> - the "Task submitted for review" notification. Opens that
   task's review straight away, then drops the parameter so a reload does not
   open it again. */
document.addEventListener('DOMContentLoaded', function () {
    const url = new URL(window.location.href);
    const id = Number(url.searchParams.get('review'));
    if (!id) return;
    url.searchParams.delete('review');
    window.history.replaceState(null, '', url.toString());
    openTaskReview(String(id));
});

function openTaskReview(taskId) {
    reviewTaskId = taskId;
    const modal = document.getElementById('taskReviewModal');
    if (!modal) return;

    document.getElementById('reviewTaskTitle').textContent = 'Loading…';
    document.getElementById('reviewTaskMeta').innerHTML = '';
    document.getElementById('reviewDecisionHint').textContent = '';
    setReviewWorkHint('');
    document.getElementById('reviewStatusRow').innerHTML = '';
    document.getElementById('reviewFeedback').value = '';
    document.getElementById('reviewError').classList.add('hidden');
    document.getElementById('reviewPrevFeedbackWrap').classList.add('hidden');

    const frame = document.getElementById('reviewPreviewFrame');
    const empty = document.getElementById('reviewPreviewEmpty');
    const openTab = document.getElementById('reviewOpenTab');
    const conceptPane = document.getElementById('reviewConceptPane');
    const changesPane = document.getElementById('reviewChangesPane');
    frame.classList.add('hidden');
    frame.src = '';
    empty.classList.remove('hidden');
    openTab.classList.add('hidden');
    conceptPane.classList.add('hidden');
    conceptPane.innerHTML = '';
    changesPane.classList.add('hidden');
    changesPane.innerHTML = '';
    document.getElementById('reviewWorkLabel').textContent = "The team's live site";
    document.getElementById('reviewDecisionBlock').classList.remove('hidden');
    document.getElementById('reviewConceptHint').classList.add('hidden');
    hideReviseStep();
    // Neither the buttons nor the Approved panel until the task says which it is.
    document.getElementById('reviewChoiceStep').classList.add('hidden');
    document.getElementById('reviewApprovedState').classList.add('hidden');
    reviewPreviewUrls = { before: null, after: null };
    reviewChanges = [];
    document.getElementById('reviewCompareToggle').classList.add('hidden');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    fetch(TASK_REVIEW_URL.replace('__ID__', encodeURIComponent(taskId)), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then((res) => res.json().then((d) => { if (!res.ok) throw new Error(d.error || 'Could not load this submission.'); return d; }))
        .then((d) => {
            document.getElementById('reviewTaskTitle').textContent = d.title || 'Submission';
            document.getElementById('reviewTaskMeta').innerHTML = [
                d.student_name ? reviewMetaChip('mdi:account-outline', d.student_name) : '',
                d.group_name ? reviewMetaChip('mdi:account-group-outline', teamLabel(d.group_name)) : '',
                d.role_label ? reviewMetaChip('mdi:briefcase-outline', d.role_label) : '',
            ].join('');

            const badges = [];
            if (d.awaiting_review) {
                // Handed in, no verdict yet: it is not Completed until approved.
                badges.push('<span class="tm-badge status-badge-pending"><span class="tm-dot status-fill-pending"></span>Pending Review' + (d.submitted_at ? ' · handed in ' + escHtml(d.submitted_at) : '') + '</span>');
            } else if (d.status === 'archived') {
                badges.push('<span class="tm-badge status-badge-completed"><span class="tm-dot status-fill-completed"></span>' + (d.is_hotel_concept ? 'Submitted' : 'Approved') + (d.submitted_at ? ' · ' + escHtml(d.submitted_at) : '') + '</span>');
            } else if (d.needs_revision) {
                badges.push('<span class="tm-badge status-badge-revision"><span class="tm-dot status-fill-revision"></span>Revision Required</span>');
            } else if (d.started) {
                badges.push('<span class="tm-badge status-badge-in_progress"><span class="tm-dot status-fill-in_progress"></span>In Progress</span>');
            } else {
                badges.push('<span class="tm-badge status-badge-not_started"><span class="tm-dot status-fill-not_started"></span>Not Started</span>');
            }
            if (d.revision_count > 0) {
                badges.push('<span class="tm-chip">Sent back ' + d.revision_count + (d.revision_count === 1 ? ' time' : ' times') + '</span>');
            }
            document.getElementById('reviewStatusRow').innerHTML = badges.join('');

            if (d.feedback) {
                document.getElementById('reviewPrevFeedback').textContent = d.feedback;
                document.getElementById('reviewPrevFeedbackMeta').textContent =
                    [d.feedback_by, d.feedback_at].filter(Boolean).join(' · ');
                document.getElementById('reviewPrevFeedbackWrap').classList.remove('hidden');
            }

            if (d.is_hotel_concept) {
                // Concepts are text, so they are read here rather than previewed, and
                // each carries its own verdict controls — the shared ones would only
                // let one decision cover both.
                document.getElementById('reviewWorkLabel').textContent = 'The two proposed hotel concepts';
                setReviewWorkHint('Read each idea, then approve it or send it back on its own card.');
                document.getElementById('reviewDecisionBlock').classList.add('hidden');
                document.getElementById('reviewConceptHint').classList.remove('hidden');
                paintReviewConcepts(d);
                conceptPane.classList.remove('hidden');
                empty.classList.add('hidden');
                return;
            }

            setReviewHighlightStatus('', null);
            if (d.preview_url) {
                reviewFrameSide = 'after';
                frame.src = d.preview_url;
                frame.classList.remove('hidden');
                empty.classList.add('hidden');
                openTab.href = d.preview_url;
                openTab.classList.remove('hidden');

                reviewPreviewUrls = { before: d.before_preview_url || null, after: d.preview_url };
                reviewChanges = Array.isArray(d.changes) ? d.changes : [];
                reviewStockCheck = d.stock_review || null;
                renderReviewChanges(reviewChanges);

                if (d.before_preview_url) {
                    document.getElementById('reviewCompareToggle').classList.remove('hidden');
                    setReviewCompareSide('after');
                } else {
                    setReviewWorkHint('This is the team\'s site as it is now.');
                }
            }

            // The buttons are for Pending Review only. An approved task shows who
            // approved it and when instead, read back from the saved verdict.
            const pending = d.status === 'archived' && !!d.awaiting_review;
            const approved = d.status === 'archived' && !d.awaiting_review;
            document.getElementById('reviewApproveBtn').disabled = !pending;
            document.getElementById('reviewReviseBtn').disabled = !pending;
            document.getElementById('reviewChoiceStep').classList.toggle('hidden', !pending);
            document.getElementById('reviewApprovedState').classList.toggle('hidden', !approved);
            document.getElementById('reviewApprovedMeta').textContent = approved
                ? [d.feedback_by ? 'by ' + d.feedback_by : '', d.feedback_at].filter(Boolean).join(' · ')
                : '';
            document.getElementById('reviewDecisionHint').textContent = pending
                ? 'Approve if the work is complete, or ask the student to change something.'
                : (approved
                    ? (d.edited_since_approval ? 'The page has been edited since it was approved.' : '')
                    : 'The student has not handed this in yet. You can decide once they do.');
        })
        .catch((err) => {
            document.getElementById('reviewTaskTitle').textContent = 'Could not load';
            showReviewError(err.message || 'Could not load this submission.');
        });
}

function closeTaskReview() {
    const modal = document.getElementById('taskReviewModal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.getElementById('reviewPreviewFrame').src = '';
    reviewTaskId = null;
    document.body.style.overflow = 'hidden'; // the team modal underneath is still open
}

function showReviewError(msg) {
    const el = document.getElementById('reviewError');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function submitTaskFeedback(decision) {
    if (!reviewTaskId) return;
    const feedback = document.getElementById('reviewFeedback').value.trim();
    if (decision === 'revise' && !feedback) {
        showReviewError('Tell the student what to change before sending it back.');
        return;
    }
    document.getElementById('reviewError').classList.add('hidden');

    const buttons = [
        document.getElementById('reviewApproveBtn'),
        document.getElementById('reviewReviseBtn'),
        document.getElementById('reviewSendFeedbackBtn'),
    ].filter(Boolean);
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback({ decision: decision, feedback: feedback }, decision, 'Task approved')
        .catch(() => { buttons.forEach((b) => { b.disabled = false; }); });
}

/* Draw both concept cards into the review pane, each with its own verdict
   controls. Rebuilt from the payload after every verdict, so approving one concept
   leaves the other's buttons exactly where they were. */
function paintReviewConcepts(data) {
    const pane = document.getElementById('reviewConceptPane');
    if (!pane) return;

    pane.innerHTML = '<div class="tm-concept-grid">'
        + (data.slots || []).map(renderReviewConceptCard).join('')
        + '</div>';
}

function renderReviewConceptCard(entry) {
    const concept = entry.concept;
    const slot = Number(entry.slot);

    if (!concept) {
        return '<div class="tm-concept-empty">'
            + '<p class="tm-card-title">' + escHtml(entry.slot_label) + '</p>'
            + '<p class="tm-panel-note mt-1">Not proposed yet.</p>'
            + '</div>';
    }

    // Both concepts have to exist and nobody chosen yet — the same rule the Team
    // Details tab uses, so both surfaces show the same buttons at the same time.
    const awaiting = entry.can_review === true;

    const controls = awaiting
        ? '<div class="tm-concept-actions">'
            + '<label class="tm-field-label" for="reviewFeedback' + slot + '">'
                + 'Your notes on ' + escHtml(entry.slot_label) + '</label>'
            + '<textarea id="reviewFeedback' + slot + '" rows="3" maxlength="2000"'
                + ' placeholder="What works here? What should change?"'
                + ' class="tm-textarea"></textarea>'
            + '<p class="tm-help">Required only when you send it back.</p>'
            + '<div class="tm-actions-row mt-3">'
                + '<button type="button" data-concept-action="' + slot + '" onclick="submitConceptFeedback(' + slot + ', \'approve\')"'
                    + ' class="btn-approve tm-act">'
                    + '<span class="iconify" data-icon="mdi:check-circle-outline"></span> Approve this concept'
                + '</button>'
                + '<button type="button" data-concept-action="' + slot + '" onclick="submitConceptFeedback(' + slot + ', \'revise\')"'
                    + ' class="btn-sendback tm-act">'
                    + '<span class="iconify" data-icon="mdi:message-reply-text-outline"></span> Send Feedback'
                + '</button>'
            + '</div>'
          + '</div>'
        : '<div class="tm-concept-actions">'
            + (concept.status === 'approved'
                ? '<p class="tm-official"><span class="iconify" data-icon="mdi:check-decagram"></span> Official hotel concept</p>'
                : '<p class="tm-panel-note">Waiting for the other concept to be proposed before you can review either one.</p>')
          + '</div>';

    return '<div class="tm-card">'
        + '<div class="tm-card-head"><p class="tm-card-title">' + escHtml(entry.slot_label) + '</p></div>'
        + '<div class="tm-concept-body">'
            + renderTeamHotelConcept({ concept: concept, history: entry.history, embedded: true })
            + controls
        + '</div>'
        + '</div>';
}

/* The verdict on one concept. The other one is untouched, so the dialog is redrawn
   from the response instead of reloading the page. */
function submitConceptFeedback(slot, decision) {
    if (!reviewTaskId) {
        showReviewError('This task could not be found — refresh and try again.');
        return;
    }

    const box = document.getElementById('reviewFeedback' + slot);
    const feedback = box ? box.value.trim() : '';
    if (decision === 'revise' && !feedback) {
        showReviewError('Tell the team what to change before sending it back.');
        return;
    }
    document.getElementById('reviewError').classList.add('hidden');

    const buttons = Array.from(document.querySelectorAll('[data-concept-action="' + slot + '"]'));
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback(
        { decision: decision, feedback: feedback, slot: slot },
        decision,
        'Concept approved',
        // Redraw in place: the second concept may still need a verdict, and a page
        // reload would close the dialog the faculty is working in.
        (d) => { if (Array.isArray(d.slots)) paintReviewConcepts(d); }
    ).catch(() => buttons.forEach((b) => { b.disabled = false; }));
}

/* Shared POST for every verdict path — the review dialog's task and concept
   verdicts, and the Team Details tab's concept verdicts. Resolves on success,
   rejects on failure so the caller can put its own buttons back. taskId and
   onError default to the review dialog's globals so its two callers need no
   change; the Team Details tab passes its own task id and error box instead. */
function postTaskFeedback(body, decision, approvedTitle, onSuccess, taskId, onError) {
    taskId = taskId || reviewTaskId;
    onError = onError || showReviewError;

    return fetch(TASK_FEEDBACK_URL.replace('__ID__', encodeURIComponent(taskId)), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
        },
        body: JSON.stringify(body)
    })
        .then((res) => res.json().then((d) => {
            if (!res.ok) {
                // 422 carries our own message; Laravel's validation bag carries errors.
                const first = d.errors ? Object.values(d.errors)[0][0] : null;
                throw new Error(d.error || first || d.message || 'Could not save feedback.');
            }
            return d;
        }))
        .then((d) => {
            if (!onSuccess) closeTaskReview();

            Swal.fire({
                icon: 'success',
                title: decision === 'revise' ? 'Sent back for revision' : approvedTitle,
                html: `<p class="text-sm text-slate-500">${d.message || 'The student has been notified.'}</p>`,
                timer: 2200,
                showConfirmButton: false,
                iconColor: '#B8873C',
                customClass: { popup: 'rounded-2xl p-6 bg-white shadow-2xl', title: 'text-lg font-bold text-slate-800' },
                buttonsStyling: false,
            }).then(() => {
                if (onSuccess) {
                    onSuccess(d);
                } else {
                    applyTaskVerdictInPlace(taskId, decision, d);
                }
            });
        })
        .catch((err) => {
            onError(err.message || 'Could not save feedback.');
            throw err;
        });
}

/* Team Details modal: Members & Roles / Team Task Activity */
function switchTeamModalTab(tabId) {
    const tabs = ['overview', 'members', 'concept', 'tasks'];
    const current = tabs.includes(tabId) ? tabId : 'overview';

    tabs.forEach(function (tab) {
        document.getElementById('team-panel-' + tab)?.classList.toggle('hidden', tab !== current);

        const btn = document.getElementById('team-tab-' + tab);
        if (!btn) return;
        btn.classList.toggle('is-on', tab === current);
        btn.setAttribute('aria-current', tab === current ? 'true' : 'false');
    });
}

/* Front Desk's hotel concept for the open team — the same rows the students read,
   fetched by group name so this giant page does not carry every team's history. */
const TEAM_CONCEPT_URL = @json(route('faculty.teams.hotel-concept', ['groupName' => '__GROUP__']));

// The concept task backing whichever team's tab is open — the Approve / Send back
// buttons here post through the same task-feedback route the review dialog uses.
let teamConceptTaskId = null;

function loadTeamHotelConcept(groupName) {
    const body = document.getElementById('teamModalConceptBody');
    if (!body) return;

    document.getElementById('teamConceptError')?.classList.add('hidden');
    body.innerHTML = '<div class="tm-empty tm-span-all">Loading hotel concept…</div>';

    fetch(TEAM_CONCEPT_URL.replace('__GROUP__', encodeURIComponent(groupName || '')), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.json().then(data => {
            if (!res.ok) throw new Error(data.error || 'Could not load the hotel concept.');
            return data;
        }))
        .then(data => {
            teamConceptTaskId = data.task_id || null;
            body.innerHTML = renderTeamConceptSlots(data);
        })
        .catch(err => {
            body.innerHTML = '<div class="tm-empty is-error tm-span-all">'
                + escHtml(err.message || 'Could not load the hotel concept.') + '</div>';
        });
}

function showTeamConceptError(msg) {
    const el = document.getElementById('teamConceptError');
    if (!el) return;
    el.textContent = msg;
    el.classList.remove('hidden');
}

/* Both of a team's concepts for the Team Details modal, side by side — each with
   its own decision buttons when faculty can still choose, or a note on why not.
   No edit history: this tab is for deciding, not auditing — the trail of
   who-changed-what stays in the student task dialog. */
function renderTeamConceptSlots(data) {
    const rows = (data.slots || []).map((entry) => renderTeamConceptCard(entry, data)).join('')
        || '<div class="tm-empty tm-span-all"><span class="iconify" data-icon="mdi:lightbulb-off-outline"></span>This team has no hotel concepts yet.</div>';

    if (!data.all_slots_filled) {
        return rows + '<p class="tm-panel-note tm-span-all">'
            + 'Waiting for the team to propose both concepts before you can choose one.</p>';
    }

    return rows;
}

/* One concept, as a single card: name + status up top, the idea in the middle,
   the decision (or its outcome) at the bottom. */
function renderTeamConceptCard(entry, data) {
    const concept = entry.concept;

    if (!concept) {
        return '<div class="tm-concept-empty">'
            + '<p class="tm-card-title">' + escHtml(entry.slot_label) + '</p>'
            + '<p class="tm-panel-note mt-1">Not proposed yet.</p>'
        + '</div>';
    }

    const status = concept.status || 'draft';
    const statusBadge = '<span class="tm-badge ' + (CONCEPT_STATUS_CLASSES[status] || CONCEPT_STATUS_CLASSES.draft) + '">'
        + escHtml(concept.status_label) + '</span>';

    // Who touched it last and when, one line each.
    const metaParts = [];
    if (concept.updated_by || concept.updated_at) {
        metaParts.push('Last changed'
            + (concept.updated_by ? ' by <b>' + escHtml(concept.updated_by) + '</b>' : '')
            + (concept.updated_at ? ' on ' + escHtml(concept.updated_at) : ''));
    }
    if (concept.submitted_at) {
        metaParts.push('Handed in'
            + (concept.submitted_by ? ' by <b>' + escHtml(concept.submitted_by) + '</b>' : '')
            + ' on ' + escHtml(concept.submitted_at));
    }
    if (concept.reviewed_at) {
        metaParts.push('Reviewed'
            + (concept.reviewed_by ? ' by <b>' + escHtml(concept.reviewed_by) + '</b>' : '')
            + ' on ' + escHtml(concept.reviewed_at));
    }
    const meta = metaParts.length
        ? '<p class="tm-concept-meta">' + metaParts.join('<br>') + '</p>'
        : '';

    const feedback = concept.faculty_feedback
        ? '<div class="tm-note">'
            + '<p class="tm-note-label">Your feedback</p>'
            + '<p class="tm-note-text">' + escHtml(concept.faculty_feedback) + '</p>'
          + '</div>'
        : '';

    return '<div class="tm-card">'
        + '<div class="tm-card-head">'
            + '<p class="tm-card-title">' + escHtml(entry.slot_label) + '</p>'
            + '<div class="tm-chips">' + statusBadge
                + '<span class="tm-chip">' + escHtml(concept.hotel_type_label) + '</span>'
            + '</div>'
        + '</div>'
        + '<div class="tm-concept-body">'
            + '<p class="tm-concept-name">' + escHtml(concept.title) + '</p>'
            + (concept.tagline ? '<p class="tm-concept-tagline">' + escHtml(concept.tagline) + '</p>' : '')
            + '<p class="tm-concept-desc">' + escHtml(concept.description) + '</p>'
            + meta
            + feedback
            + renderTeamConceptControls(entry, data)
        + '</div>'
    + '</div>';
}

/* Approve / Send back for one concept, or the reason those are not shown — decided
   already, or this concept simply is not the one that lost.
   Approve fires immediately — there is nothing more to say. Send back needs
   feedback first, so that field only appears once the faculty asks for it. */
function renderTeamConceptControls(entry, data) {
    if (entry.can_review) {
        const slot = Number(entry.slot);
        return '<div class="tm-concept-actions">'
            + '<div class="tm-actions-row" id="teamConceptButtons' + slot + '">'
                + '<button type="button" data-team-concept-action="' + slot + '" onclick="submitTeamConceptFeedback(' + slot + ', \'approve\')"'
                    + ' class="btn-approve tm-act">'
                    + '<span class="iconify" data-icon="mdi:check-circle-outline"></span> Approve this concept'
                + '</button>'
                + '<button type="button" data-team-concept-action="' + slot + '" onclick="showTeamConceptRevisionForm(' + slot + ')"'
                    + ' class="btn-sendback tm-act">'
                    + '<span class="iconify" data-icon="mdi:message-reply-text-outline"></span> Send Feedback'
                + '</button>'
            + '</div>'
            + '<div id="teamConceptRevisionForm' + slot + '" class="hidden">'
                + '<label class="tm-field-label" for="teamConceptFeedback' + slot + '">'
                    + 'What should the team change in ' + escHtml(entry.slot_label) + '?</label>'
                + '<textarea id="teamConceptFeedback' + slot + '" rows="3" maxlength="2000"'
                    + ' placeholder="What works here? What should change?"'
                    + ' class="tm-textarea"></textarea>'
                + '<p class="tm-help">Required before sending feedback. The team will read this.</p>'
                + '<div class="tm-actions-row mt-3">'
                    + '<button type="button" onclick="hideTeamConceptRevisionForm(' + slot + ')" class="tm-btn">Cancel</button>'
                    + '<button type="button" data-team-concept-action="' + slot + '" onclick="submitTeamConceptFeedback(' + slot + ', \'revise\')"'
                        + ' class="btn-sendback tm-act">'
                        + '<span class="iconify" data-icon="mdi:send-outline"></span> Send Feedback to team'
                    + '</button>'
                + '</div>'
            + '</div>'
        + '</div>';
    }

    // Decided, and this entry made it into the payload at all — the losing
    // concept never does once a decision exists, so this is always the winner.
    if (data.decided) {
        return '<div class="tm-concept-actions">'
            + '<p class="tm-official"><span class="iconify" data-icon="mdi:check-decagram"></span> Official hotel concept</p>'
        + '</div>';
    }

    return '';
}

/* Reveal the feedback field only once the faculty has actually chosen to send
   the concept back — Approve stays a single click with nothing to fill in. */
function showTeamConceptRevisionForm(slot) {
    document.getElementById('teamConceptButtons' + slot)?.classList.add('hidden');
    const form = document.getElementById('teamConceptRevisionForm' + slot);
    form?.classList.remove('hidden');
    document.getElementById('teamConceptFeedback' + slot)?.focus();
}

function hideTeamConceptRevisionForm(slot) {
    document.getElementById('teamConceptRevisionForm' + slot)?.classList.add('hidden');
    document.getElementById('teamConceptButtons' + slot)?.classList.remove('hidden');
}

/* The verdict on one concept, from the Team Details tab. Mirrors
   submitConceptFeedback below, but targets this team's task id and this tab's
   error box instead of the review dialog's globals. */
function submitTeamConceptFeedback(slot, decision) {
    if (!teamConceptTaskId) {
        showTeamConceptError('This team has no hotel concept task to send back — refresh and try again.');
        return;
    }

    const box = document.getElementById('teamConceptFeedback' + slot);
    const feedback = box ? box.value.trim() : '';
    if (decision === 'revise' && !feedback) {
        showTeamConceptError('Tell the team what to change before sending it back.');
        return;
    }
    document.getElementById('teamConceptError')?.classList.add('hidden');

    const buttons = Array.from(document.querySelectorAll('[data-team-concept-action="' + slot + '"]'));
    buttons.forEach((b) => { b.disabled = true; });

    postTaskFeedback(
        { decision: decision, feedback: feedback, slot: slot },
        decision,
        'Concept approved',
        (d) => { document.getElementById('teamModalConceptBody').innerHTML = renderTeamConceptSlots(d); },
        teamConceptTaskId,
        showTeamConceptError
    ).catch(() => buttons.forEach((b) => { b.disabled = false; }));
}

/* Where a concept stands in the workflow. Same colours the students see. */
const CONCEPT_STATUS_CLASSES = {
    draft: 'bg-slate-100 text-slate-600 border-slate-200',
    submitted: 'status-badge-pending',
    needs_revision: 'status-badge-revision',
    approved: 'status-badge-completed',
    not_selected: 'bg-slate-100 text-slate-500 border-slate-300',
};

/* Renders ONE concept and its edit history, for the student task dialog — a
   submitted concept is read, not previewed. Callers pass a single slot's
   { concept, history }; it always sits inside a card there. */
function renderTeamHotelConcept(data) {
    const concept = data.concept;
    const history = Array.isArray(data.history) ? data.history : [];
    if (!concept) {
        return '<p class="tm-panel-note">Not proposed yet.</p>';
    }

    const status = concept.status || 'draft';
    const statusBadge = '<span class="tm-badge ' + (CONCEPT_STATUS_CLASSES[status] || CONCEPT_STATUS_CLASSES.draft) + '">'
        + escHtml(concept.status_label) + '</span>';

    // Who changed it, who handed it in, and the last decision on it.
    const metaParts = [];
    if (concept.updated_by || concept.updated_at) {
        metaParts.push('Last changed'
            + (concept.updated_by ? ' by <b>' + escHtml(concept.updated_by) + '</b>' : '')
            + (concept.updated_at ? ' on ' + escHtml(concept.updated_at) : ''));
    }
    if (concept.submitted_at) {
        metaParts.push('Handed in'
            + (concept.submitted_by ? ' by <b>' + escHtml(concept.submitted_by) + '</b>' : '')
            + ' on ' + escHtml(concept.submitted_at));
    }
    if (concept.reviewed_at) {
        metaParts.push('Reviewed'
            + (concept.reviewed_by ? ' by <b>' + escHtml(concept.reviewed_by) + '</b>' : '')
            + ' on ' + escHtml(concept.reviewed_at));
    }

    const conceptBlock =
        '<div class="tm-chips">' + statusBadge + '<span class="tm-chip">' + escHtml(concept.hotel_type_label) + '</span></div>'
        + '<p class="tm-concept-name mt-2">' + escHtml(concept.title) + '</p>'
        + (concept.tagline ? '<p class="tm-concept-tagline">' + escHtml(concept.tagline) + '</p>' : '')
        + '<p class="tm-concept-desc">' + escHtml(concept.description) + '</p>'
        + (metaParts.length ? '<p class="tm-concept-meta">' + metaParts.join('<br>') + '</p>' : '')
        + (concept.faculty_feedback
            ? '<div class="tm-note"><p class="tm-note-label">Your feedback</p>'
                + '<p class="tm-note-text">' + escHtml(concept.faculty_feedback) + '</p></div>'
            : '');

    const historyRows = history.length
        ? history.map(function (entry) {
            const changes = (entry.changes || []).map(function (change) {
                return '<li class="mt-1"><b>' + escHtml(change.label) + ':</b> '
                    + '<s class="tm-muted">' + (escHtml(change.from) || 'empty') + '</s> to '
                    + escHtml(change.to) + '</li>';
            }).join('');

            return '<div class="tm-history-row">'
                + '<p><b>' + escHtml(entry.editor) + '</b> ' + escHtml(entry.action_label) + '</p>'
                + '<p class="tm-muted">' + escHtml(entry.created_at) + ' (' + escHtml(entry.created_at_human) + ')</p>'
                + (changes
                    ? '<ul>' + changes + '</ul>'
                    : '<p class="mt-1">' + escHtml(entry.title) + ', ' + escHtml(entry.hotel_type_label) + '</p>')
            + '</div>';
        }).join('')
        : '<div class="tm-history-row tm-muted">No changes recorded yet.</div>';

    return conceptBlock
        + '<div class="tm-history">'
            + '<p class="tm-field-label">Changes made so far</p>'
            + '<div class="tm-history-list">' + historyRows + '</div>'
        + '</div>';
}

/* Step 3's Reset to Manual: hand every student back to Unassigned and clear
   their roles, then let the preview code redraw the (now empty) team slots.
   The faculty member then picks each student's team and role in the list. */
function resetBulkAssignment() {
    document.querySelectorAll('#bulkStudentAssignList .bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        if (select) select.value = '';
        row.querySelectorAll('.bulk-role-checkbox').forEach((box) => { box.checked = false; });
    });
    setBulkAutoGroupLabel(false);
    if (typeof refreshBulkTeamPreviews === 'function') refreshBulkTeamPreviews();
}

// Once teams are filled, pressing the same button again reshuffles them.
function setBulkAutoGroupLabel(grouped) {
    const label = document.getElementById('bulkAutoGroupLabel');
    if (label) label.textContent = grouped ? 'Shuffle Teams' : 'Automatically Create Teams';
}

/* A message in the corner instead of the browser's "site says" dialog. Raised
   above the page's modals so it shows when fired from inside one. */
function teamFormNotice(message) {
    if (!window.Swal) return;
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        iconColor: '#7B1730',
        title: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        width: '24rem',
        didOpen: (el) => { el.closest('.swal2-container').style.zIndex = '20000'; },
    });
}

function submitActiveModalTab() {
    if (currentModalTab === 'add_team') {
        if (currentCreateTeamMode === 'multiple') {
            if (!buildBulkTeamsHiddenInputs()) {
                return;
            }
            document.getElementById('createTeamForm').submit();
            return;
        }

        const checked = document.querySelectorAll('.create-student-checkbox:checked').length;
        if (checked < 1) {
            teamFormNotice('Select at least 1 member for the team.');
            return;
        }
        if (checked > TEAM_MEMBER_MAX) {
            teamFormNotice('A team cannot have more than ' + TEAM_MEMBER_MAX + ' members.');
            return;
        }
        document.getElementById('createTeamFormSingle').submit();
    }
}

// ── The four seats every team is built from (rotate when auto-assigning) ──
const TEAM_DEFAULT_ROLES = [
    'front_desk',
    'room_management',
    'restaurant_management',
    'housekeeping',
];

// How many members the team behind a role checkbox has right now. Bulk teams are
// always 4, so they are not counted here.
function roleTeamSize(checkbox) {
    if (checkbox.classList.contains('create-role-checkbox')) {
        return document.querySelectorAll('.create-student-checkbox:checked').length;
    }
    if (checkbox.classList.contains('update-role-checkbox')) {
        return document.querySelectorAll('.update-student-checkbox:checked').length;
    }
    return TEAM_MEMBER_MAX;
}

// A role checkbox on a full team is one seat, not a multi-select: ticking one
// clears any other checked box for the same member. A team short of members still
// has all four seats to cover, so there a member may tick several. Bulk's
// checkboxes carry no name (they are read straight off the DOM at submit time),
// so they are scoped by their shared .bulk-role-grid container instead;
// create/update both name theirs member_roles[{id}][], identically for
// every seat option of that one member.
function enforceSingleRoleCheckbox(checkbox) {
    if (!checkbox.checked) return;
    const grid = checkbox.closest('.bulk-role-grid');
    if (!grid && roleTeamSize(checkbox) < TEAM_MEMBER_MAX) return;
    const siblings = grid
        ? grid.querySelectorAll('input[type="checkbox"]')
        : (checkbox.name ? document.querySelectorAll('input[type="checkbox"][name="' + CSS.escape(checkbox.name) + '"]') : []);
    siblings.forEach((cb) => { if (cb !== checkbox) cb.checked = false; });
}

// ── Bulk multi-team creation (exactly 4 members per team) ──
const BULK_TEAM_SIZE = 4;
// Lowest unused "Team N" names, gaps first; the server re-checks on save.
const NEXT_TEAM_NAMES = @json($nextTeamNames ?? []);
const defaultBulkTeamName = (index) => NEXT_TEAM_NAMES[index] || `Team ${index + 1}`;

function availableBulkStudentCount() {
    return document.querySelectorAll('.bulk-student-row').length;
}

function maxCompleteBulkTeams() {
    return Math.floor(availableBulkStudentCount() / BULK_TEAM_SIZE);
}

function rebuildBulkTeamSlots(forcedCount = null) {
    const countInput = document.getElementById('bulkTeamCount');
    const slots = document.getElementById('bulkTeamSlots');
    if (!countInput || !slots) return;

    const maxTeams = maxCompleteBulkTeams();
    let count = forcedCount != null ? parseInt(forcedCount, 10) : parseInt(countInput.value, 10);
    if (!Number.isFinite(count) || count < 0) count = 0;
    count = Math.min(count, maxTeams, 40);
    countInput.value = String(count);

    const previousNames = Array.from(document.querySelectorAll('.bulk-team-name-input'))
        .map((input) => input.value.trim());

    const names = [];
    for (let i = 0; i < count; i++) {
        names.push(previousNames[i] || defaultBulkTeamName(i));
    }

    if (count === 0) {
        slots.innerHTML = `
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                Need at least ${BULK_TEAM_SIZE} unassigned students to create a team. Each team must have exactly ${BULK_TEAM_SIZE} members.
            </div>
        `;
        syncBulkTeamSelectOptions();
        refreshBulkTeamPreviews();
        return;
    }

    slots.innerHTML = names.map((name, index) => `
        <div class="rounded-xl border border-slate-200 bg-white p-4 bulk-team-slot" data-team-index="${index}">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1">
                    <label class="block text-[0.625rem] font-bold uppercase tracking-wider text-slate-400 mb-1">Team ${index + 1} Name</label>
                    <input type="text" value="${escapeBulkAttr(name)}"
                        class="bulk-team-name-input w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition"
                        oninput="syncBulkTeamSelectOptions()">
                </div>
                <div class="sm:w-40">
                    <p class="text-[0.625rem] font-bold uppercase tracking-wider text-slate-400 mb-1">Members</p>
                    <p class="bulk-team-count h-10 px-3 rounded-xl bg-slate-50 border border-slate-200 text-sm font-semibold text-slate-600 flex items-center">0 / ${BULK_TEAM_SIZE}</p>
                </div>
            </div>
            <div class="bulk-team-preview mt-3 flex flex-wrap gap-1.5 text-[0.6875rem] text-slate-400">No students assigned yet.</div>
        </div>
    `).join('');

    syncBulkTeamSelectOptions();
    refreshBulkTeamPreviews();
}

function escapeBulkAttr(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function escapeBulkHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function getBulkTeamNames() {
    return Array.from(document.querySelectorAll('.bulk-team-name-input'))
        .map((input, index) => {
            const value = input.value.trim();
            return value !== '' ? value : defaultBulkTeamName(index);
        });
}

function syncBulkTeamSelectOptions() {
    const names = getBulkTeamNames();
    document.querySelectorAll('.bulk-team-select').forEach((select) => {
        const current = select.value;
        select.innerHTML = '<option value="">Unassigned</option>' + names.map((name, index) => {
            const selected = String(current) === String(index) ? ' selected' : '';
            return `<option value="${index}"${selected}>${escapeBulkHtml(name)}</option>`;
        }).join('');
        if (current !== '' && Number(current) >= names.length) {
            select.value = '';
        }
    });
    refreshBulkTeamPreviews();
}

function onBulkStudentTeamChange(selectEl) {
    const row = selectEl?.closest?.('.bulk-student-row');
    const teamIndex = selectEl?.value === '' ? -1 : parseInt(selectEl.value, 10);

    if (teamIndex >= 0) {
        const counts = getBulkTeamMemberCounts();
        // Exclude this select's previous contribution by temporarily clearing then recounting isn't needed —
        // value already changed. Count other rows assigned to this team.
        let others = 0;
        document.querySelectorAll('.bulk-student-row').forEach((r) => {
            if (r === row) return;
            const s = r.querySelector('.bulk-team-select');
            if (s && parseInt(s.value, 10) === teamIndex) others++;
        });
        if (others >= BULK_TEAM_SIZE) {
            teamFormNotice(`Each team can only have ${BULK_TEAM_SIZE} members.`);
            selectEl.value = '';
        }
    }

    if (row) {
        const hasRole = row.querySelector('.bulk-role-checkbox:checked');
        if (!hasRole && selectEl.value !== '') {
            const assignedCount = Array.from(document.querySelectorAll('.bulk-team-select'))
                .filter((select) => select.value !== '').length;
            const role = TEAM_DEFAULT_ROLES[(assignedCount - 1 + TEAM_DEFAULT_ROLES.length) % TEAM_DEFAULT_ROLES.length];
            const roleCb = row.querySelector(`.bulk-role-checkbox[value="${role}"]`);
            if (roleCb) roleCb.checked = true;
        }
        if (selectEl.value === '') {
            row.querySelectorAll('.bulk-role-checkbox').forEach((cb) => { cb.checked = false; });
        }
    }
    refreshBulkTeamPreviews();
}

function getBulkTeamMemberCounts() {
    const names = getBulkTeamNames();
    const counts = names.map(() => 0);
    document.querySelectorAll('.bulk-team-select').forEach((select) => {
        if (select.value === '') return;
        const idx = parseInt(select.value, 10);
        if (Number.isFinite(idx) && counts[idx] != null) counts[idx]++;
    });
    return counts;
}

function refreshBulkTeamPreviews() {
    const names = getBulkTeamNames();
    const buckets = names.map(() => []);

    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        if (teamIndex < 0 || !buckets[teamIndex]) return;

        const roles = Array.from(row.querySelectorAll('.bulk-role-checkbox:checked')).map((cb) => cb.value);
        buckets[teamIndex].push({
            id: row.getAttribute('data-student-id'),
            name: row.getAttribute('data-student-name') || 'Student',
            roles,
        });
    });

    // A role may be held by at most one member of the SAME team (bucket index) — a
    // student assigned to a different team can freely reuse it.
    const usedRolesByTeam = buckets.map((members) => new Set(members.flatMap((m) => m.roles)));
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        const used = teamIndex >= 0 ? (usedRolesByTeam[teamIndex] || new Set()) : new Set();
        row.querySelectorAll('.bulk-role-checkbox').forEach((roleCb) => {
            roleCb.disabled = !roleCb.checked && used.has(roleCb.value);
        });
    });

    document.querySelectorAll('.bulk-team-slot').forEach((slot, index) => {
        const countEl = slot.querySelector('.bulk-team-count');
        const previewEl = slot.querySelector('.bulk-team-preview');
        const members = buckets[index] || [];
        const count = members.length;
        if (countEl) {
            countEl.textContent = `${count} / ${BULK_TEAM_SIZE}`;
            countEl.classList.toggle('text-green-700', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('border-green-200', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('bg-green-50', count === BULK_TEAM_SIZE);
            countEl.classList.toggle('text-amber-700', count > 0 && count !== BULK_TEAM_SIZE);
            countEl.classList.toggle('border-amber-200', count > 0 && count !== BULK_TEAM_SIZE);
            countEl.classList.toggle('bg-amber-50', count > 0 && count !== BULK_TEAM_SIZE);
        }
        if (!previewEl) return;
        if (!members.length) {
            previewEl.innerHTML = '<span class="text-slate-400">No students assigned yet.</span>';
            return;
        }
        // Each member with the role they hold, so the team reads at a glance.
        const roleLabels = window.TEAM_ROLE_LABELS || {};
        previewEl.innerHTML = members.map((member) => `
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-600">
                ${member.roles.map((role) => `<span class="w-1.5 h-1.5 rounded-full role-dot-${escapeBulkAttr(role)} shrink-0"></span>`).join('')}
                <span class="font-semibold">${escapeBulkHtml(member.name)}</span>
                <span class="text-slate-400">· ${member.roles.length ? member.roles.map((role) => escapeBulkHtml(roleLabels[role] || role)).join(', ') : 'No role yet'}</span>
            </span>
        `).join('');
    });

    const hint = document.getElementById('bulkTeamHint');
    if (hint) {
        const totalStudents = availableBulkStudentCount();
        const assigned = Array.from(document.querySelectorAll('.bulk-team-select')).filter((s) => s.value !== '').length;
        const leftover = totalStudents - assigned;
        const completeTeams = (getBulkTeamMemberCounts() || []).filter((c) => c === BULK_TEAM_SIZE).length;
        hint.textContent = `${assigned} of ${totalStudents} students placed · ${completeTeams} complete team${completeTeams === 1 ? '' : 's'} of ${BULK_TEAM_SIZE}`
            + (leftover > 0 ? ` · ${leftover} not yet assigned` : '')
            + '.';
    }

    // Disable full teams in dropdowns for unassigned students
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        if (!select) return;
        const current = select.value;
        const counts = getBulkTeamMemberCounts();
        Array.from(select.options).forEach((opt) => {
            if (opt.value === '') return;
            const idx = parseInt(opt.value, 10);
            const isCurrent = String(current) === String(idx);
            const full = (counts[idx] || 0) >= BULK_TEAM_SIZE && !isCurrent;
            opt.disabled = full;
            const base = opt.textContent.replace(/\s*\(full\)$/, '');
            opt.textContent = full ? `${base} (full)` : base;
        });
    });
}

function autoGroupStudentsIntoTeamsOfFour() {
    const rows = Array.from(document.querySelectorAll('.bulk-student-row'));
    const teamCount = maxCompleteBulkTeams();

    if (teamCount < 1) {
        teamFormNotice(`You need at least ${BULK_TEAM_SIZE} unassigned students to create a team.`);
        rebuildBulkTeamSlots(0);
        return;
    }

    rebuildBulkTeamSlots(teamCount);

    // Clear all assignments first
    rows.forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        if (select) select.value = '';
        row.querySelectorAll('.bulk-role-checkbox').forEach((cb) => { cb.checked = false; });
    });

    // Fisher-Yates: every mix of students is equally likely on each shuffle.
    const shuffled = [...rows];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    const toAssign = shuffled.slice(0, teamCount * BULK_TEAM_SIZE);

    toAssign.forEach((row, index) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = Math.floor(index / BULK_TEAM_SIZE);
        if (select) select.value = String(teamIndex);

        const role = TEAM_DEFAULT_ROLES[index % TEAM_DEFAULT_ROLES.length];
        const roleCb = row.querySelector(`.bulk-role-checkbox[value="${role}"]`);
        if (roleCb) roleCb.checked = true;
    });

    refreshBulkTeamPreviews();
    setBulkAutoGroupLabel(true);

    const leftover = rows.length - toAssign.length;
    if (leftover > 0) {
        const hint = document.getElementById('bulkTeamHint');
        if (hint) {
            hint.textContent = `Grouped ${toAssign.length} students into ${teamCount} team${teamCount === 1 ? '' : 's'} of ${BULK_TEAM_SIZE}. ${leftover} student${leftover === 1 ? '' : 's'} left over (not enough for another team of ${BULK_TEAM_SIZE}).`;
        }
    }
}

function distributeAllStudentsToTeams() {
    autoGroupStudentsIntoTeamsOfFour();
}

function filterBulkStudentList() {
    const q = (document.getElementById('bulkStudentSearch')?.value || '').trim().toLowerCase();
    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const hay = row.getAttribute('data-search') || '';
        row.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

function buildBulkTeamsHiddenInputs() {
    const container = document.getElementById('bulkTeamsHiddenInputs');
    if (!container) return false;
    container.innerHTML = '';

    const names = getBulkTeamNames();
    if (!names.length) {
        teamFormNotice(`You need at least ${BULK_TEAM_SIZE} unassigned students to create a team.`);
        return false;
    }

    const uniqueNames = names.map((n) => n.trim().toLowerCase());
    if (new Set(uniqueNames).size !== uniqueNames.length) {
        teamFormNotice('Each team needs a unique name.');
        return false;
    }

    const buckets = names.map(() => ({ members: [], roles: {} }));
    let assignedCount = 0;

    document.querySelectorAll('.bulk-student-row').forEach((row) => {
        const select = row.querySelector('.bulk-team-select');
        const teamIndex = select?.value === '' ? -1 : parseInt(select.value, 10);
        if (teamIndex < 0 || !buckets[teamIndex]) return;

        const studentId = row.getAttribute('data-student-id');
        if (!studentId) return;

        let roles = Array.from(row.querySelectorAll('.bulk-role-checkbox:checked')).map((cb) => cb.value);
        if (!roles.length) {
            roles = [TEAM_DEFAULT_ROLES[assignedCount % TEAM_DEFAULT_ROLES.length]];
        }

        buckets[teamIndex].members.push(studentId);
        buckets[teamIndex].roles[studentId] = roles;
        assignedCount++;
    });

    for (let i = 0; i < buckets.length; i++) {
        const count = buckets[i].members.length;
        if (count !== BULK_TEAM_SIZE) {
            teamFormNotice(`"${names[i]}" must have exactly ${BULK_TEAM_SIZE} members (currently ${count}). Press Automatically Create Teams to fix it.`);
            return false;
        }
    }

    if (assignedCount === 0) {
        teamFormNotice('Assign students before saving.');
        return false;
    }

    names.forEach((name, index) => {
        const bucket = buckets[index];
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = `teams[${index}][group_name]`;
        nameInput.value = name;
        container.appendChild(nameInput);

        bucket.members.forEach((studentId) => {
            const memberInput = document.createElement('input');
            memberInput.type = 'hidden';
            memberInput.name = `teams[${index}][members][]`;
            memberInput.value = studentId;
            container.appendChild(memberInput);

            (bucket.roles[studentId] || []).forEach((role) => {
                const roleInput = document.createElement('input');
                roleInput.type = 'hidden';
                roleInput.name = `teams[${index}][member_roles][${studentId}][]`;
                roleInput.value = role;
                container.appendChild(roleInput);
            });
        });
    });

    return true;
}

// The card holding a ticked student's role checkboxes.
function studentRoleContainer(memberCheckbox) {
    return memberCheckbox ? memberCheckbox.closest('.team-student-card') : null;
}

function clearStudentRoles(memberCheckbox) {
    const card = studentRoleContainer(memberCheckbox);
    if (!card) return;
    card.querySelectorAll('input[type="checkbox"][name^="member_roles"]').forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
    });
    delete card.dataset.randomized;
}

function assignDefaultRoleToCard(card, roleKey) {
    if (!card || !roleKey) return;
    const roleCb = card.querySelector('input[type="checkbox"][name^="member_roles"][value="' + roleKey + '"]');
    if (roleCb) roleCb.checked = true;
}

function nextDefaultRoleIndex(mode) {
    const checked = document.querySelectorAll(teamMemberSelector(mode) + ':checked');
    const used = new Set();
    checked.forEach(cb => {
        const card = studentRoleContainer(cb);
        if (!card) return;
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked').forEach(r => {
            used.add(r.value);
        });
    });
    for (let i = 0; i < TEAM_DEFAULT_ROLES.length; i++) {
        if (!used.has(TEAM_DEFAULT_ROLES[i])) return i;
    }
    return checked.length % TEAM_DEFAULT_ROLES.length;
}

// The member checkboxes of the create list or the Update Team dialog.
function teamMemberSelector(mode) {
    return mode === 'update' ? '.update-student-checkbox' : '.create-student-checkbox';
}

// A role may be held by at most one member. Scans whichever list is active (create,
// or update) and disables any role checkbox already checked on another member
// of the same team, so the faculty sees it grey out instead of discovering the clash
// on submit. Checked boxes stay enabled so their own owner can still uncheck them.
function refreshRoleAvailability(mode) {
    const config = {
        create: { checkbox: '.create-student-checkbox', card: '.create-student-card' },
        update: { checkbox: '.update-student-checkbox', card: '.update-student-row' },
    }[mode];
    if (!config) return;

    const used = new Set();

    document.querySelectorAll(config.checkbox + ':checked').forEach(memberCb => {
        const card = studentRoleContainer(memberCb) || memberCb.closest(config.card);
        if (!card) return;
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked').forEach(r => used.add(r.value));
    });

    document.querySelectorAll(config.card).forEach(card => {
        if (card.dataset.randomized === 'true') return; // roles locked by Randomize, leave them disabled
        card.querySelectorAll('input[type="checkbox"][name^="member_roles"]').forEach(roleCb => {
            const taken = !roleCb.checked && used.has(roleCb.value);
            // Update Team keeps a taken role clickable so onUpdateRoleToggle() can
            // say who holds it; it is only dimmed here.
            if (mode === 'update') {
                roleCb.disabled = false;
                roleCb.closest('label')?.style.setProperty('opacity', taken ? '.5' : '');
            } else {
                roleCb.disabled = taken;
            }
        });
    });

    // Every member tick and every role tick on the create list reaches here, so the
    // summary is refreshed from one place rather than from each handler.
    if (mode === 'create') {
        renderCreateTeamSummary();
    }
}

function onTeamMemberToggle(checkbox, mode) {
    const card = studentRoleContainer(checkbox);

    if (!checkbox.checked) {
        clearStudentRoles(checkbox);
    } else if (card) {
        const anyRole = card.querySelector('input[type="checkbox"][name^="member_roles"]:checked');
        if (!anyRole && !takeSpareSeat(mode, card)) {
            const idx = nextDefaultRoleIndex(mode);
            assignDefaultRoleToCard(card, TEAM_DEFAULT_ROLES[idx]);
        }
    }
    updateTeamSelectedCount(mode);
    refreshRoleAvailability(mode);
}

/* A member joining a team whose four seats are all held - because a short team
   gave someone two - takes one of that member's extra seats instead of doubling
   up on a seat. Returns whether a seat was handed over. */
function takeSpareSeat(mode, card) {
    const holders = Array.from(document.querySelectorAll(teamMemberSelector(mode) + ':checked'))
        .map(cb => studentRoleContainer(cb))
        .filter(other => other && other !== card);

    const used = new Set();
    holders.forEach(other => other.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked')
        .forEach(r => used.add(r.value)));
    if (TEAM_DEFAULT_ROLES.some(r => !used.has(r))) return false;

    const donor = holders.slice().reverse().find(other =>
        other.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked').length > 1);
    if (!donor) return false;

    const spare = Array.from(donor.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked')).pop();
    spare.checked = false;
    spare.disabled = false;
    assignDefaultRoleToCard(card, spare.value);
    return true;
}

function onSingleTeamMemberToggle(checkbox) {
    if (checkbox.checked) {
        const checked = document.querySelectorAll('.create-student-checkbox:checked').length;
        if (checked > TEAM_MEMBER_MAX) {
            checkbox.checked = false;
            teamFormNotice('A team cannot have more than ' + TEAM_MEMBER_MAX + ' members.');
            return;
        }
    }
    onTeamMemberToggle(checkbox, 'create');
}

/*
   The picked members and their roles, shown above the list instead of only inside
   it. Randomize ticks four boxes somewhere in a scrolling column of every
   unassigned student, so without this the only way to read the result is to scroll
   and hunt for ticks.

   Built from the checkboxes rather than from what Randomize chose, so a member
   unticked or a role changed by hand is reflected too and the panel cannot claim
   someone who is no longer on the team.
*/
function renderCreateTeamSummary() {
    const panel = document.getElementById('createTeamSummary');
    const chips = document.getElementById('createTeamSummaryChips');
    if (!panel || !chips) return;

    const labels = window.TEAM_ROLE_LABELS || {};
    const picked = Array.from(document.querySelectorAll('.create-student-checkbox:checked'));

    // Nothing selected is the modal's opening state, not a result worth a panel.
    panel.classList.toggle('hidden', picked.length === 0);
    if (picked.length === 0) {
        chips.innerHTML = '';
        return;
    }

    chips.innerHTML = picked.map((cb) => {
        const card = cb.closest('.team-student-card');
        const name = card?.getAttribute('data-student-name') || 'Student';
        const roles = Array.from(
            card?.querySelectorAll('input[type="checkbox"][name^="member_roles"]:checked') || []
        ).map(r => r.value);

        const roleMarkup = roles.length
            ? roles.map(key => `
                <span class="inline-flex items-center gap-1 text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full role-dot-${escapeBulkAttr(key)} shrink-0"></span>
                    ${escapeBulkHtml(labels[key] || key)}
                </span>
            `).join('')
            : '<span class="text-amber-700">No role yet</span>';

        return `
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white border border-slate-200 text-[0.6875rem]">
                <span class="font-semibold text-slate-700">${escapeBulkHtml(name)}</span>
                <span class="text-slate-300">·</span>
                ${roleMarkup}
            </span>
        `;
    }).join('');
}

function randomizeSingleTeamMembers() {
    const checkboxes = Array.from(document.querySelectorAll('.create-student-checkbox'));
    const note = document.getElementById('createRandomizeNote');
    if (checkboxes.length === 0) {
        if (note) note.textContent = 'There are no students left to assign.';
        return;
    }

    checkboxes.forEach((cb) => {
        cb.checked = false;
        clearStudentRoles(cb);
    });

    const shuffled = [...checkboxes].sort(() => Math.random() - 0.5);
    const pick = shuffled.slice(0, Math.min(TEAM_MEMBER_MAX, shuffled.length));
    pick.forEach((cb, index) => {
        cb.checked = true;
        const card = cb.closest('.team-student-card');
        // Fewer than 4 picked: the seats left over go round the members again,
        // so every seat is still covered.
        for (let seat = index; seat < TEAM_DEFAULT_ROLES.length; seat += pick.length) {
            assignDefaultRoleToCard(card, TEAM_DEFAULT_ROLES[seat]);
        }
        // Lock roles Randomize assigned — faculty reroll instead of hand-editing them.
        if (card) {
            card.dataset.randomized = 'true';
            card.querySelectorAll('input[type="checkbox"][name^="member_roles"]').forEach(r => { r.disabled = true; });
        }
    });

    updateTeamSelectedCount('create');
    refreshRoleAvailability('create');
    if (note) {
        note.textContent = pick.length < TEAM_MEMBER_MAX
            ? `Selected ${pick.length} available student${pick.length === 1 ? '' : 's'}. A full team needs ${TEAM_MEMBER_MAX}.`
            : `${TEAM_MEMBER_MAX} students selected automatically.`;
    }
}

function filterTeamStudentList(mode) {
    const input = document.getElementById('createStudentSearch');
    const cards = document.querySelectorAll('.create-student-card');
    const q = (input?.value || '').trim().toLowerCase();
    cards.forEach(card => {
        const hay = card.getAttribute('data-search') || '';
        card.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

function updateTeamSelectedCount(mode) {
    const selector = '.create-student-checkbox';
    const el = document.getElementById('createSelectedCount');
    const count = document.querySelectorAll(selector + ':checked').length;
    if (!el) return;
    el.textContent = mode === 'create'
        ? (count + ' of ' + TEAM_MEMBER_MAX + ' Students Selected')
        : (count + ' selected');
}

document.addEventListener('DOMContentLoaded', function () {
    updateTeamSelectedCount('create');
    renderCreateTeamSummary();
    rebuildBulkTeamSlots(maxCompleteBulkTeams());
    const randomizeBtn = document.getElementById('createRandomizeBtn');
    if (randomizeBtn) randomizeBtn.addEventListener('click', randomizeSingleTeamMembers);
});

// ── Open Update Team modal: this team's members, plus unassigned students to add ──
function openUpdateModal(groupName, memberData) {
    const form = document.getElementById('updateTeamForm');
    form.action = '/faculty/role/groups/' + encodeURIComponent(groupName);

    document.getElementById('updateGroupNameInput').value = groupName;
    document.getElementById('updateTeamEditingLabel').textContent = groupName;

    const searchInput = document.getElementById('updateStudentSearch');
    if (searchInput) searchInput.value = '';

    const rolesMap = {};
    (memberData || []).forEach(m => {
        const id = String(m.student_id);
        rolesMap[id] = m.roles || (m.role ? [m.role] : []);
    });

    document.querySelectorAll('.update-student-checkbox').forEach(cb => { cb.checked = false; });
    document.querySelectorAll('.update-role-checkbox').forEach(cb => { cb.checked = false; });
    document.querySelectorAll('.update-role-warning').forEach(w => w.classList.add('hidden'));

    const list = document.getElementById('updateMemberList');
    const addList = document.getElementById('updateAddList');
    const rows = Array.from(document.querySelectorAll('.update-student-row'));
    let memberCount = 0;
    let candidateCount = 0;

    rows.forEach(row => {
        const sid = String(row.dataset.studentId || '');
        const isMember = Object.prototype.hasOwnProperty.call(rolesMap, sid);
        // A student with no team yet, in this block, can be added here.
        const isCandidate = !isMember && row.dataset.addable === '1';
        const cb = row.querySelector('.update-student-checkbox');
        const onBadge = row.querySelector('.update-on-team-badge');

        row.classList.toggle('hidden', !isMember && !isCandidate);
        row.classList.toggle('ut-add-row', isCandidate);
        row.dataset.isTeamMember = isMember ? '1' : '0';
        row.dataset.isCandidate = isCandidate ? '1' : '0';
        row.querySelector('.update-new-badge')?.classList.add('hidden');

        if (!isMember) {
            if (cb) cb.checked = false;
            if (onBadge) onBadge.classList.add('hidden');
            if (isCandidate) {
                candidateCount++;
                addList.appendChild(row);
            }
            return;
        }

        memberCount++;
        if (cb) cb.checked = true;
        if (onBadge) onBadge.classList.remove('hidden');

        (rolesMap[sid] || []).forEach(role => {
            const roleCb = row.querySelector('.update-role-checkbox[value="' + role + '"]');
            if (roleCb) roleCb.checked = true;
        });
    });

    // Members at the top of the list
    rows.filter(r => r.dataset.isTeamMember === '1').forEach(row => list.appendChild(row));

    // A full team is one seat each, so a member of a full team built before
    // that rule keeps only their first tick — the save would refuse more. A
    // short team keeps every seat its members hold.
    rows.filter(r => r.dataset.isTeamMember === '1' && memberCount >= TEAM_MEMBER_MAX).forEach(row => {
        const checked = Array.from(row.querySelectorAll('.update-role-checkbox:checked'));
        checked.slice(1).forEach(cb => { cb.checked = false; });
    });

    const emptyNote = document.getElementById('updateNoMembersNote');
    if (emptyNote) emptyNote.classList.toggle('hidden', memberCount > 0);
    document.getElementById('updateNoAddableNote')?.classList.toggle('hidden', candidateCount > 0);

    refreshRoleAvailability('update');
    refreshUpdateCapacity();

    document.getElementById('updateTeamModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeUpdateModal() {
    document.getElementById('updateTeamModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function filterUpdateStudentList() {
    const q = (document.getElementById('updateStudentSearch')?.value || '').trim().toLowerCase();
    document.querySelectorAll('.update-student-row').forEach(row => {
        // Only this team's members and the students who may join it
        if (row.dataset.isTeamMember !== '1' && row.dataset.isCandidate !== '1') {
            row.classList.add('hidden');
            return;
        }
        const hay = row.getAttribute('data-search') || '';
        row.classList.toggle('hidden', q !== '' && !hay.includes(q));
    });
}

/* A role on Update Team belongs to one member. Picking one another member still
   holds is undone, and the row says who has it, rather than the clash only
   surfacing as a rejected save. */
function onUpdateRoleToggle(roleCb) {
    const row = roleCb.closest('.update-student-row');
    const warning = row?.querySelector('.update-role-warning');
    document.querySelectorAll('.update-role-warning').forEach(w => w.classList.add('hidden'));

    if (roleCb.checked) {
        const holder = Array.from(document.querySelectorAll('.update-student-row')).find(other =>
            other !== row
            && other.querySelector('.update-student-checkbox')?.checked
            && other.querySelector('.update-role-checkbox[value="' + roleCb.value + '"]')?.checked
        );

        if (holder) {
            roleCb.checked = false;
            const name = holder.dataset.studentName || 'another member';
            if (warning) {
                warning.textContent = 'This role is already assigned to ' + name
                    + '. Please uncheck the role from ' + name + ' first.';
                warning.classList.remove('hidden');
            }
            return;
        }
    }

    enforceSingleRoleCheckbox(roleCb);
    refreshRoleAvailability('update');
}

function onUpdateMemberToggle(checkbox) {
    const card = checkbox.closest('.update-student-row');
    if (!card) return;
    const isCandidate = card.dataset.isCandidate === '1';

    // A fifth member is never let in; refreshUpdateCapacity() normally has the
    // box disabled before it can be ticked.
    if (checkbox.checked && document.querySelectorAll('.update-student-checkbox:checked').length > TEAM_MEMBER_MAX) {
        checkbox.checked = false;
        refreshUpdateCapacity();
        return;
    }

    const badge = card.querySelector(isCandidate ? '.update-new-badge' : '.update-on-team-badge');
    if (badge) badge.classList.toggle('hidden', !checkbox.checked);

    if (!checkbox.checked) {
        card.querySelectorAll('.update-role-checkbox').forEach(cb => { cb.checked = false; });
    } else if (isCandidate && !card.querySelector('.update-role-checkbox:checked')) {
        // Joining with a role already picked: a seat nobody holds, or one of the
        // extra seats a short team handed a member.
        if (!takeSpareSeat('update', card)) {
            assignDefaultRoleToCard(card, TEAM_DEFAULT_ROLES[nextDefaultRoleIndex('update')]);
        }
    }
    refreshRoleAvailability('update');
    refreshUpdateCapacity();
}

/* Headcount against the 4-member cap. At 4, every student not yet on the team is
   locked out of Add Students and the full-team notice shows. */
function refreshUpdateCapacity() {
    const count = document.querySelectorAll('.update-student-checkbox:checked').length;
    const full = count >= TEAM_MEMBER_MAX;
    const left = Math.max(0, TEAM_MEMBER_MAX - count);

    const badge = document.getElementById('updateCapacityBadge');
    if (badge) {
        badge.textContent = count + ' of ' + TEAM_MEMBER_MAX + ' members'
            + (full ? ' · Full' : ' · ' + left + ' slot' + (left === 1 ? '' : 's') + ' left');
        badge.classList.toggle('is-full', full);
    }
    const bar = document.getElementById('updateCapacityBar');
    if (bar) bar.style.width = Math.min(100, (count / TEAM_MEMBER_MAX) * 100) + '%';
    document.getElementById('updateFullNote')?.classList.toggle('hidden', !full);

    document.querySelectorAll('.update-student-row[data-is-candidate="1"]').forEach(row => {
        const cb = row.querySelector('.update-student-checkbox');
        const locked = full && cb && !cb.checked;
        if (cb) cb.disabled = locked;
        row.classList.toggle('is-locked', !!locked);
        row.title = locked ? 'This team is already full. Maximum of 4 members allowed.' : '';
    });
}

// ── Task Assignment Form ───────────────────────
// One page, no steps: team, tasks and due date are all on screen at once, and the
// summary at the foot updates as they are filled in. It used to be a five-screen
// wizard, which cost a click per screen to reach the assign button.
let selectedTeams = [];

// How many members of each team hold each role, so a task row can say who is
// actually there to receive it.
const TEAM_ROLE_COUNTS = @json($teamRoleCounts ?? []);

/* Every team that is ticked, in the order they appear. One name or twenty, the
   rest of the form behaves the same — the summary and the headcount hints read
   this list rather than a single name. */
function selectedTeamNames() {
    return Array.from(document.querySelectorAll('#taskTeamSelector input[name="group_names[]"]:checked'))
        .map((box) => box.value);
}

function onTeamToggled() {
    const boxes = Array.from(document.querySelectorAll('#taskTeamSelector input[name="group_names[]"]'));
    const all = document.getElementById('taskAllTeams');
    if (all) all.checked = boxes.length > 0 && boxes.every((box) => box.checked);

    selectedTeams = selectedTeamNames();
    updateRoleMemberCounts();
    updateSubmitState();
}

function toggleAllTeams(on) {
    document.querySelectorAll('#taskTeamSelector input[name="group_names[]"]').forEach((box) => {
        box.checked = !!on;
    });
    onTeamToggled();
}

// The headcount hint the department step used to carry on its cards. A task row
// names its own department now, so the hint rides along with the row. A role
// nobody fills is still assignable — the task waits for whoever takes it — so
// this informs rather than blocks.
function updateRoleMemberCounts() {
    // Summed across the teams picked: with three teams chosen, "4 members" is the
    // four students who will actually receive this role's activity.
    const counts = {};
    selectedTeams.forEach((team) => {
        const teamCounts = TEAM_ROLE_COUNTS[team] || {};
        Object.keys(teamCounts).forEach((role) => {
            counts[role] = (counts[role] || 0) + (teamCounts[role] || 0);
        });
    });

    const picked = selectedTeams.length > 0;

    document.querySelectorAll('[data-role-count]').forEach(el => {
        const n = counts[el.dataset.roleCount] || 0;
        el.textContent = picked
            ? ' · ' + (n > 0 ? n + (n === 1 ? ' member' : ' members') : 'nobody holds this')
            : '';
        el.classList.toggle('text-amber-500', picked && n === 0);
    });
}

// ── Task groups ────────────────────────────────
// One group is one numbered step of the simulation, holding that position's task
// for every department that still has one.

function taskGroupBoxes(step) {
    return Array.from(document.querySelectorAll('.task-step-check[data-group="' + step + '"]'));
}

// public/css/app.css is a frozen build with no has-[:checked] variant in it, so
// a card's selected look is toggled by hand instead of leaning on that selector.
function setTaskCardSelected(step, selected) {
    const card = document.getElementById('taskCard-' + step);
    if (!card) return;
    card.classList.toggle('border-brand', selected);
    card.classList.toggle('bg-brand-soft', selected);
}

function refreshTaskCardState(step) {
    const master = document.getElementById('taskGroupCheck-' + step);
    setTaskCardSelected(step, !!master && (master.checked || master.indeterminate));
}

function openTaskDetailsModal(step) {
    document.getElementById('taskDetailsModal-' + step)?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTaskDetailsModal(step) {
    document.getElementById('taskDetailsModal-' + step)?.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ── Search + category filter over the task cards ──
let currentTaskCategoryFilter = 'all';

function filterTaskCards(filter) {
    if (typeof filter === 'string') {
        currentTaskCategoryFilter = filter;
        document.querySelectorAll('.task-filter-tab').forEach(btn => {
            const active = btn.dataset.filter === filter;
            btn.classList.toggle('bg-brand', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('bg-white', !active);
            btn.classList.toggle('text-slate-500', !active);
        });
    }

    const query = (document.getElementById('taskSearchInput')?.value || '').trim().toLowerCase();

    document.querySelectorAll('.task-card').forEach(card => {
        const categories = (card.dataset.categories || '').split(' ');
        const matchesCategory = currentTaskCategoryFilter === 'all' || categories.includes(currentTaskCategoryFilter);
        const matchesSearch = !query || (card.dataset.search || '').includes(query);
        card.classList.toggle('hidden', !(matchesCategory && matchesSearch));
    });
}

function toggleTaskGroupAll(step) {
    const master = document.getElementById('taskGroupCheck-' + step);
    if (!master) return;

    master.indeterminate = false;
    // Skip a box the concept lock has disabled — checking the header must not
    // silently tick work the form is about to have refused.
    taskGroupBoxes(step).forEach(cb => { if (!cb.disabled) cb.checked = master.checked; });
    refreshTaskCardState(step);
    updateSubmitState();
}

// The header box reflects its enabled rows: all, none, or the browser's own
// partial mark when one has been unticked out of the task. A box the concept
// lock disabled counts toward neither — it is not part of what "all" means
// while it is locked.
function syncTaskGroup(step) {
    const master = document.getElementById('taskGroupCheck-' + step);
    if (!master) return;

    const boxes = taskGroupBoxes(step).filter(cb => !cb.disabled);
    const checked = boxes.filter(cb => cb.checked).length;

    master.checked = checked > 0 && checked === boxes.length;
    master.indeterminate = checked > 0 && checked < boxes.length;
    refreshTaskCardState(step);
}

function selectAllVisibleTasks() {
    const boxes = Array.from(document.querySelectorAll('.task-check:not(:disabled)'));
    const allChecked = boxes.length > 0 && boxes.every(cb => cb.checked);

    boxes.forEach(cb => cb.checked = !allChecked);
    document.querySelectorAll('.task-group-check:not(:disabled)').forEach(master => {
        master.checked = !allChecked;
        master.indeterminate = false;
        const step = master.id.replace('taskGroupCheck-', '');
        setTaskCardSelected(step, !allChecked);
    });

    updateSubmitState();
}

// ── Summary + submit ───────────────────────────
// Assigning needs a team and at least one task; the server checks the team too,
// but there is no reason to let the button be pressed without one.
/* The hotel concept gates every other activity, so a team with no approved
   concept can only be given that one. Applied per checkbox rather than per
   task card: TASK 01 holds the concept alongside Front Desk's other website
   work, so locking the whole card would also lock the one activity that is
   never locked. Applied as the teams are ticked rather than only on submit:
   storeTask() refuses the post either way, but being refused after filling
   the form in is a worse way to find out. */
function lockedTeamNames() {
    return Array.from(document.querySelectorAll('#taskTeamSelector input[name="group_names[]"]:checked'))
        .map((box) => box.closest('[data-team]'))
        .filter((label) => label && label.dataset.conceptSubmitted !== '1')
        .map((label) => label.dataset.team);
}

/* The activities each team already holds, by lowercased title. A team keeps a
   task once it has been given it, so anything in here would be a duplicate —
   storeTask refuses it, and this is what stops it being ticked in the first
   place. */
const TEAM_HELD_TITLES = @json($teamHeldTitles ?? []);

/* A title every selected team already holds. Not "any team": ticking an
   activity for one team that has it and one that does not is still a real
   assignment for the second, and the server hands it to that one alone. */
function heldByEverySelectedTeam(title) {
    return selectedTeams.length > 0 && selectedTeams.every(
        (team) => (TEAM_HELD_TITLES[team] || []).includes(title)
    );
}

function applyStepLocks() {
    const locked = lockedTeamNames();
    const note = document.getElementById('taskConceptLockNote');
    const reasons = [];

    if (locked.length) {
        reasons.push('Customization is locked: ' + locked.join(', ')
            + (locked.length === 1 ? ' has' : ' have') + ' not submitted a hotel concept yet.');
    }

    // A task the team already holds stays tickable: setting it again sends no
    // second copy, but moves its due date to the one on this form.
    const isLocked = (box) => locked.length > 0 && box.dataset.concept !== '1';

    const held = new Set();
    document.querySelectorAll('.task-step-check').forEach((box) => {
        const lockedBox = isLocked(box);
        box.disabled = lockedBox;
        // A box that just locked must not stay ticked, or the form would post
        // what the server is about to refuse.
        if (lockedBox && box.checked) box.checked = false;
        if (!lockedBox && box.checked && heldByEverySelectedTeam(box.dataset.title)) held.add(box.dataset.title);
    });

    // A card dims only once every activity in it is locked — TASK 01 keeps its
    // normal look while the concept box in it is still open.
    document.querySelectorAll('.task-card').forEach((card) => {
        const step = String(card.id).replace('taskCard-', '');
        const boxes = taskGroupBoxes(step);
        const allLocked = boxes.length > 0 && boxes.every((box) => box.disabled);
        // opacity-60, not 50: the frozen public/css/app.css build has no
        // opacity-50 rule in it at all, so the card would not dim.
        card.classList.toggle('opacity-60', allLocked);
        const master = document.getElementById('taskGroupCheck-' + step);
        if (master) master.disabled = allLocked;
    });

    if (held.size) {
        reasons.push(Array.from(held).join(', ') + (held.size === 1 ? ' is' : ' are')
            + ' already assigned' + (selectedTeams.length === 1 ? ' to ' + selectedTeams[0] : ' to every team picked')
            + '. No second copy is sent; set a due date below to change ' + (held.size === 1 ? 'its' : 'their') + ' deadline.');
    }

    if (note) {
        note.textContent = reasons.join(' ');
        note.classList.toggle('hidden', reasons.length === 0);
    }
}

function updateSubmitState() {
    applyStepLocks();

    const checked = Array.from(document.querySelectorAll('.task-check:checked'));

    const btn = document.getElementById('submitTasksBtn');
    if (btn) btn.disabled = selectedTeams.length === 0 || checked.length === 0;

    updateSummary(checked);
}

function updateSummary(checked) {
    checked = checked || Array.from(document.querySelectorAll('.task-check:checked'));

    document.getElementById('reviewTeamName').textContent =
        selectedTeams.length === 0 ? '—'
        : selectedTeams.length === 1 ? selectedTeams[0]
        : selectedTeams.length + ' teams';

    document.getElementById('reviewTaskCount').textContent =
        checked.length + ' task' + (checked.length !== 1 ? 's' : '');

    // A step spans every department that has work at that position, so the summary
    // counts them rather than naming one.
    const roles = new Set(checked.map(cb => cb.dataset.role));
    document.getElementById('reviewDeptName').textContent = roles.size
        ? 'across ' + roles.size + (roles.size === 1 ? ' department' : ' departments')
        : '—';

    // datetime-local already carries the time, so parse it as-is rather than
    // pinning midnight the way the date-only input needed.
    const dueDate = document.querySelector('input[name="due_date"]').value;
    document.getElementById('reviewDueDate').textContent = dueDate
        ? new Date(dueDate).toLocaleString('en-US', {
              month: 'short', day: 'numeric', year: 'numeric',
              hour: 'numeric', minute: '2-digit',
          })
        : 'No deadline';
}

function resetTaskWizard() {
    selectedTeams = [];
    const allTeams = document.getElementById('taskAllTeams');
    if (allTeams) allTeams.checked = false;
    document.querySelectorAll('.task-team-radio').forEach(r => r.checked = false);
    document.querySelectorAll('.task-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.task-group-check').forEach(master => {
        master.checked = false;
        master.indeterminate = false;
        setTaskCardSelected(master.id.replace('taskGroupCheck-', ''), false);
    });
    document.querySelector('input[name="due_date"]').value = '';

    updateRoleMemberCounts();
    updateSubmitState();
}

// Track checkbox and due-date changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('task-check')) {
        syncTaskGroup(e.target.dataset.group);
        updateSubmitState();
    } else if (e.target.name === 'due_date') {
        updateSummary();
    }
});


// Initialize on page load
(function() {
    const activeTab = '{{ $activeTab }}';
    if (activeTab === 'create_task') {
        // A failed submit re-renders with the teams still ticked; read them back so
        // the summary and the headcount hints match what is on the form.
        onTeamToggled();
        filterTaskCards('all');
        document.querySelectorAll('.task-group-check').forEach(master => refreshTaskCardState(master.id.replace('taskGroupCheck-', '')));
        updateSubmitState();
    }
    if (activeTab === 'team_setup') {
        @if($errors->any() && old('_form_source') === 'create_teams_bulk')
            currentCreateTeamMode = 'multiple';
        @elseif($errors->any() && old('_form_source') === 'create_team')
            currentCreateTeamMode = 'single';
        @endif
        initTeamSetupScreen();
        // Reflect the roles old() restored into the form as already-taken.
        refreshRoleAvailability('create');
    }
    // "Team Setup" from Activity Logs still arrives as ?create=1
    @if(request()->boolean('create'))
        if (activeTab !== 'team_setup') openCreateTeamModal();
    @endif
})();

@if ($errors->any() && old('_form_source') === 'update_team')
window.addEventListener('load', function () {
    Swal.fire({
        icon: 'error',
        title: 'Could not save team',
        text: @json($errors->first()),
        confirmButtonColor: '#7B1730',
    });
});
@endif

@if (session('success'))
// Creating a team redirects, so the modal is gone by the time the page is back and
// the green banner is the only thing that says it worked — easy to miss above a full
// table. Same popup the rest of the app uses for a completed action.
window.addEventListener('load', function () {
    Swal.fire({
        icon: 'success',
        title: @json(session('success_title', 'Success')),
        text: @json(session('success')),
        @if (session('success_confirm'))
        confirmButtonText: 'OK',
        confirmButtonColor: '#374151',
        @else
        timer: 2500,
        timerProgressBar: true,
        showConfirmButton: false,
        @endif
        iconColor: '#C9A45C',
        width: '22rem',
    });
});
@endif

// ── Team Info Modal ────────────────────────────
let teamModalActivityLogs = [];
let teamModalActivityPage = 1;
// The department tab open in Team Task Activity ("role|student"); null picks one.
let teamModalActivityTab = null;
const TEAM_MODAL_ACTIVITY_PER_PAGE = 5;

// "Team 3" stays as it is; only a bare name gets the word (Group::label).
function teamLabel(name) {
    return !name ? 'Team' : (/^team\s/i.test(name) ? name : 'Team ' + name);
}

function escHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/* Where one task row stands, in the five states of the approval workflow
   (SimulationPhase::taskState); the badge colours are the ones students see. */
function teamTaskState(log) {
    const labels = {
        not_started: 'Not Started', in_progress: 'In Progress', pending: 'Pending Review',
        revision: 'Revision Required', approved: 'Approved',
    };
    const key = log.state || (log.awaiting_review ? 'pending'
        : (log.status === 'archived' ? 'approved'
        : 'not_started'));
    return { key: key === 'approved' ? 'completed' : key, label: labels[key] };
}

/* Three counts over the team's tasks, so faculty see at a glance whether
   anything is waiting on them. */
function renderTeamModalActivityStats(logs) {
    const box = document.getElementById('teamModalActivityStats');
    const waiting = logs.filter((l) => l.awaiting_review).length;
    const reviewed = logs.filter((l) => l.status === 'archived' && !l.awaiting_review).length;
    const working = logs.length - waiting - reviewed;

    const tabCount = document.getElementById('teamTabCountTasks');
    if (tabCount) {
        tabCount.textContent = waiting
            ? waiting + ' waiting for your review'
            : (logs.length ? logs.length + (logs.length === 1 ? ' task' : ' tasks') : '');
        tabCount.classList.toggle('is-alert', waiting > 0);
    }
    if (!box) return;

    const stat = (key, icon, num, label) =>
        '<div class="tm-stat">'
            + '<span class="tm-stat-icon status-badge-' + key + '"><span class="iconify" data-icon="' + icon + '"></span></span>'
            + '<div><p class="tm-stat-num status-text-' + key + '">' + num + '</p><p class="tm-stat-label">' + label + '</p></div>'
        + '</div>';

    box.innerHTML = logs.length
        ? stat('pending', 'mdi:clock-alert-outline', waiting, 'Waiting for your review')
            + stat('completed', 'mdi:check-circle-outline', reviewed, 'Handed in and reviewed')
            + stat('in_progress', 'mdi:progress-pencil', working, 'Still being worked on')
        : '';
}

function renderTeamModalActivityPage() {
    const activityBody = document.getElementById('teamModalActivityBody');
    const pager = document.getElementById('teamModalActivityPager');
    const meta = document.getElementById('teamModalActivityMeta');
    const pageLabel = document.getElementById('teamModalActivityPageLabel');
    const prevBtn = document.getElementById('teamModalActivityPrev');
    const nextBtn = document.getElementById('teamModalActivityNext');
    // Approved tasks sink to the end of their department, so what still needs
    // faculty stays on top. Stable sort: the rest keep their order. Runs on every
    // render, so a task approved in place moves down at once.
    const logs = teamModalActivityLogs.sort((a, b) => (a.state === 'approved') - (b.state === 'approved'));
    const total = logs.length;
    // Every task on one page: the department groups replace paging.
    const totalPages = 1;

    if (teamModalActivityPage > totalPages) teamModalActivityPage = totalPages;
    if (teamModalActivityPage < 1) teamModalActivityPage = 1;

    renderTeamModalActivityStats(logs);

    if (total === 0) {
        const emptyTabs = document.getElementById('teamModalActivityTabs');
        if (emptyTabs) emptyTabs.innerHTML = '';
        activityBody.innerHTML = '<tr><td colspan="5"><div class="tm-empty">'
            + '<span class="iconify" data-icon="mdi:clipboard-outline"></span>'
            + 'No tasks have been given to this team yet.</div></td></tr>';
        if (pager) pager.classList.add('hidden');
        if (meta) meta.textContent = '';
        return;
    }

    const start = 0;
    const end = total;

    // One group per department and student, in desk order. A task fans out one
    // row per member, so the student is named once in the group's header
    // instead of on every task.
    const ROLE_ORDER = ['front_desk', 'room_management', 'restaurant_management', 'housekeeping', 'maintenance'];
    const groups = new Map();
    logs.forEach((log) => {
        const key = (log.role || '') + '|' + (log.student_name || '');
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(log);
    });
    const rank = (key) => { const i = ROLE_ORDER.indexOf(key.split('|')[0]); return i === -1 ? ROLE_ORDER.length : i; };
    const ordered = [...groups.entries()].sort((a, b) => rank(a[0]) - rank(b[0]));

    const taskRow = function(log, index) {
        const isDone = log.status === 'archived';
        const state = teamTaskState(log);
        const statusBadge = '<span class="tm-badge status-badge-' + state.key + '">'
            + '<span class="tm-dot status-fill-' + state.key + '"></span>' + state.label + '</span>';

        // A task can be opened and looked at any time — it's only the decision
        // (inside the dialog, gated by canAct there) that waits on a submission.
        const openCell = log.id
            ? '<button type="button" data-review-task="' + Number(log.id) + '"'
                + ' title="' + (isDone
                    ? 'Open this task to see the work and decide'
                    : 'See how far the student has got') + '"'
                + ' class="tm-btn tm-btn-sm' + (log.awaiting_review ? ' tm-btn-dark' : '') + '">'
                + '<span class="iconify" data-icon="' + (log.awaiting_review ? 'mdi:file-document-edit-outline' : 'mdi:eye-outline') + '"></span>'
                + (log.awaiting_review ? 'Review now' : (isDone ? 'View again' : 'View progress'))
              + '</button>'
            : '<span class="tm-muted">None</span>';


        return '<tr>' +
            '<td>' +
                '<p class="tm-task-name"><span class="tm-task-no">Task ' + String(index + 1).padStart(2, '0') + '</span>' + escHtml(log.title || 'Untitled task') + '</p>' +
                (log.description ? '<p class="tm-muted tm-ellipsis">' + escHtml(log.description) + '</p>' : '') +
            '</td>' +
            '<td' + (log.due_date ? '' : ' class="tm-muted"') + '>' + escHtml(log.due_date || 'No due date') + '</td>' +
            '<td>' + statusBadge
                + (log.edited_since_approval
                    ? '<p class="tm-muted mt-1" style="color:#B45309;" title="The department page changed after you approved this. Open it and ask for changes if it no longer meets the task.">Edited after approval</p>'
                    : '') + '</td>' +
            '<td' + (isDone ? '' : ' class="tm-muted"') + '>' +
                escHtml(isDone ? (log.submitted_at || log.updated_at || '') : 'Not yet') +
            '</td>' +
            '<td class="text-center">' + openCell + '</td>' +
        '</tr>';
    };

    // Tabs, so only one department's tasks are on screen. Opens on the first
    // department with work waiting for review, else the first one.
    if (!groups.has(teamModalActivityTab)) {
        const waitingGroup = ordered.find(([, items]) => items.some((l) => l.awaiting_review));
        teamModalActivityTab = (waitingGroup || ordered[0])[0];
    }
    const tabs = document.getElementById('teamModalActivityTabs');
    if (tabs) {
        tabs.innerHTML = ordered.map(function([key, items]) {
            const waiting = items.filter((l) => l.awaiting_review).length;
            return '<button type="button" data-activity-tab="' + escHtml(key) + '"'
                    + ' class="tm-role-tab' + (key === teamModalActivityTab ? ' is-active' : '') + '"'
                    + ' title="' + escHtml(items[0].student_name || 'Not assigned') + '">'
                + escHtml(items[0].role_label || items[0].role || 'Other')
                + '<span class="tm-role-tab-count">' + items.length + '</span>'
                + (waiting ? '<span class="tm-role-tab-alert" title="' + waiting + ' to review"></span>' : '')
            + '</button>';
        }).join('');
        tabs.onclick = function(e) {
            const btn = e.target.closest('[data-activity-tab]');
            if (!btn) return;
            teamModalActivityTab = btn.getAttribute('data-activity-tab');
            renderTeamModalActivityPage();
        };
    }

    activityBody.innerHTML = ordered.filter(([key]) => key === teamModalActivityTab).map(function([, items]) {
        const name = items[0].student_name;
        const waiting = items.filter((l) => l.awaiting_review).length;
        return '<tr class="tm-group-row"><td colspan="5">'
                + '<div class="tm-group-head">'
                    + '<div class="min-w-0">'
                        + '<p class="tm-group-title">' + escHtml(items[0].role_label || items[0].role || 'Other') + '</p>'
                        + '<p class="tm-group-student">'
                            + '<img src="{{ asset('images/default-avatar.svg') }}" alt="" class="tm-avatar tm-avatar-sm">'
                            + '<span class="tm-ellipsis">' + escHtml(name || 'Not assigned') + '</span></p>'
                    + '</div>'
                    + '<span class="tm-muted" style="white-space:nowrap;">' + items.length + (items.length === 1 ? ' task' : ' tasks')
                        + (waiting ? ' · <b style="color:#B45309;">' + waiting + ' to review</b>' : '') + '</span>'
                + '</div>'
            + '</td></tr>'
            + items.map(taskRow).join('');
    }).join('');

    if (meta) meta.textContent = '(tasks ' + (start + 1) + ' to ' + end + ' of ' + total + ')';
    if (pageLabel) pageLabel.textContent = 'Page ' + teamModalActivityPage + ' of ' + totalPages;

    if (pager) {
        if (totalPages > 1) {
            pager.classList.remove('hidden');
            pager.classList.add('flex');
        } else {
            pager.classList.add('hidden');
            pager.classList.remove('flex');
        }
    }
    if (prevBtn) prevBtn.disabled = teamModalActivityPage <= 1;
    if (nextBtn) nextBtn.disabled = teamModalActivityPage >= totalPages;
}

/* ── Hotel Customization approval & Simulation roles ─────────────────────── */
const TEAM_APPROVAL = @json($teamApprovalByGroup ?? []);
const SIM_SEAT_LABELS = @json(\App\Support\HotelTemplateBuilder::SIMULATION_SEAT_LABELS);
const SIM_ROLES_URL = @json(route('faculty.teams.simulation-roles', ['groupName' => '__G__']));
let teamModalGroup = null;
let teamModalMembers = [];

/* The team's approval progress: only approved tasks count toward it, and the
   simulation opens at 100% once the roles are confirmed. */
function renderTeamApproval() {
    const box = document.getElementById('teamModalApproval');
    if (!box) return;
    const a = TEAM_APPROVAL[teamModalGroup];
    if (!a || !a.required) { box.classList.add('hidden'); return; }
    box.classList.remove('hidden');

    // The Simulation is open to every team; the badge says how far approval is.
    const lock = a.unlocked
        ? '<span class="tm-badge status-badge-completed"><span class="tm-dot status-fill-completed"></span>All tasks approved · Simulation open</span>'
        : '<span class="tm-badge status-badge-in_progress"><span class="tm-dot status-fill-in_progress"></span>Approval in progress · Simulation open</span>';
    const count = (label, n, key) => '<span style="margin-right:1rem;">' + label + ': <b class="status-text-' + key + '">' + n + '</b></span>';

    box.innerHTML = '<div class="flex items-center justify-between gap-2 flex-wrap">'
            + '<p class="tm-section-title"><span class="iconify" data-icon="mdi:check-decagram-outline"></span>'
                + escHtml(teamLabel(teamModalGroup)) + ' — Hotel Customization</p>' + lock
        + '</div>'
        + '<p class="tm-muted mt-2">'
            + count('Required Tasks', a.required, 'not_started') + count('Approved', a.approved, 'completed')
            + count('Pending Review', a.pending, 'pending') + count('Revision Required', a.revision, 'revision')
        + '</p>'
        + '<div class="flex items-center gap-3 mt-2">'
            + '<div style="flex:1; height:.5rem; border-radius:999px; background:#F0ECE8; overflow:hidden;">'
                + '<div class="status-fill-completed" style="height:100%; width:' + Number(a.percent) + '%;"></div></div>'
            + '<b style="font-size:0.8125rem;">Approval Progress ' + Number(a.percent) + '%</b>'
        + '</div>';
}

/* Simulation desks per student: one each on a full team, several on a team
   short of students, and never one desk for two students. Their Customization
   role is not touched; saving is faculty's confirmation, which the simulation
   waits for. */
function renderTeamSimRoles() {
    const box = document.getElementById('teamModalSimRoles');
    if (!box) return;
    if (!teamModalMembers.length) { box.classList.add('hidden'); return; }
    box.classList.remove('hidden');

    const a = TEAM_APPROVAL[teamModalGroup] || {};
    const current = a.simulation_roles || {};
    // Confirmed is final: the students were told their desks, so nothing here moves.
    const locked = !!a.roles_confirmed;
    const shortTeam = teamModalMembers.length < Object.keys(SIM_SEAT_LABELS).length;
    const seatsOf = (m) => [].concat(current[String(m.student_id)] || []);
    const desks = (m) => Object.keys(SIM_SEAT_LABELS).map((key) =>
        '<label style="display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .55rem; border:1px solid #E4E2E0; border-radius:.6rem; font-size:0.75rem; font-weight:600; color:#4A4643; background:' + (locked ? '#F3F2F1; cursor:not-allowed;' : '#fff; cursor:pointer;') + '">'
            + '<input type="checkbox" data-sim-role="' + Number(m.student_id) + '" value="' + key + '"'
                + (seatsOf(m).includes(key) ? ' checked' : '') + (locked ? ' disabled' : '')
                + ' onchange="onSimDeskToggle(this)" style="accent-color:#374151;">'
            + escHtml(SIM_SEAT_LABELS[key])
        + '</label>'
    ).join('');

    box.innerHTML = '<p class="tm-section-title"><span class="iconify" data-icon="mdi:bell-ring-outline"></span>Simulation Roles</p>'
        + '<p class="tm-muted mt-1">'
            + (shortTeam
                ? 'This team has fewer than 4 students, so a student can run more than one desk. Each desk goes to one student. '
                : 'Each student runs one desk in the Hotel Simulation. ')
            + 'Their customization role stays as it is. '
            + (a.roles_confirmed
                ? 'You have confirmed these roles.'
                : 'Not confirmed yet: the team cannot start the simulation until you confirm them.')
        + '</p>'
        + '<div style="display:grid; gap:.75rem; margin-top:.75rem;">'
        + teamModalMembers.map((m) =>
            '<div>'
                + '<p class="tm-ellipsis" style="font-size:0.8125rem; font-weight:700; color:#181818; margin-bottom:.35rem;">' + escHtml(m.name)
                    + ' <span class="tm-muted">(' + escHtml((m.role_labels || []).join(', ') || 'No role yet') + ')</span></p>'
                + '<div style="display:flex; flex-wrap:wrap; gap:.4rem;">' + desks(m) + '</div>'
            + '</div>'
        ).join('')
        + '</div>'
        + '<div id="teamSimRolesError" class="tm-alert hidden" style="margin-top:.75rem;"></div>'
        + '<div style="margin-top:.75rem;">'
            + (locked
                ? '<button type="button" class="tm-btn" disabled style="cursor:not-allowed; opacity:.7;">'
                    + '<span class="iconify" data-icon="mdi:check-circle"></span> Simulation roles confirmed</button>'
                : '<button type="button" id="teamSimRolesSave" onclick="saveTeamSimRoles()" class="tm-btn tm-btn-dark">'
                    + '<span class="iconify" data-icon="mdi:check"></span> Confirm simulation roles</button>')
        + '</div>';
}

/* A desk belongs to one student, so ticking it takes it off whoever had it.
   On a full team a student runs one desk, so their other tick goes too. */
function onSimDeskToggle(box) {
    if (!box.checked) return;
    const all = Array.from(document.querySelectorAll('#teamModalSimRoles input[data-sim-role]'));
    const fullTeam = teamModalMembers.length >= Object.keys(SIM_SEAT_LABELS).length;
    all.forEach((other) => {
        if (other === box) return;
        const sameDesk = other.value === box.value && other.dataset.simRole !== box.dataset.simRole;
        const sameStudent = fullTeam && other.dataset.simRole === box.dataset.simRole;
        if (sameDesk || sameStudent) other.checked = false;
    });
}

function saveTeamSimRoles() {
    const roles = {};
    teamModalMembers.forEach((m) => { roles[String(m.student_id)] = []; });
    document.querySelectorAll('#teamModalSimRoles input[data-sim-role]:checked').forEach((box) => {
        (roles[box.dataset.simRole] = roles[box.dataset.simRole] || []).push(box.value);
    });
    const err = document.getElementById('teamSimRolesError');
    const btn = document.getElementById('teamSimRolesSave');
    err.classList.add('hidden');
    btn.disabled = true;

    fetch(SIM_ROLES_URL.replace('__G__', encodeURIComponent(teamModalGroup)), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
        },
        body: JSON.stringify({ roles: roles })
    })
        .then((res) => res.json().then((d) => {
            if (!res.ok) {
                const first = d.errors ? Object.values(d.errors)[0][0] : null;
                throw new Error(d.error || first || d.message || 'Could not save the simulation roles.');
            }
            return d;
        }))
        .then((d) => {
            TEAM_APPROVAL[teamModalGroup] = d.approval;
            renderTeamSimRoles();
            renderTeamApproval();

            const seats = (d.approval && d.approval.simulation_roles) || {};
            const rows = teamModalMembers.map((m) =>
                '<li style="display:flex; justify-content:space-between; gap:1rem; padding:.35rem 0; border-top:1px solid #EFEFEF;">'
                    + '<span style="font-weight:700; color:#181818;">' + escHtml(m.name) + '</span>'
                    + '<span style="color:#5F5A55;">' + escHtml([].concat(seats[String(m.student_id)] || []).map((k) => SIM_SEAT_LABELS[k] || k).join(', ') || 'None') + '</span>'
                + '</li>'
            ).join('');
            const ready = 'The team can run the simulation now; customization approval is at '
                + Number((d.approval && d.approval.percent) || 0) + '%.';

            Swal.fire({
                icon: 'success',
                title: 'Simulation roles confirmed',
                html: '<ul style="list-style:none; margin:0 0 .75rem; padding:0; text-align:left; font-size:0.8125rem;">' + rows + '</ul>'
                    + '<p class="text-sm text-slate-500">Each student has been notified of their role. ' + ready + '</p>',
                confirmButtonText: 'Done',
                iconColor: '#B8873C',
                customClass: {
                    popup: 'rounded-2xl p-6 bg-white shadow-2xl',
                    title: 'text-lg font-bold text-slate-800',
                    confirmButton: 'tm-btn tm-btn-dark',
                },
                buttonsStyling: false,
            });
        })
        .catch((e) => {
            err.textContent = e.message;
            err.classList.remove('hidden');
            btn.disabled = false;
        });
}

function openTeamModal(groupName, members, createdAt, activityLogs, options) {
    options = options || {};
    teamModalGroup = groupName;
    teamModalMembers = Array.isArray(members) ? members : [];
    // Review Submission opens straight onto what it announced — every other
    // caller (View Team, Update) gets the full, unfiltered activity list.
    const logs = (Array.isArray(activityLogs) ? activityLogs : [])
        .filter((l) => !options.onlyAwaitingReview || l.awaiting_review);

    document.getElementById('modalTeamName').textContent = teamLabel(groupName);
    document.getElementById('modalTeamSummary').textContent = [
        members.length + (members.length === 1 ? ' member' : ' members'),
        createdAt ? 'Created ' + createdAt : '',
    ].filter(Boolean).join(' · ');
    document.getElementById('teamTabCountMembers').textContent = members.length
        ? members.length + (members.length === 1 ? ' member' : ' members') : '';

    const grid = document.getElementById('teamModalMembersBody');
    if (members.length === 0) {
        grid.innerHTML = '<div class="tm-empty" style="grid-column: 1 / -1;">'
            + '<span class="iconify" data-icon="mdi:account-off-outline"></span>'
            + 'This team has no members yet.</div>';
    } else {
        grid.innerHTML = members.map(function(m, i) {
            const roleLabels = m.role_labels || [m.role_label || m.role];
            const roleChips = roleLabels.filter(Boolean).map(function(rl) {
                return '<span class="tm-chip"><span class="iconify" data-icon="mdi:briefcase-outline"></span>' + escHtml(rl) + '</span>';
            }).join('') || '<span class="tm-muted">No role yet</span>';
            const activityBtn = m.user_id
                ? '<button type="button" data-activity-user="' + Number(m.user_id) + '"'
                    + ' data-activity-name="' + escHtml(m.name) + '" class="tm-btn"'
                    + ' title="Show everything this student has done">'
                    + '<span class="iconify" data-icon="mdi:clipboard-text-clock-outline"></span> See activity'
                  + '</button>'
                : '';
            return '<div class="tm-member" data-member-card="' + Number(m.user_id || 0) + '">'
                + '<div class="tm-member-top">'
                    + '<img src="{{ asset('images/default-avatar.svg') }}" alt="" class="tm-avatar">'
                    + '<div class="min-w-0">'
                        + '<p class="tm-member-name" title="' + escHtml(m.name) + '">' + escHtml(m.name) + '</p>'
                        + '<p class="tm-member-no">Member ' + (i + 1) + '</p>'
                    + '</div>'
                + '</div>'
                + '<div class="tm-chips">' + roleChips + '</div>'
                + activityBtn
            + '</div>';
        }).join('');
    }

    renderTeamOverview(groupName);
    closeMemberActivityPanel();
    renderTeamSimRoles();
    renderTeamApproval();

    teamModalActivityLogs = logs;
    teamModalActivityPage = 1;
    teamModalActivityTab = null;
    renderTeamModalActivityPage();

    // Overview by default, so the modal never reappears on the other tab; Review
    // Submission asks for Tasks instead, already filtered above.
    switchTeamModalTab(options.tab || 'overview');
    loadTeamHotelConcept(groupName);

    document.getElementById('teamInfoModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

/* The Overview tab is the team card's hidden [data-team-overview] block, copied
   as it stands now - the live poll keeps that block current, so a copy taken
   on open shows the latest review notices. */
function renderTeamOverview(groupName) {
    const body = document.getElementById('teamModalOverviewBody');
    if (!body) return;
    const want = String(groupName || '').toLowerCase();
    const card = Array.from(document.querySelectorAll('.team-card[data-team-name]'))
        .find((c) => (c.dataset.teamName || '').toLowerCase() === want);
    const source = card && card.querySelector('[data-team-overview]');
    body.innerHTML = source
        ? source.innerHTML
        : '<div class="tm-empty">No overview for this team.</div>';

    const waiting = body.querySelectorAll('.ov-notice:not(.hidden)').length;
    const count = document.getElementById('teamTabCountOverview');
    if (count) count.textContent = waiting ? waiting + ' to review' : '';
}

/* Task Submission Indicator's Review Submission button: the same team modal,
   opened straight onto Tasks and filtered to what is actually awaiting a
   verdict, rather than making faculty find it in the full activity list. */
function openTeamModalAwaitingReview(groupName, members, createdAt, activityLogs) {
    openTeamModal(groupName, members, createdAt, activityLogs, { tab: 'tasks', onlyAwaitingReview: true });
}

/* Hotel Concept Submission Indicator's Review Concept button: same modal,
   opened onto the Hotel Concept tab — loadTeamHotelConcept() (already called
   by openTeamModal for every caller) is what actually fetches the concept
   text and its history, this just lands on the tab that shows it. */
function openTeamModalConceptReview(groupName, members, createdAt, activityLogs) {
    openTeamModal(groupName, members, createdAt, activityLogs, { tab: 'concept' });
}

/* ── Submission indicators: kept live ──────────────────────────────────────
   A student submitting is the one thing on this page worth hearing about
   within seconds, so the tick is 5s rather than a minute. Two requests would
   not fit that budget, so the loop is split:

     pulse   (cheap, every tick)  "has anything changed, and what is the bell on"
     payload (real, on change)    the full per-group shape the badges paint from

   The pulse also carries the bell's unread count, so the bell stops polling
   separately on this page (window.HMS_BELL_EXTERNAL_POLL below) and the whole
   page costs one request per tick instead of two.

   Every submission already fires its own faculty notification
   (Notifier::taskSubmitted, Notifier::conceptsSubmitted). The badges and the
   toast are built from the tasks table instead, never from those rows: opening
   the bell marks everything read, and Notifier swallows its own insert
   failures, so notification state cannot be trusted to mean "awaiting review".

   Three guards keep 5s affordable on one free instance against Supabase's
   transaction pooler. A hidden tab does not poll at all. An untouched visible
   tab walks 5s -> 15s -> 30s. A failed request drops straight to the slowest
   rung. Net effect versus the old flat 60s interval: a background tab costs
   nothing where it used to cost two requests a minute forever, and the fast
   rate applies only while somebody is actually working on the page. */
const TEAM_PENDING_REVIEW_URL = @json(route('faculty.role.pending-review'));
const TEAM_REVIEW_PULSE_URL   = @json(route('faculty.role.pulse'));
const TEAM_REVIEW_DEEPLINK_URL = @json(route('faculty.role', ['tab' => 'teams', 'team' => '__TEAM__']));

// The backoff ladder, in ms, and how many unchanged ticks it takes to step down.
const PULSE_RUNGS = [5000, 15000, 30000];
const PULSE_TICKS_PER_RUNG = 24;

let pulseRung = 0;
let pulseQuietTicks = 0;
let pulseLastSig = null;
let pulseInFlight = false;
let pulseTimer = null;

/* Seeded from the first paint, not left empty and not skipped for one tick.
   Empty would toast every already-pending submission the moment the page
   loads; skipping the first tick would instead swallow one that genuinely
   arrived in the seconds between render and first poll. */
let pendingReviewSeen  = seedSeenIds(@json($teamPendingReviewByGroup ?? []), 'task_ids');
let conceptPendingSeen = seedSeenIds(@json($teamConceptPendingByGroup ?? []), 'hotel_concept_ids');

function seedSeenIds(byGroup, key) {
    const seen = new Set();
    Object.keys(byGroup || {}).forEach((group) => {
        ((byGroup[group] || {})[key] || []).forEach((id) => seen.add(group + ':' + id));
    });
    return seen;
}

function updateSubmissionBadge(card, selectorPrefix, entry, headlineText, detailText) {
    const badge = card.querySelector('[data-' + selectorPrefix + '-badge]');
    if (!badge) return;

    badge.classList.toggle('hidden', !entry);
    if (!entry) return;

    const headline = badge.querySelector('[data-' + selectorPrefix + '-headline]');
    if (headline) headline.textContent = headlineText(entry);

    const detail = badge.querySelector('[data-' + selectorPrefix + '-detail]');
    if (detail && entry.latest) detail.textContent = detailText(entry.latest);
}

function updatePendingReviewBadges(data) {
    const tasks = (data && data.tasks) || {};
    const concepts = (data && data.concepts) || {};

    document.querySelectorAll('.team-card[data-team-name]').forEach((card) => {
        const team = card.dataset.teamName;

        updateSubmissionBadge(card, 'pending-review', tasks[team],
            (entry) => 'New Submission — ' + entry.count + ' Awaiting Review',
            (latest) => latest.student_name + ' · ' + latest.role_label
                + ' · "' + latest.title + '" · ' + pendingAgo(latest));

        updateSubmissionBadge(card, 'concept-pending', concepts[team],
            (entry) => 'Hotel Concept Submitted' + (entry.count > 1 ? ' — Both Proposals' : ''),
            (latest) => latest.submitted_by + ' · ' + latest.slot_label
                + ' · "' + latest.title + '" · ' + pendingAgo(latest));
    });
}

/* Ages the badge without a request. submitted_human is stamped server-side and
   the payload is now refetched only when the signature moves, so relying on it
   alone would leave a badge reading "2 minutes ago" an hour later. Falls back
   to the server's wording for a payload that predates submitted_ts. */
function pendingAgo(latest) {
    const ts = Number(latest && latest.submitted_ts);
    if (!ts) return (latest && latest.submitted_human) || '';

    const secs = Math.max(0, Math.floor(Date.now() / 1000) - ts);
    if (secs < 45) return 'just now';
    if (secs < 5400) {
        const mins = Math.round(secs / 60);
        return mins + (mins === 1 ? ' minute ago' : ' minutes ago');
    }
    if (secs < 79200) {
        const hrs = Math.round(secs / 3600);
        return hrs + (hrs === 1 ? ' hour ago' : ' hours ago');
    }
    const days = Math.round(secs / 86400);
    return days + (days === 1 ? ' day ago' : ' days ago');
}

/* What is genuinely new since the last look, keyed on the whole pending id set
   rather than on the latest row. A task sent back and resubmitted keeps its
   task_id, so the only reliable question is whether an id left the set and came
   back — which "latest changed" cannot answer, and which a count comparison
   misses outright when a review and an arrival land in the same tick. */
function collectNewSubmissions(byGroup, key, seen, describe) {
    const lines = [];
    const fresh = new Set();
    let firstGroup = null;

    Object.keys(byGroup || {}).forEach((group) => {
        const entry = byGroup[group] || {};
        (entry.task_ids || entry.hotel_concept_ids || []).forEach((id) => {
            const stamp = group + ':' + id;
            fresh.add(stamp);
            if (seen.has(stamp)) return;
            if (firstGroup === null) firstGroup = group;
            lines.push(describe(group, entry));
        });
    });

    return { lines, fresh, firstGroup };
}

function announceNewSubmissions(data) {
    const taskNew = collectNewSubmissions(
        (data && data.tasks) || {}, 'task_ids', pendingReviewSeen,
        (group, entry) => escHtml(teamLabel(group)) + ' — '
            + escHtml((entry.latest && entry.latest.student_name) || 'A student')
            + ' · ' + escHtml((entry.latest && entry.latest.role_label) || '')
            + ' · "' + escHtml((entry.latest && entry.latest.title) || '') + '"'
    );
    const conceptNew = collectNewSubmissions(
        (data && data.concepts) || {}, 'hotel_concept_ids', conceptPendingSeen,
        (group, entry) => escHtml(teamLabel(group)) + ' — Hotel Concept · '
            + escHtml((entry.latest && entry.latest.submitted_by) || 'A team member')
    );

    const lines = taskNew.lines.concat(conceptNew.lines);

    /* Mid-review, the confirm dialog owns the single SweetAlert slot. Returning
       without absorbing the new ids means the toast fires on the next tick
       instead of being wiped by the dialog. Badges still repaint either way. */
    if (lines.length && window.Swal && Swal.isVisible()) return;

    pendingReviewSeen = taskNew.fresh;
    conceptPendingSeen = conceptNew.fresh;

    if (!lines.length || !window.Swal) return;

    // One toast, never a loop: SweetAlert2 has a single active instance, so
    // consecutive fires would replace each other and only the last would show.
    const shown = lines.slice(0, 4);
    if (lines.length > shown.length) {
        shown.push('and ' + (lines.length - shown.length) + ' more');
    }
    const target = taskNew.firstGroup || conceptNew.firstGroup;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        iconColor: '#7B1730',
        title: lines.length === 1 ? 'New submission' : lines.length + ' new submissions',
        html: shown.join('<br>'),
        showConfirmButton: false,
        timer: 8000,
        timerProgressBar: true,
        width: '24rem',
        didOpen: (el) => {
            if (!target) return;
            el.style.cursor = 'pointer';
            el.addEventListener('click', () => {
                Swal.close();
                focusTeamAwaitingReview(target);
            });
        },
    });
}

/* One answer to "show me this team's submissions", shared by the toast click
   and the ?team= deep link a notification lands on. Falls back to navigating,
   because the payload spans every class block while the cards on screen belong
   only to the active one. */
function focusTeamAwaitingReview(groupName) {
    const want = String(groupName || '').toLowerCase(); // group_name is citext
    const card = Array.from(document.querySelectorAll('.team-card[data-team-name]'))
        .find((c) => (c.dataset.teamName || '').toLowerCase() === want);
    const badge = card && card.querySelector('[data-pending-review-badge]');
    const btn = badge && badge.querySelector('[data-review-submission]');

    // A hidden badge means it has already been reviewed; opening an empty
    // modal off a stale bell item would be worse than doing nothing.
    if (btn && !badge.classList.contains('hidden')) {
        if (typeof switchTab === 'function') switchTab('teams');
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        btn.click();
        return;
    }
    if (!card) {
        window.location.href = TEAM_REVIEW_DEEPLINK_URL
            .replace('__TEAM__', encodeURIComponent(groupName));
    }
}

function fetchPendingReviewPayload() {
    return fetch(TEAM_PENDING_REVIEW_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store',
    })
        .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
        .then((data) => {
            if (!data || typeof data !== 'object') return;
            // Announce before painting: the diff has to run against what the
            // human is still looking at, not against the repaint.
            announceNewSubmissions(data);
            updatePendingReviewBadges(data);
        });
}

// Kept as the old name so anything else calling it still works.
function pollPendingReview() {
    return fetchPendingReviewPayload().catch(() => {});
}

function schedulePulse(delay) {
    clearTimeout(pulseTimer);
    pulseTimer = setTimeout(pollReviewPulse, delay);
}

function resetPulseRate() {
    pulseQuietTicks = 0;
    if (pulseRung !== 0) {
        pulseRung = 0;
        schedulePulse(PULSE_RUNGS[0]);
    }
}

function pollReviewPulse() {
    // A hidden tab is rescheduled, never fetched: visibilitychange below wakes
    // it and drops it straight back to the fast rung.
    if (document.hidden || pulseInFlight) {
        schedulePulse(PULSE_RUNGS[pulseRung]);
        return;
    }

    pulseInFlight = true;
    fetch(TEAM_REVIEW_PULSE_URL, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
        cache: 'no-store',
    })
        .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
        .then((data) => {
            if (!data || typeof data.sig !== 'string') return;

            // While the panel is open it owns the count: open() has just marked
            // everything read, and overwriting that would resurrect the badge.
            if (window.HMS_BELL && !window.HMS_BELL.isOpen()) {
                window.HMS_BELL.setUnread(Number(data.unread) || 0);
            }

            if (data.sig === pulseLastSig) {
                pulseQuietTicks++;
                if (pulseQuietTicks >= PULSE_TICKS_PER_RUNG
                    && pulseRung < PULSE_RUNGS.length - 1) {
                    pulseRung++;
                    pulseQuietTicks = 0;
                }
                return;
            }

            // First tick establishes the baseline; the seen-sets were already
            // seeded from the first paint, so the payload fetch is still safe.
            pulseLastSig = data.sig;
            pulseQuietTicks = 0;
            pulseRung = 0;
            return fetchPendingReviewPayload();
        })
        .catch(() => {
            // Includes a cold start on the free instance, where the first
            // request can take most of a minute. Back all the way off.
            pulseRung = PULSE_RUNGS.length - 1;
            pulseQuietTicks = 0;
        })
        .finally(() => {
            pulseInFlight = false;
            schedulePulse(PULSE_RUNGS[pulseRung]);
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // The grid exists on every tab (only its own tab-panel is hidden), so one
    // loop keeps every card's badge current whichever tab is on screen.
    if (!document.getElementById('teamCardsGrid')) return;

    // The bell stops polling for itself here; the pulse carries its count.
    window.HMS_BELL_EXTERNAL_POLL = true;

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) resetPulseRate();
    });
    ['pointerdown', 'keydown', 'scroll'].forEach((evt) => {
        document.addEventListener(evt, resetPulseRate, { passive: true });
    });

    // A notification deep link names the team it is about.
    @if(filled(request('team')))
        focusTeamAwaitingReview(@json(request('team')));
    @endif

    schedulePulse(PULSE_RUNGS[0]);
});

/* Centralized activity log — the server decides whether this faculty may read it. */
const MEMBER_ACTIVITY_URL = @json(route('faculty.activity.user', ['user' => '__ID__']));

function closeMemberActivityPanel() {
    const panel = document.getElementById('memberActivityPanel');
    if (panel) panel.classList.add('hidden');
    document.querySelectorAll('[data-member-card].is-on').forEach((c) => c.classList.remove('is-on'));
}

/* Delegated: the buttons are rebuilt whenever the team modal opens, and an
   inline onclick cannot carry a name containing quotes without breaking the
   attribute it lives in. */
document.addEventListener('click', function (e) {
    const btn = e.target.closest ? e.target.closest('[data-activity-user]') : null;
    if (!btn) return;
    viewMemberActivity(btn.getAttribute('data-activity-user'), btn.getAttribute('data-activity-name'));
});

function viewMemberActivity(userId, memberName) {
    const panel = document.getElementById('memberActivityPanel');
    const title = document.getElementById('memberActivityPanelTitle');
    const body = document.getElementById('memberActivityPanelBody');
    if (!panel || !body) return;

    panel.classList.remove('hidden');
    document.querySelectorAll('[data-member-card]').forEach((c) => {
        c.classList.toggle('is-on', c.getAttribute('data-member-card') === String(userId));
    });
    if (title) title.textContent = 'What ' + (memberName || 'this member') + ' has done';
    body.innerHTML = '<div class="tm-empty">Loading activity…</div>';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    fetch(MEMBER_ACTIVITY_URL.replace('__ID__', String(userId)), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (res) {
            return res.json().then(function (data) {
                if (!res.ok) throw new Error(data.error || 'Could not load activity logs.');
                return data;
            });
        })
        .then(function (data) {
            body.innerHTML = renderActivityRows(data.logs || []);
        })
        .catch(function (err) {
            body.innerHTML = '<div class="tm-empty is-error">'
                + escHtml(err.message || 'Could not load activity logs.') + '</div>';
        });
}

function renderActivityRows(logs) {
    if (!logs.length) {
        return '<div class="tm-empty">No recorded activity for this member yet.</div>';
    }
    return logs.map(function (log) {
        return '<div class="tm-activity-row">'
            + '<span class="tm-chip">' + escHtml(log.activity_label || log.activity || 'Activity') + '</span>'
            + '<div class="min-w-0 flex-1">'
                + '<p class="tm-activity-desc">' + escHtml(log.description || '') + '</p>'
                + '<p class="tm-activity-time">' + escHtml(log.created_at || '')
                    + (log.created_at_human ? ' (' + escHtml(log.created_at_human) + ')' : '') + '</p>'
            + '</div>'
        + '</div>';
    }).join('');
}

/* A task verdict used to reload the page, which closed the Team Details modal
   under the faculty mid-review. The verdict is now painted into the Team Task
   Activity tab they are still on, and the page reloads only once they close the
   modal, so the team cards (and the task lists baked into their buttons) catch up. */
let teamModalStale = false;

function applyTaskVerdictInPlace(taskId, decision, d) {
    teamModalStale = true;
    const log = teamModalActivityLogs.find((l) => String(l.id) === String(taskId));
    if (log) {
        log.status = d.status;
        log.awaiting_review = false;
        log.edited_since_approval = false;
        log.state = decision === 'revise' ? 'revision' : 'approved';
        log.revision_count = d.revision_count;
    }
    renderTeamModalActivityPage();

    if (d.approval && teamModalGroup) {
        TEAM_APPROVAL[teamModalGroup] = d.approval;
        renderTeamSimRoles();
        renderTeamApproval();
    }
    fetchPendingReviewPayload().catch(() => {});
}

function closeTeamModal() {
    document.getElementById('teamInfoModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    if (teamModalStale) window.location.reload();
}

document.addEventListener('DOMContentLoaded', function () {
    const prevBtn = document.getElementById('teamModalActivityPrev');
    const nextBtn = document.getElementById('teamModalActivityNext');
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (teamModalActivityPage > 1) {
                teamModalActivityPage -= 1;
                renderTeamModalActivityPage();
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            const totalPages = Math.max(1, Math.ceil(teamModalActivityLogs.length / TEAM_MODAL_ACTIVITY_PER_PAGE));
            if (teamModalActivityPage < totalPages) {
                teamModalActivityPage += 1;
                renderTeamModalActivityPage();
            }
        });
    }
});
</script>
@endsection