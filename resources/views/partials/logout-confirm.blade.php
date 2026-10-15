{{-- "Log out?" confirm for every Sign Out / Logout form that posts to the logout
     route: the sidebar profile card (dean, faculty, student dashboard) and the
     Staff Tools / department profile dropdowns. One delegated listener, so a new
     logout form gets the same dialog without wiring of its own. Needs SweetAlert2
     on the page; without it the form submits as normal. --}}
@auth
<style>
    .hms-logout-popup.swal2-popup { width: 34rem; max-width: calc(100vw - 2rem); padding: 2.5rem 2rem 2.25rem; border-radius: 1.5rem; font-family: inherit; }
    .hms-logout-popup .swal2-icon { width: 6rem; height: 6rem; margin: 0 auto 1.5rem; border: 0; background: #FDE8EA; }
    .hms-logout-popup .swal2-icon .swal2-icon-content { color: #DC1C35; font-size: 3.5rem; font-weight: 800; }
    .hms-logout-popup .swal2-title { padding: 0; color: #111827; font-size: 2rem; font-weight: 800; }
    .hms-logout-popup .swal2-html-container { margin: .85rem 0 0; padding: 0; color: #4B5563; font-size: 1.05rem; }
    .hms-logout-popup .swal2-actions { width: 100%; margin: 2rem 0 0; gap: .9rem; flex-wrap: nowrap; }
    .hms-logout-btn { flex: 0 1 11rem; padding: .85rem 1rem; border-radius: .75rem; font: inherit; font-size: 1.05rem; font-weight: 600; cursor: pointer; }
    .hms-logout-cancel { background: #fff; color: #1F2937; border: 1.5px solid #E5E7EB; }
    .hms-logout-cancel:hover { background: #F9FAFB; }
    .hms-logout-confirm { background: #DC1C35; color: #fff; border: 1.5px solid #DC1C35; }
    .hms-logout-confirm:hover { background: #C0182E; border-color: #C0182E; }
    .hms-logout-btn:focus-visible { outline: 3px solid rgba(220, 28, 53, .35); outline-offset: 2px; }
</style>
<script>
(function () {
    if (window.__hmsLogoutConfirm) return;
    window.__hmsLogoutConfirm = true;

    var LOGOUT_PATH = @json(route('logout', [], false));

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement) || typeof Swal === 'undefined') return;
        if (new URL(form.action, location.href).pathname !== LOGOUT_PATH) return;

        e.preventDefault();
        Swal.fire({
            title: 'Log out?',
            text: 'You will need to sign in again to access the dashboard.',
            icon: 'warning',
            iconHtml: '!',
            showCancelButton: true,
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            buttonsStyling: false,
            heightAuto: false,
            customClass: {
                popup: 'hms-logout-popup',
                confirmButton: 'hms-logout-btn hms-logout-confirm',
                cancelButton: 'hms-logout-btn hms-logout-cancel'
            }
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });
})();
</script>
@endauth
