@extends('students.builder.ops-shell')

@php $backRoute = 'students.housekeeping'; @endphp

@section('page-title', 'Reports')

@section('head-extra')
@include('students.partials.reports-styles')
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
window.HMS_REPORTS = {
  backUrl: @json(route($backRoute)),
  inspectionsUrl: @json(route('students.hotel.inspections.index')),
  complaintsUrl: @json(route('students.hotel.complaints.index')),
};
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;
@endverbatim
@include('students.partials.reports-shared')
@verbatim

function CompletedTable({ rows, page, onPage, rangeLabel }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const view = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);
  if (!rows.length) return <EmptyState icon="fa-broom" message={`No housekeeping work was completed in ${rangeLabel}.`} />;

  return <>
    <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
      <table className="rp-table">
        <thead><tr><th>Type</th><th>Room</th><th>Guest / Finding</th><th>Details</th><th>Completed By</th><th>Completed</th><th>Time Taken</th></tr></thead>
        <tbody>{view.map(row => <tr key={row.key}>
          <td className="rp-strong">{row.type}</td><td>{row.room || 'N/A'}</td><td>{row.subject || 'N/A'}</td>
          <td>{row.details || 'None recorded'}</td><td>{row.by || 'Team member'}</td>
          <td>{formatWhen(row.completedAt)}</td><td>{formatElapsed(row.startedAt, row.completedAt)}</td>
        </tr>)}</tbody>
      </table>
    </div>
    <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
  </>;
}

function App() {
  const { preset, from, to, setPreset, onFrom, onTo, rangeLabel } = useReportRange();
  const [tab, setTab] = useState('all'); const [search, setSearch] = useState(''); const [page, setPage] = useState(1);
  const inspectionsFeed = useReportFeed(CFG.inspectionsUrl, d => d.inspections);
  const complaintsFeed = useReportFeed(CFG.complaintsUrl, d => d.complaints);

  const inspections = useMemo(() => (inspectionsFeed.rows || []).filter(r => r.status === 'Completed').map(r => ({
    key: `inspection-${r.id}`, kind: 'inspection', type: 'Room Inspection', room: r.roomName,
    subject: r.finding || r.guestName, details: r.notes, by: r.completedBy || r.inspectedBy,
    startedAt: r.inspectedAt || r.checkedOutAt, completedAt: r.completedAt,
  })), [inspectionsFeed.rows]);
  const complaints = useMemo(() => (complaintsFeed.rows || []).filter(r => r.department === 'housekeeping' && r.status === 'Resolved').map(r => ({
    key: `complaint-${r.id}`, kind: 'complaint', type: 'Resolved Concern', room: r.roomNumber,
    subject: r.category, details: r.resolutionNote || r.details, by: r.handledBy,
    startedAt: r.filedAt, completedAt: r.resolvedAt,
  })), [complaintsFeed.rows]);
  const all = useMemo(() => inspections.concat(complaints), [inspections, complaints]);
  const rows = useMemo(() => { const q = search.trim().toLowerCase(); return all
    .filter(r => tab === 'all' || r.kind === tab).filter(r => inWindow(localDayKey(r.completedAt), from, to))
    .filter(r => !q || [r.room, r.subject, r.details, r.by].some(v => String(v || '').toLowerCase().includes(q)))
    .sort((a,b) => String(b.completedAt || '').localeCompare(String(a.completedAt || ''))); }, [all, tab, search, from, to]);
  useEffect(() => setPage(1), [tab, search, from, to]);

  return <div style={{ padding: '1.5rem' }}>
    <ReportHead eyebrow="Housekeeping" title="Reports" blurb="Completed room inspections and guest concerns resolved by Housekeeping." />
    <div style={{ display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))', gap:'0.7rem', marginBottom:'1.2rem' }}>
      <Tile label="All Completed" value={all.length} sub="all time" grand /><Tile label="Inspections" value={inspections.length} sub="rooms returned to service" /><Tile label="Concerns Resolved" value={complaints.length} sub="housekeeping concerns" />
    </div>
    <TabBar tab={tab} onTab={setTab} tabs={[{key:'all',label:'All',count:all.length},{key:'inspection',label:'Inspections',count:inspections.length},{key:'complaint',label:'Concerns',count:complaints.length}]} />
    <FilterBar preset={preset} from={from} to={to} onPreset={setPreset} onFrom={onFrom} onTo={onTo} search={search} onSearch={setSearch} searchPlaceholder="Room, finding, or staff member" />
    {inspectionsFeed.loading || complaintsFeed.loading ? <p style={{color:'var(--fg-muted)'}}>Loading completed work...</p> : <CompletedTable rows={rows} page={page} onPage={setPage} rangeLabel={rangeLabel} />}
  </div>;
}
ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
