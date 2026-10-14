{{--
    Portal size for the student, faculty and dean portals: on a 1920px-wide
    screen they read the way they did at 125% browser zoom.

    Everything in these portals is sized in rem, so the root size scales it all
    (type, cards, icons, padding, the sidebar). It grows with the window from
    the browser's own 16px at 1536px wide to 20px at 1920px and stops there.
    Inside that range the page is always 96rem wide, the width the 125% look
    was laid out in, so no Tailwind breakpoint (all of them px) ever sees a page
    narrower than it was built for. At 1536px and below nothing changes.
--}}
<style>
    html { font-size: clamp(1rem, calc(100vw / 96), 1.25rem); }
</style>
