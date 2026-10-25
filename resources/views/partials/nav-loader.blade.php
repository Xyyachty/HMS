{{-- Loading circle while the next page loads, on every portal page: dean, faculty,
     the student dashboard and the Simulation pages. It hangs off beforeunload, so a
     link, a window.location change (the builder leaves that way) and a form post all
     get it. A page asking "Leave site?" keeps the circle off.
     A mailto:/tel:/download link fires beforeunload without leaving, so those are
     skipped, and the circle is cleared when the back/forward cache restores a page. --}}
<style>
    #hmsNavLoader {
        position: fixed; inset: 0; z-index: 99999;
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
    var skipUntil = 0;
    var hideTimer;

    window.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a[href]');
        if (link && (link.hasAttribute('download') || !/^https?:$/.test(link.protocol))) {
            skipUntil = Date.now() + 1000;
        }
    }, true);

    window.addEventListener('beforeunload', function (e) {
        if (Date.now() < skipUntil) return;
        // Next tick, after every other beforeunload listener has run, whenever it
        // was registered: one that asked "Leave site?" has marked the event by then.
        setTimeout(function () {
            if (e.defaultPrevented || (typeof e.returnValue === 'string' && e.returnValue !== '')) return;
            loader.classList.add('is-on');
            // ponytail: a link that turns out to be a file download never leaves the
            // page, so the circle clears itself; a page slower than this loses it early.
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () { loader.classList.remove('is-on'); }, 15000);
        }, 0);
    });

    window.addEventListener('pageshow', function () { loader.classList.remove('is-on'); });
})();
</script>
