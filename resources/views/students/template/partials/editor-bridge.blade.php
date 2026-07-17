{{-- Injected into hotel templates for click-to-edit support --}}
@php
    $hmsCustomizations = $customizations ?? [];
    $hmsCanEdit = (bool) ($canEditTemplate ?? false);
@endphp
<script>
    window.__HMS_CUSTOMIZATIONS__ = @json($hmsCustomizations);
    window.__HMS_CAN_EDIT__ = @json($hmsCanEdit);
</script>
<script src="{{ asset('js/hms-template-editor.js') }}"></script>
