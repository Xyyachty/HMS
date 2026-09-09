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

    /* Whether the design task that opens the Amenities section is assigned to
       this student and still open. The section's own controls read it, so a
       teammate — or the assignee after they have submitted — sees the finished
       cards without the tools that change them. A guest on the Mini Portfolio is
       never asked the question. */
    /* The page this role actually works on. Front Desk owns the home page, Room
       Management the rooms, Restaurant the menu, Housekeeping its two — so opening
       every one of them on Home means every student but one lands on somebody
       else's work and has to navigate out of it. Guests and the Mini Portfolio are
       not asked: a visitor always starts at the front of the site. */
    $hmsInitialPage = 'home';
    if (!$hmsPublicSlug && $hmsBuilderRole) {
        $hmsInitialPage = \App\Support\HotelTemplateBuilder::preferredPageForRole($hmsBuilderRole);
    }

    $hmsAmenityTask = null;
    if (!$hmsPublicSlug && $hmsCanEdit) {
        $hmsAmenityMembership = \App\Support\HotelAmenityAccess::membership();
        $hmsAmenityTask = $hmsAmenityMembership
            ? \App\Support\AmenityTaskDesk::payload($hmsAmenityMembership)
            : null;
    }
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.__HMS_CUSTOMIZATIONS__ = @json($hmsCustomizations);
    window.__HMS_CAN_EDIT__ = @json($hmsCanEdit);
    window.__HMS_EDITABLE_PAGES__ = @json($hmsEditablePages);
    window.__HMS_BUILDER_ROLE__ = @json($hmsBuilderRole);
    window.__HMS_AMENITY_TASK__ = @json($hmsAmenityTask);
    try {
        // Design tools only belong inside the builder iframe. A standalone tab
        // (e.g. "View Live") is always the read-only live site, regardless of
        // the visitor's underlying edit permission.
        if (!window.parent || window.parent === window) {
            window.__HMS_CAN_EDIT__ = false;
            window.__HMS_EDITABLE_PAGES__ = [];
            window.__HMS_AMENITY_TASK__ = null;
        }
    } catch (e) {
        window.__HMS_CAN_EDIT__ = false;
        window.__HMS_EDITABLE_PAGES__ = [];
        window.__HMS_AMENITY_TASK__ = null;
    }
    /* Read once, as the page the app opens on, so the role's own section is what
       paints rather than what it navigates to a moment later. */
    window.__HMS_INITIAL_PAGE__ = @json($hmsInitialPage);
    window.__HMS_CURRENT_PAGE__ = window.__HMS_INITIAL_PAGE__;
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
        amenityReservations: @json("/hotel/{$hmsPublicSlug}/api/amenity-reservations"),
        amenityVisits:       @json("/hotel/{$hmsPublicSlug}/api/amenity-visits"),
    };
    // No media upload: it is a named route inside the students auth group, and a
    // visitor has no business uploading to the team's site anyway.
    window.__HMS_MEDIA_UPLOAD_URL__ = null;
    /* Guest accounts, on the published site's own endpoints. The staff copies of
       these sit behind the students login, which is what left a visitor being told
       there was no accounts service on the page they most needed one. */
    window.__HMS_HOTEL_AUTH_ROUTES__ = {
        me:             @json("/hotel/{$hmsPublicSlug}/api/auth/me"),
        customerLogin:  @json("/hotel/{$hmsPublicSlug}/api/auth/login"),
        customerSignup: @json("/hotel/{$hmsPublicSlug}/api/auth/signup"),
        logout:         @json("/hotel/{$hmsPublicSlug}/api/auth/logout"),
    };
@else
    window.__HMS_PUBLIC__ = false;
    window.__HMS_API__ = {
        rooms:      '/students/hotel/rooms',
        addons:     '/students/hotel/addons',
        amenities:  '/students/hotel/amenities',
        menus:      '/students/hotel/menus',
        roomUpdate: '/students/hotel/rooms',
        bookings:   '/students/hotel/bookings',
        amenityReservations: '/students/hotel/amenity-reservations',
        amenityVisits:       '/students/hotel/amenity-visits',
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
<script src="{{ asset('js/hms-hotel-auth.js') }}"></script>
<script src="{{ asset('js/hms-template-editor.js') }}?v={{ filemtime(public_path('js/hms-template-editor.js')) }}"></script>
<script src="{{ asset('js/hms-site-content.js') }}?v={{ filemtime(public_path('js/hms-site-content.js')) }}"></script>
@if ($hmsReviewHighlight)
<script src="{{ asset('js/hms-review-highlight.js') }}?v={{ filemtime(public_path('js/hms-review-highlight.js')) }}"></script>
@endif
