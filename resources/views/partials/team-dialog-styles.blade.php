{{-- Team Details dialog (faculty Manage Teams and dean Teams Overview) and the
     faculty student task dialog. Written out rather than composed from
     utilities: public/css/app.css is a frozen build that lacks most of what
     these need. Both are wide and sized to the screen, gray like the rest of
     the staff pages; status badges keep their own colours. --}}
<style>
    #teamInfoModal .hidden,
    #taskReviewModal .hidden { display: none !important; }
    #taskReviewModal { z-index: 60; }

    .tm-box {
        width: min(94vw, 1240px);
        height: min(88vh, 820px);
        border-radius: 1.25rem;
        border: 1px solid #E4E2E0;
    }
    #taskReviewModal .review-modal-box { width: min(95vw, 1360px); height: min(90vh, 880px); }
    @media (max-width: 640px) {
        .tm-box, #taskReviewModal .review-modal-box { width: 96vw; height: 92vh; }
    }

    .tm-head {
        display: flex; align-items: center; gap: .9rem; flex-shrink: 0;
        padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #E4E2E0;
    }
    .tm-head-icon {
        width: 2.75rem; height: 2.75rem; border-radius: .85rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: #F3F2F1; color: #4A4643; font-size: 1.4rem;
    }
    .tm-kicker { font-size: 12px; font-weight: 700; color: #5F5A55; }
    .tm-title { font-size: 1.3rem; font-weight: 800; color: #181818; line-height: 1.25; }
    .tm-sub { font-size: 13px; color: #5F5A55; margin-top: .15rem; }
    .tm-close {
        margin-left: auto; flex-shrink: 0; display: inline-flex; align-items: center; gap: .35rem;
        height: 2.5rem; padding: 0 1rem; border-radius: .75rem; border: 1px solid #E4E2E0;
        background: #fff; color: #181818; font-size: 13px; font-weight: 700;
        transition: background-color .15s, border-color .15s;
    }
    .tm-close:hover { background: #dadada; border-color: #dadada; }

    /* Section list on the left, the chosen section filling the rest. */
    .tm-body { flex: 1; min-height: 0; display: flex; }
    .tm-nav {
        width: 18rem; flex-shrink: 0; overflow-y: auto; display: flex; flex-direction: column; gap: .4rem;
        padding: 1rem .75rem; background: #F3F2F1; border-right: 1px solid #E4E2E0;
    }
    .tm-tab {
        display: flex; align-items: flex-start; gap: .7rem; width: 100%; text-align: left;
        padding: .8rem .85rem; border-radius: .9rem; border: 1px solid transparent;
        background: transparent; color: #181818; transition: background-color .15s, border-color .15s;
    }
    .tm-tab:hover { background: #dadada; }
    .tm-tab.is-on { background: #fff; border-color: #E4E2E0; box-shadow: 0 6px 18px -10px rgba(24,24,24,.35); }
    .tm-tab-icon {
        width: 2.25rem; height: 2.25rem; border-radius: .7rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff; color: #4A4643; font-size: 1.15rem;
    }
    .tm-tab.is-on .tm-tab-icon { background: #4A4643; color: #fff; }
    .tm-tab-copy { flex: 1; min-width: 0; }
    .tm-tab-title { display: block; font-size: 14px; font-weight: 800; }
    .tm-tab-desc { display: block; font-size: 12px; color: #5F5A55; margin-top: .15rem; line-height: 1.4; }
    .tm-tab-count {
        display: inline-block; margin-top: .45rem; padding: .1rem .55rem; border-radius: 999px; white-space: nowrap;
        font-size: 11.5px; font-weight: 800; background: #fff; color: #181818; border: 1px solid #E4E2E0;
    }
    .tm-tab-count:empty { display: none; }
    .tm-tab-count.is-alert { background: #FEF3C7; color: #D97706; border-color: #FDE68A; }

    .tm-content { flex: 1; min-width: 0; overflow-y: auto; overscroll-behavior: contain; padding: 1.25rem 1.5rem 1.5rem; background: #fff; }
    .tm-panel-head { margin-bottom: 1rem; }
    .tm-panel-title { font-size: 1.1rem; font-weight: 800; color: #181818; }
    .tm-panel-note { font-size: 13px; color: #5F5A55; line-height: 1.5; max-width: 72ch; }
    .tm-panel-head .tm-panel-note { margin-top: .2rem; }

    @media (max-width: 860px) {
        .tm-body { flex-direction: column; }
        .tm-nav { width: 100%; flex-direction: row; overflow-x: auto; border-right: 0; border-bottom: 1px solid #E4E2E0; padding: .6rem; }
        .tm-tab { min-width: 13rem; }
        .tm-tab-desc { display: none; }
    }

    /* Shared pieces */
    .tm-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem; white-space: nowrap;
        height: 2.5rem; padding: 0 1rem; border-radius: .75rem; border: 1px solid #E4E2E0;
        background: #fff; color: #181818; font-size: 13px; font-weight: 700;
        transition: background-color .15s, border-color .15s, color .15s;
    }
    .tm-btn:hover { background: #dadada; border-color: #dadada; color: #181818; }
    .tm-btn-dark { background: #4A4643; border-color: #4A4643; color: #fff; }
    .tm-btn-sm { height: 2.1rem; padding: 0 .75rem; font-size: 12px; }
    .tm-btn:disabled, .tm-act:disabled { opacity: .4; cursor: not-allowed; }
    .tm-act {
        display: inline-flex; align-items: center; justify-content: center; gap: .4rem; white-space: nowrap;
        height: 2.6rem; padding: 0 1rem; border-radius: .75rem; font-size: 13px; font-weight: 800;
        transition: background-color .15s, opacity .15s;
    }
    .tm-actions-row { display: flex; flex-wrap: wrap; gap: .5rem; }
    .tm-actions-row > * { flex: 1 1 9rem; }

    .tm-card { border: 1px solid #E4E2E0; border-radius: 1rem; overflow: hidden; background: #fff; }
    .tm-card-head {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
        padding: .7rem 1rem; background: #EFEFEF; border-bottom: 1px solid #E4E2E0;
    }
    .tm-card-title { font-size: 14px; font-weight: 800; color: #181818; }
    .tm-section { border: 1px solid #E4E2E0; border-radius: 1rem; padding: .9rem 1rem; }
    .tm-section-title { display: flex; align-items: center; gap: .4rem; font-size: 14px; font-weight: 800; color: #181818; }
    .tm-section-title .iconify { color: #4A4643; }
    .tm-section-count { font-size: 12px; font-weight: 700; color: #5F5A55; }
    .tm-chips { display: flex; flex-wrap: wrap; gap: .35rem; }
    .tm-chip {
        display: inline-flex; align-items: center; gap: .3rem; padding: .2rem .65rem; border-radius: 999px;
        background: #F3F2F1; border: 1px solid #E4E2E0; color: #181818; font-size: 12px; font-weight: 700;
    }
    .tm-chip .iconify { color: #4A4643; }
    .tm-badge {
        display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .65rem; border-radius: 999px;
        border: 1px solid; font-size: 12px; font-weight: 800; white-space: nowrap;
    }
    .tm-dot { width: .45rem; height: .45rem; border-radius: 999px; flex-shrink: 0; }
    .tm-empty { padding: 2.5rem 1rem; text-align: center; font-size: 13.5px; color: #5F5A55; }
    .tm-empty .iconify { display: block; margin: 0 auto .5rem; font-size: 2rem; color: #8A817A; }
    .tm-empty.is-error { color: #DC2626; font-weight: 700; }
    .tm-alert {
        margin-bottom: .9rem; padding: .65rem .85rem; border-radius: .75rem;
        background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; font-size: 13px; font-weight: 700;
    }
    .tm-note { margin-top: .85rem; padding: .7rem .85rem; border-radius: .75rem; background: #F3F2F1; border: 1px solid #E4E2E0; }
    .tm-note-label { font-size: 12px; font-weight: 800; color: #181818; }
    .tm-note-text { font-size: 13px; color: #181818; line-height: 1.5; white-space: pre-line; }
    .tm-field-label { display: block; margin-bottom: .35rem; font-size: 13px; font-weight: 800; color: #181818; }
    .tm-textarea {
        width: 100%; padding: .65rem .8rem; border-radius: .75rem; border: 1px solid #DADADA;
        background: #fff; color: #181818; font-size: 13px; line-height: 1.5; resize: vertical;
    }
    .tm-textarea:focus { outline: none; border-color: #8A817A; box-shadow: 0 0 0 3px rgba(138,129,122,.2); }
    .tm-help { margin-top: .3rem; font-size: 12px; color: #5F5A55; }

    /* Members */
    .tm-member-grid { display: grid; gap: .85rem; grid-template-columns: repeat(auto-fill, minmax(15.5rem, 1fr)); }
    .tm-member {
        display: flex; flex-direction: column; gap: .8rem; padding: 1rem; border-radius: 1rem;
        border: 1px solid #E4E2E0; background: #fff; transition: border-color .15s, box-shadow .15s;
    }
    .tm-member.is-on { border-color: #4A4643; box-shadow: 0 0 0 3px rgba(74,70,67,.12); }
    .tm-member-top { display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .tm-avatar {
        width: 2.75rem; height: 2.75rem; border-radius: 999px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: #4A4643; color: #fff; font-size: 1rem; font-weight: 800;
    }
    .tm-avatar-sm { width: 1.75rem; height: 1.75rem; font-size: .7rem; }
    .tm-member-name { font-size: 14.5px; font-weight: 800; color: #181818; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tm-member-no { font-size: 12px; color: #5F5A55; }
    .tm-member .tm-btn { width: 100%; margin-top: auto; }
    .tm-activity-list { max-height: 20rem; overflow-y: auto; }
    .tm-activity-row { display: flex; align-items: flex-start; gap: .75rem; padding: .8rem 1rem; border-top: 1px solid #EFEFEF; }
    .tm-activity-row:first-child { border-top: 0; }
    .tm-activity-desc { font-size: 13px; color: #181818; line-height: 1.5; }
    .tm-activity-time { font-size: 12px; color: #5F5A55; margin-top: .15rem; }

    /* Hotel concept: the two proposals side by side. */
    .tm-concept-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
    .tm-span-all { grid-column: 1 / -1; }
    @media (max-width: 900px) { .tm-concept-grid { grid-template-columns: minmax(0, 1fr); } }
    .tm-concept-body { padding: 1rem; }
    .tm-concept-name { font-size: 1.1rem; font-weight: 800; color: #181818; }
    .tm-concept-tagline { margin-top: .1rem; font-size: 13px; font-style: italic; color: #4A4643; }
    .tm-concept-desc { margin-top: .65rem; font-size: 13.5px; color: #181818; line-height: 1.6; white-space: pre-line; }
    .tm-concept-meta { margin-top: .8rem; font-size: 12px; color: #5F5A55; line-height: 1.6; }
    .tm-concept-meta b { color: #181818; }
    .tm-concept-empty { border: 2px dashed #DADADA; border-radius: 1rem; padding: 2rem 1rem; text-align: center; }
    .tm-concept-actions { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #EFEFEF; }
    .tm-official { display: inline-flex; align-items: center; gap: .35rem; font-size: 13.5px; font-weight: 800; color: #16A34A; }
    .tm-history { margin-top: 1rem; }
    .tm-history-list { max-height: 16rem; overflow-y: auto; border: 1px solid #E4E2E0; border-radius: .75rem; }
    .tm-history-row { padding: .7rem .85rem; border-top: 1px solid #EFEFEF; font-size: 12.5px; color: #181818; }
    .tm-history-row:first-child { border-top: 0; }

    /* Task activity */
    .tm-stats { display: grid; gap: .75rem; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 1rem; }
    .tm-stat { display: flex; align-items: center; gap: .8rem; padding: .85rem 1rem; border-radius: 1rem; border: 1px solid #E4E2E0; }
    .tm-stat-icon {
        width: 2.5rem; height: 2.5rem; border-radius: .75rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem;
    }
    .tm-stat-num { font-size: 1.5rem; font-weight: 800; line-height: 1; }
    .tm-stat-label { margin-top: .2rem; font-size: 12.5px; font-weight: 700; color: #181818; }
    .tm-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .tm-table th { padding: .7rem 1rem; background: #EFEFEF; text-align: left; font-size: 12.5px; font-weight: 800; color: #181818; }
    .tm-table th.text-center { text-align: center; }
    .tm-table td { padding: .8rem 1rem; border-top: 1px solid #EFEFEF; vertical-align: middle; font-size: 13px; color: #181818; }
    .tm-table tbody tr:hover { background: #FAFAFA; }
    .tm-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tm-task-name { font-weight: 800; }
    .tm-muted { font-size: 12px; color: #5F5A55; }
    .tm-table .tm-btn, .tm-table .tm-act { width: 100%; }
    .tm-pager { padding: .65rem 1rem; border-top: 1px solid #EFEFEF; background: #FAFAFA; }
    .tm-pager-label { font-size: 12.5px; font-weight: 700; color: #5F5A55; }
    @media (max-width: 900px) { .tm-stats { grid-template-columns: minmax(0, 1fr); } }

    /* Student task dialog: the work on the left, notes and decision on the right. */
    .rv-meta { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .4rem; }
    .rv-status { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: flex-end; flex-shrink: 0; max-width: 22rem; }
    .rv-body { flex: 1; min-height: 0; display: flex; }
    .rv-work { flex: 1; min-width: 0; min-height: 0; display: flex; flex-direction: column; background: #EFEFEF; border-right: 1px solid #E4E2E0; }
    .rv-toolbar {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; flex-shrink: 0;
        padding: .7rem 1rem; background: #fff; border-bottom: 1px solid #E4E2E0;
    }
    .rv-work-label { font-size: 14px; font-weight: 800; color: #181818; }
    .rv-work-hint { font-size: 12px; color: #5F5A55; margin-top: .1rem; }
    .rv-work-hint #reviewHighlightStatus { font-weight: 700; margin-left: .25rem; }
    .rv-seg { display: inline-flex; padding: .2rem; gap: .2rem; border-radius: .75rem; background: #F3F2F1; border: 1px solid #E4E2E0; }
    .rv-seg button {
        display: inline-flex; align-items: center; gap: .3rem; padding: .4rem .9rem; border-radius: .55rem;
        font-size: 12.5px; font-weight: 700; color: #5F5A55; transition: background-color .15s, color .15s;
    }
    .rv-seg button:hover { background: #dadada; color: #181818; }
    .rv-seg button.is-on { background: #4A4643; color: #fff; }
    .rv-side { width: 24rem; flex-shrink: 0; min-height: 0; display: flex; flex-direction: column; background: #fff; }
    .rv-side-scroll { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 1rem; display: flex; flex-direction: column; gap: .75rem; }
    .rv-bar { height: .55rem; margin-top: .65rem; border-radius: 999px; background: #EFEFEF; overflow: hidden; }
    .rv-bar > span { display: block; height: 100%; border-radius: 999px; transition: width .4s ease; }
    .rv-bar > span:not([class*="status-fill-"]) { background: #4A4643; }
    .rv-steps { margin-top: .75rem; display: flex; flex-direction: column; gap: .45rem; }
    .rv-steps li { display: flex; align-items: flex-start; gap: .5rem; font-size: 13px; line-height: 1.45; color: #181818; }
    .rv-steps li .iconify { flex-shrink: 0; margin-top: .1rem; font-size: 1rem; color: #8A817A; }
    .rv-steps li.is-done { color: #5F5A55; }
    .rv-steps li.is-done .iconify { color: #16A34A; }
    .rv-error { margin: 0 1rem .5rem; padding: .6rem .8rem; border-radius: .75rem; background: #FEE2E2; border: 1px solid #FECACA; color: #DC2626; font-size: 12.5px; font-weight: 700; }
    .rv-decision { flex-shrink: 0; padding: 1rem; border-top: 1px solid #E4E2E0; background: #FAFAFA; }

    /* Stacked on a narrow screen: the work keeps the larger share. */
    @media (max-width: 1023px) {
        .rv-body { flex-direction: column; }
        .rv-work { flex: 1 1 60%; border-right: 0; border-bottom: 1px solid #E4E2E0; }
        .rv-side { width: 100%; flex: 1 1 40%; }
        .tm-head { flex-wrap: wrap; }
        .rv-status { justify-content: flex-start; max-width: none; }
    }
</style>
