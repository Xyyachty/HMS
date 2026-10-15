{{-- "Log out?" confirm for every Sign Out / Logout form that posts to the logout
     route: the sidebar profile card (dean, faculty, student dashboard) and the
     Staff Tools / department profile dropdowns. One delegated listener, so a new
     logout form gets the same dialog without wiring of its own. Needs SweetAlert2
     on the page; without it the form submits as normal. The look comes from
     partials.swal-theme. --}}
@auth
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
            iconColor: '#7B1730',
            showCancelButton: true,
            confirmButtonText: 'Logout',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#7B1730',
            reverseButtons: true,
            heightAuto: false
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });
})();
</script>
@endauth
