{{-- Loading circle while a sidebar link (dean and faculty .nav-item) loads the next
     page, so a slow server read does not look like a click that did nothing.
     Not shown for a click that opens a new tab, and hidden again when the browser
     restores the page from its back/forward cache. --}}
<style>
    #hmsNavLoader {
        position: fixed; inset: 0; z-index: 9999;
        display: none; align-items: center; justify-content: center;
        background: rgba(255, 255, 255, 0.55);
    }
    #hmsNavLoader.is-on { display: flex; }
    #hmsNavLoader span {
        width: 3rem; height: 3rem; border-radius: 50%;
        border: 4px solid rgba(123, 23, 48, 0.2); border-top-color: #7B1730;
        animation: hmsNavSpin 0.8s linear infinite;
    }
    @keyframes hmsNavSpin { to { transform: rotate(360deg); } }
</style>
<div id="hmsNavLoader" role="status" aria-label="Loading"><span></span></div>
<script>
(function () {
    var loader = document.getElementById('hmsNavLoader');

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a.nav-item[href]');
        if (!link || e.defaultPrevented || e.button !== 0
            || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || link.target === '_blank') return;
        if (new URL(link.href, location.href).origin !== location.origin) return;
        loader.classList.add('is-on');
    });

    window.addEventListener('pageshow', function () { loader.classList.remove('is-on'); });
})();
</script>
