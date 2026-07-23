{{-- Injected into hotel templates for click-to-edit --}}
@php
    $hmsCustomizations = $customizations ?? [];
    $hmsCanEdit = (bool) ($canEditTemplate ?? false);
    $hmsEditablePages = $editablePages ?? [];
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    window.__HMS_CUSTOMIZATIONS__ = @json($hmsCustomizations);
    window.__HMS_CAN_EDIT__ = @json($hmsCanEdit);
    window.__HMS_EDITABLE_PAGES__ = @json($hmsEditablePages);
    window.__HMS_CURRENT_PAGE__ = 'home';
    window.__HMS_CSRF__ = @json(csrf_token());
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
<script src="{{ asset('js/hms-site-content.js') }}"></script>
