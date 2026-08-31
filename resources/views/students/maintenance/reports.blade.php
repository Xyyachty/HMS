@extends('students.builder.ops-shell')

@php $backRoute = 'students.maintenance'; @endphp
@section('page-title', 'Reports')
@section('head-extra')
@include('students.partials.reports-styles')
@endsection
@section('content')<div id="ops-root"></div>@endsection

@section('scripts')
<script>window.HMS_REPORTS = { backUrl: @json(route($backRoute)), complaintsUrl: @json(route('students.hotel.complaints.index')) };</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;
@endverbatim
@include('students.partials.reports-shared')
@verbatim

function RepairTable({ rows, page, onPage, rangeLabel }) {
  const totalPages=Math.max(1,Math.ceil(rows.length/PER_PAGE)); const safePage=Math.min(page,totalPages);
  const view=rows.slice((safePage-1)*PER_PAGE,safePage*PER_PAGE);
  if(!rows.length) return <EmptyState icon="fa-screwdriver-wrench" message={`No maintenance work was completed in ${rangeLabel}.`} />;
  return <><div style={{overflowX:'auto',borderRadius:10,border:'1px solid var(--border)'}}><table className="rp-table">
    <thead><tr><th>Room / Facility</th><th>Category</th><th>Reported Problem</th><th>Resolution</th><th>Completed By</th><th>Completed</th><th>Time Taken</th></tr></thead>
    <tbody>{view.map(r=><tr key={r.id}><td className="rp-strong">{r.roomNumber||'N/A'}</td><td>{r.category}</td><td>{r.details}</td><td>{r.resolutionNote||'Resolved'}</td><td>{r.handledBy||'Team member'}</td><td>{formatWhen(r.resolvedAt)}</td><td>{formatElapsed(r.filedAt,r.resolvedAt)}</td></tr>)}</tbody>
  </table></div><Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage}/></>;
}

function App(){
  const {preset,from,to,setPreset,onFrom,onTo,rangeLabel}=useReportRange(); const [search,setSearch]=useState(''); const [page,setPage]=useState(1);
  const {rows:complaints,loading}=useReportFeed(CFG.complaintsUrl,d=>d.complaints);
  const all=useMemo(()=>(complaints||[]).filter(r=>r.department==='maintenance'&&r.status==='Resolved'),[complaints]);
  const rows=useMemo(()=>{const q=search.trim().toLowerCase();return all.filter(r=>inWindow(localDayKey(r.resolvedAt),from,to)).filter(r=>!q||[r.roomNumber,r.category,r.details,r.resolutionNote,r.handledBy].some(v=>String(v||'').toLowerCase().includes(q))).sort((a,b)=>String(b.resolvedAt||'').localeCompare(String(a.resolvedAt||'')));},[all,search,from,to]);
  useEffect(()=>setPage(1),[search,from,to]);
  const avg=useMemo(()=>{const spans=rows.map(r=>new Date(r.resolvedAt)-new Date(r.filedAt)).filter(n=>Number.isFinite(n)&&n>=0);return spans.length?formatElapsed(new Date(0).toISOString(),new Date(spans.reduce((a,b)=>a+b,0)/spans.length).toISOString()):'N/A';},[rows]);
  return <div style={{padding:'1.5rem'}}><ReportHead eyebrow="Maintenance" title="Reports" blurb="Repairs and maintenance concerns completed by this department."/>
    <div style={{display:'grid',gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))',gap:'0.7rem',marginBottom:'1.2rem'}}><Tile label="Completed" value={rows.length} sub={rangeLabel} grand/><Tile label="All-Time Completed" value={all.length} sub="maintenance records"/><Tile label="Average Resolution" value={avg} sub="for the selected period"/></div>
    <FilterBar preset={preset} from={from} to={to} onPreset={setPreset} onFrom={onFrom} onTo={onTo} search={search} onSearch={setSearch} searchPlaceholder="Room, problem, or staff member"/>
    {loading?<p style={{color:'var(--fg-muted)'}}>Loading completed work...</p>:<RepairTable rows={rows} page={page} onPage={setPage} rangeLabel={rangeLabel}/>}</div>;
}
ReactDOM.createRoot(document.getElementById('ops-root')).render(<App/>);
</script>
@endverbatim
@endsection
