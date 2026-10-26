{{-- One colour per work status, shared by every page that shows one, so a
     status reads the same to a student, their faculty and the dean.
     status-badge-* paints a badge (text, light background, and the border on
     badges that draw one); status-fill-* paints a dot or progress bar in the
     same hue; status-text-* colours a bare label. Plain CSS rather than
     utilities: the faculty pages load the frozen public/css/app.css, which has
     none of these colours. The class is tripled to outrank .ink-all's black
     text, and this partial is included after it so a hover tie still goes here. --}}
<style>
    .status-badge-not_started.status-badge-not_started.status-badge-not_started { color:#6B7280; background-color:#F3F4F6; border-color:#E5E7EB; }
    .status-badge-in_progress.status-badge-in_progress.status-badge-in_progress { color:#2563EB; background-color:#DBEAFE; border-color:#BFDBFE; }
    .status-badge-revision.status-badge-revision.status-badge-revision          { color:#DC2626; background-color:#FEE2E2; border-color:#FECACA; }
    .status-badge-pending.status-badge-pending.status-badge-pending             { color:#D97706; background-color:#FEF3C7; border-color:#FDE68A; }
    .status-badge-completed.status-badge-completed.status-badge-completed       { color:#16A34A; background-color:#DCFCE7; border-color:#BBF7D0; }

    .status-fill-not_started { background-color:#6B7280; }
    .status-fill-in_progress { background-color:#2563EB; }
    .status-fill-revision    { background-color:#DC2626; }
    .status-fill-pending     { background-color:#D97706; }
    .status-fill-completed   { background-color:#16A34A; }

    .status-text-not_started.status-text-not_started.status-text-not_started { color:#6B7280; }
    .status-text-in_progress.status-text-in_progress.status-text-in_progress { color:#2563EB; }
    .status-text-revision.status-text-revision.status-text-revision          { color:#DC2626; }
    .status-text-pending.status-text-pending.status-text-pending             { color:#D97706; }
    .status-text-completed.status-text-completed.status-text-completed       { color:#16A34A; }
</style>
