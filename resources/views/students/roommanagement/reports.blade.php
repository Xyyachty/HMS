@extends('students.builder.ops-shell')

@php $backRoute = 'students.roommanagement.manage'; @endphp

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
    // Every stay this team has taken. The report keeps the ones that finished.
    bookingsUrl: @json(route('students.hotel.bookings.index')),
    roomsUrl: @json(route('students.hotel.rooms.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;
@endverbatim
@include('students.partials.reports-shared')
@verbatim

/* Room Management's finished work is a stay that has ended.
   A stay is counted on the day it checked out — the day the room came back to this
   department — not the day it was booked or the day the guest arrived. */
function StayTable({ rows, page, onPage, rangeLabel }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const view = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (!rows.length) {
    return <EmptyState icon="fa-bed" message={`No stays were completed in ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Room</th>
              <th>Guest</th>
              <th>Stay</th>
              <th className="rp-num">Blocks</th>
              <th className="rp-num">Room</th>
              <th className="rp-num">Extras</th>
              <th className="rp-num">Total</th>
              <th>Checked Out</th>
            </tr>
          </thead>
          <tbody>
            {view.map(r => {
              // Everything on the bill that was not the room itself: food, add-ons,
              // and anything the desk typed in by hand.
              const extras = (Number(r.roomServiceTotal) || 0)
                + (Number(r.addonsTotal) || 0)
                + (Number(r.otherCharges) || 0);
              return (
                <tr key={r.bookingId}>
                  <td className="rp-strong">{r.roomName || '—'}</td>
                  <td>{r.fullName || 'Guest'}</td>
                  <td>{formatDate(r.checkIn)} – {formatDate(r.checkOut)}</td>
                  <td className="rp-num">{r.blocks || '—'}</td>
                  <td className="rp-num rp-money">{formatPeso(r.totalDue)}</td>
                  <td className={'rp-num ' + (extras > 0 ? 'rp-muted-money' : 'rp-zero')}>
                    {extras > 0 ? formatPeso(extras) : '—'}
                  </td>
                  <td className="rp-num rp-money">{formatPeso(r.grandTotal)}</td>
                  <td>{formatWhen(r.checkedOutAt)}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

/* Which rooms actually earned. Built from the same completed stays rather than a
   second query, so the two tables can never disagree. */
function RoomBreakdown({ rows }) {
  const byRoom = {};
  rows.forEach(r => {
    const key = r.roomName || '—';
    if (!byRoom[key]) byRoom[key] = { room: key, stays: 0, blocks: 0, revenue: 0 };
    byRoom[key].stays += 1;
    byRoom[key].blocks += Number(r.blocks) || 0;
    byRoom[key].revenue += Number(r.grandTotal) || 0;
  });
  const list = Object.values(byRoom).sort((a, b) => b.revenue - a.revenue);

  if (!list.length) return null;

  return (
    <div style={{ marginTop: '1.5rem' }}>
      <p className="rp-tile-label" style={{ marginBottom: '0.5rem' }}>By room</p>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Room</th>
              <th className="rp-num">Stays</th>
              <th className="rp-num">Blocks Sold</th>
              <th className="rp-num">Revenue</th>
            </tr>
          </thead>
          <tbody>
            {list.map(r => (
              <tr key={r.room}>
                <td className="rp-strong">{r.room}</td>
                <td className="rp-num">{r.stays}</td>
                <td className="rp-num">{r.blocks}</td>
                <td className="rp-num rp-money">{formatPeso(r.revenue)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function App() {
  const { preset, from, to, setPreset, onFrom, onTo, rangeLabel } = useReportRange();
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  const { rows: bookings, loading } = useReportFeed(CFG.bookingsUrl, d => d.bookings);

  const completed = useMemo(() => {
    const q = search.trim().toLowerCase();
    return (bookings || [])
      // A stay is finished when it reads Checked Out. Cancelled stays never happened
      // and open ones have not finished, so neither is this department's completed work.
      .filter(b => b.status === 'Checked Out')
      .filter(b => inWindow(localDayKey(b.checkedOutAt), from, to))
      .filter(b => !q
        || String(b.roomName || '').toLowerCase().includes(q)
        || String(b.fullName || '').toLowerCase().includes(q))
      .sort((a, b) => (String(a.checkedOutAt || '') < String(b.checkedOutAt || '') ? 1 : -1));
  }, [bookings, from, to, search]);

  const totals = useMemo(() => completed.reduce((acc, r) => ({
    room:   acc.room + (Number(r.totalDue) || 0),
    extras: acc.extras + (Number(r.roomServiceTotal) || 0) + (Number(r.addonsTotal) || 0) + (Number(r.otherCharges) || 0),
    grand:  acc.grand + (Number(r.grandTotal) || 0),
    blocks: acc.blocks + (Number(r.blocks) || 0),
  }), { room: 0, extras: 0, grand: 0, blocks: 0 }), [completed]);

  // A filter change can strand the view on a page that no longer exists.
  useEffect(() => { setPage(1); }, [from, to, search]);

  return (
    <div style={{ padding: '1.5rem' }}>
      <ReportHead
        eyebrow="Room Management"
        title="Reports"
        blurb="Stays this department saw through to check-out — what each room sold and earned."
      />

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '0.7rem', marginBottom: '1.2rem' }}>
        <Tile label="Stays Completed" value={completed.length} sub={rangeLabel} />
        <Tile label="Blocks Sold" value={totals.blocks} sub="12 hours each" />
        <Tile label="Room Revenue" value={formatPeso(totals.room)} sub="the rooms alone" />
        <Tile label="Total Billed" value={formatPeso(totals.grand)} sub={`incl. ${formatPeso(totals.extras)} extras`} grand />
      </div>

      <FilterBar
        preset={preset} from={from} to={to}
        onPreset={setPreset} onFrom={onFrom} onTo={onTo}
        search={search} onSearch={setSearch}
        searchPlaceholder="Room or guest name"
      />

      {loading
        ? <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem' }}>Loading stays…</p>
        : <>
            <StayTable rows={completed} page={page} onPage={setPage} rangeLabel={rangeLabel} />
            <RoomBreakdown rows={completed} />
          </>}

      <p className="rp-note">
        Counted on the day the guest checked out — {rangeLabel}
        {search ? `, matching "${search}"` : ''}.
      </p>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
