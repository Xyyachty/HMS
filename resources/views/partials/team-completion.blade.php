{{-- A team's Team Task Completion Indicator: "All Tasks Completed" once faculty
     have approved every required customization task, otherwise "In Progress"
     with the approved count. Takes $done and $total (SimulationPhase::progress's
     approved / required — faculty approvals, not student submissions).

     Rendered here and by DeanController::teamProgressLive, so the live refresh
     paints exactly this markup. Styled inline: the frozen Tailwind build and the
     gray scheme would otherwise repaint the green. --}}
@php $complete = ($total ?? 0) > 0 && ($done ?? 0) >= $total; @endphp
@if($complete)
    <span title="Faculty approved every required task"
          style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.02em;white-space:nowrap;background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;">
        <span class="iconify" data-icon="mdi:check-decagram"></span> All Tasks Completed
    </span>
@else
    <span title="{{ $done }} of {{ $total }} required tasks approved by faculty"
          style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.02em;white-space:nowrap;background:#F3F2F1;color:#4A4643;border:1px solid #E4E2E0;">
        <span class="iconify" data-icon="mdi:progress-clock"></span> In Progress · {{ $done }}/{{ $total }}
    </span>
@endif
