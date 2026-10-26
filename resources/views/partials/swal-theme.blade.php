{{-- One look for every SweetAlert dialog (toasts keep theirs): no ring, the icon
     sits in a soft circle tinted from its own color, bold title, muted text, and
     wide rounded buttons with an outlined Cancel.

     Colors still come from each call: iconColor is an inline style and
     confirmButtonColor sets --swal2-confirm-button-background-color on the button,
     so both win over the defaults here, and the circle tint follows the
     icon color through currentColor. Calls that set no color get the site's
     maroon and gold. Popups with their own background / color (the dark builder
     confirm) keep them: title, text and Cancel inherit the popup color. --}}
<style>
    .swal2-container .swal2-popup:not(.swal2-toast) { padding: 2.25rem 2rem 2rem; border-radius: 1.5rem; color: #111827; font-family: inherit; --swal2-confirm-button-background-color: #7B1730; box-shadow: 0 24px 60px rgba(17, 24, 39, .18); }

    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon { margin: 0 auto 1.25rem; border-width: 0; background-color: rgba(123, 23, 48, .1); background-color: color-mix(in srgb, currentColor 13%, transparent); }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon .swal2-icon-content { font-size: 3.25rem; font-weight: 800; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon.swal2-success { color: #B8873C; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon.swal2-error,
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon.swal2-warning,
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon.swal2-question { color: #7B1730; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon.swal2-info { color: #4A4643; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon [class^="swal2-success-line"],
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon [class^="swal2-x-mark-line"] { background-color: currentColor; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon .swal2-success-ring { display: none; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon [class^="swal2-success-circular-line"],
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-icon .swal2-success-fix { background-color: transparent !important; }

    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-title { padding: 0; color: inherit; font-size: 1.65rem; font-weight: 800; line-height: 1.25; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-html-container { margin: .75rem 0 0; padding: 0; color: inherit; opacity: .72; font-size: 1.02rem; line-height: 1.55; }

    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-actions { width: 100%; margin: 1.75rem auto 0; padding: 0; gap: .85rem; flex-wrap: nowrap; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-actions button { flex: 0 1 11rem; min-width: 0; margin: 0; padding: .8rem 1rem; border-radius: .75rem; font: inherit; font-size: 1rem; font-weight: 600; line-height: 1.25; cursor: pointer; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-confirm { background: var(--swal2-confirm-button-background-color); color: #fff; border: 0; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-confirm:hover { filter: brightness(.92); }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-cancel { background: transparent !important; color: inherit !important; border: 1.5px solid rgba(107, 114, 128, .35); border: 1.5px solid color-mix(in srgb, currentColor 22%, transparent); }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-cancel:hover { background: rgba(107, 114, 128, .08) !important; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-actions button:focus { box-shadow: none; }
    .swal2-container .swal2-popup:not(.swal2-toast) .swal2-actions button:focus-visible { outline: 3px solid rgba(123, 23, 48, .3); outline-offset: 2px; }
</style>
