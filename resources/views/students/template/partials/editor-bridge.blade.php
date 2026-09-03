{{-- Injected into hotel templates for click-to-edit --}}
@php
    $hmsCustomizations = $customizations ?? [];
    $hmsCanEdit = (bool) ($canEditTemplate ?? false);
    $hmsEditablePages = $editablePages ?? [];
    $hmsBuilderRole = $builderRole ?? null;
    $hmsReviewHighlight = $reviewHighlight ?? null;

    // Set only by PublicSiteController. Its presence is the difference between the
    // builder's copy of the site and the Mini Portfolio a guest visits.
    $hmsPublicSlug = $publicSlug ?? null;
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.__HMS_CUSTOMIZATIONS__ = @json($hmsCustomizations);
    window.__HMS_CAN_EDIT__ = @json($hmsCanEdit);
    window.__HMS_EDITABLE_PAGES__ = @json($hmsEditablePages);
    window.__HMS_BUILDER_ROLE__ = @json($hmsBuilderRole);
    try {
        // Design tools only belong inside the builder iframe. A standalone tab
        // (e.g. "View Live") is always the read-only live site, regardless of
        // the visitor's underlying edit permission.
        if (!window.parent || window.parent === window) {
            window.__HMS_CAN_EDIT__ = false;
            window.__HMS_EDITABLE_PAGES__ = [];
        }
    } catch (e) {
        window.__HMS_CAN_EDIT__ = false;
        window.__HMS_EDITABLE_PAGES__ = [];
    }
    window.__HMS_CURRENT_PAGE__ = 'home';
    // Set only on the faculty Before/After preview — drives hms-review-highlight.js.
    window.__HMS_REVIEW_HIGHLIGHT__ = @json($hmsReviewHighlight);
    window.__HMS_CSRF__ = @json(csrf_token());

@if ($hmsPublicSlug)
    /* ── Mini Portfolio ───────────────────────────────────────────────────────
       A guest has no login, so every /students/* endpoint would 302 them to the
       login page. The template reads this map instead of its hardcoded paths.

       Four reads and one write, all slug-scoped and all narrower than the staff
       endpoints they mirror. roomUpdate is deliberately absent: changing a room's
       status is Housekeeping's, and the picker that calls it is staff-only UI a
       visitor never sees. */
    window.__HMS_PUBLIC__ = true;
    // Root-relative on purpose. url() would bake in APP_URL, which is wrong the moment
    // the app is reached on any other host — behind a proxy, or locally while APP_URL
    // still points at the deployed site.
    window.__HMS_API__ = {
        rooms:     @json("/hotel/{$hmsPublicSlug}/api/rooms"),
        addons:    @json("/hotel/{$hmsPublicSlug}/api/addons"),
        amenities: @json("/hotel/{$hmsPublicSlug}/api/amenities"),
        menus:     @json("/hotel/{$hmsPublicSlug}/api/menus"),
        bookings:  @json("/hotel/{$hmsPublicSlug}/api/bookings"),
    };
    // No media upload and no hotel-auth routes: both are named routes inside the
    // students auth group, and hms-hotel-auth.js boots by calling one of them.
    window.__HMS_MEDIA_UPLOAD_URL__ = null;
@else
    window.__HMS_PUBLIC__ = false;
    window.__HMS_API__ = {
        rooms:      '/students/hotel/rooms',
        addons:     '/students/hotel/addons',
        amenities:  '/students/hotel/amenities',
        menus:      '/students/hotel/menus',
        roomUpdate: '/students/hotel/rooms',
        bookings:   '/students/hotel/bookings',
    };
    window.__HMS_MEDIA_UPLOAD_URL__ = @json(route('students.frontdesk.template.media'));
    window.__HMS_HOTEL_AUTH_ROUTES__ = {
        me: @json(route('students.hotel-auth.me')),
        staffLogin: @json(route('students.hotel-auth.staff.login')),
        customerLogin: @json(route('students.hotel-auth.customer.login')),
        customerSignup: @json(route('students.hotel-auth.customer.signup')),
        logout: @json(route('students.hotel-auth.logout')),
    };
@endif
</script>
@unless ($hmsPublicSlug)
<script src="{{ asset('js/hms-hotel-auth.js') }}"></script>
@endunless
<script src="{{ asset('js/hms-template-editor.js') }}?v={{ filemtime(public_path('js/hms-template-editor.js')) }}"></script>
<script src="{{ asset('js/hms-site-content.js') }}?v={{ filemtime(public_path('js/hms-site-content.js')) }}"></script>
@if ($hmsReviewHighlight)
<script src="{{ asset('js/hms-review-highlight.js') }}?v={{ filemtime(public_path('js/hms-review-highlight.js')) }}"></script>
@endif
