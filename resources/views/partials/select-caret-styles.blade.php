{{--
    A down-chevron on every dropdown whose native arrow is switched off.

    The portals style their <select>s with Tailwind's appearance-none, which drops
    the browser's arrow, so a field like Status on the dean's Update User form read
    as a plain text box. This puts one back on all of them at once. The builder's
    .style-input selects draw their own caret and are left alone.
--}}
<style>
    select.appearance-none:not(.style-input) {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2394a3b8'%3E%3Cpath d='M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.15rem;
        padding-right: 2.25rem;
        cursor: pointer;
    }
</style>
