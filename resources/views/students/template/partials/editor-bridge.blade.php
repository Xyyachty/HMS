{{-- Injected into hotel templates for click-to-edit --}}
@php
    $hmsCustomizations = $customizations ?? [];
    $hmsCanEdit = (bool) ($canEditTemplate ?? false);
    $hmsEditablePages = $editablePages ?? [];
    $hmsBuilderRole = $builderRole ?? null;
    $hmsReviewHighlight = $reviewHighlight ?? null;
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
    window.__HMS_MEDIA_UPLOAD_URL__ = @json(route('students.frontdesk.template.media'));
    window.__HMS_HOTEL_AUTH_ROUTES__ = {
        me: @json(route('students.hotel-auth.me')),
        staffLogin: @json(route('students.hotel-auth.staff.login')),
        customerLogin: @json(route('students.hotel-auth.customer.login')),
        customerSignup: @json(route('students.hotel-auth.customer.signup')),
        logout: @json(route('students.hotel-auth.logout')),
    };
</script>
<script src="{{ asset('js/hms-hotel-auth.js') }}"></script>
<script src="{{ asset('js/hms-template-editor.js') }}?v={{ filemtime(public_path('js/hms-template-editor.js')) }}"></script>
<script src="{{ asset('js/hms-site-content.js') }}?v={{ filemtime(public_path('js/hms-site-content.js')) }}"></script>
@if ($hmsReviewHighlight)
<script src="{{ asset('js/hms-review-highlight.js') }}?v={{ filemtime(public_path('js/hms-review-highlight.js')) }}"></script>
@endif
