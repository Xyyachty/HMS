<?php
/*
 * Compares the imported Supabase data against the live MySQL database.
 *
 *   php database/checks/pgsql_data_verify.php
 *
 * POINT-IN-TIME CHECK — meaningful only immediately after the import. Once the app is
 * used on Supabase the two databases diverge by design: new logins append to
 * activity_logs, and every manual save writes a template version snapshot. For ongoing
 * health use database/checks/phase1_regression.php instead.
 *
 * Row counts per table, sequence positions, referential integrity, and a byte-level
 * spot check of the JSON and large-text columns. Exits non-zero on any mismatch.
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$my = DB::connection('mysql');
$pg = DB::connection('pgsql');

$fail = 0;
$check = function (string $label, bool $ok, string $detail = '') use (&$fail) {
    printf("  %-42s %s%s\n", $label, $ok ? 'PASS' : 'FAIL', $detail !== '' ? "  ($detail)" : '');
    if (!$ok) { $fail++; }
};

$tables = collect($pg->select(
    "select table_name t from information_schema.tables where table_schema='public' and table_type='BASE TABLE'"
))->pluck('t')->reject(fn ($t) => $t === 'migrations')->sort()->values();

echo "Row counts (Supabase vs MySQL)\n";
$totalPg = 0;
foreach ($tables as $t) {
    $pgCount = (int) $pg->select("select count(*) c from \"$t\"")[0]->c;
    $myCount = (int) $my->select("select count(*) c from `$t`")[0]->c;
    $totalPg += $pgCount;
    $check($t, $pgCount === $myCount, "pg=$pgCount mysql=$myCount");
}
printf("\n  total rows imported: %d\n", $totalPg);

echo "\nSequences (must be past max id, or the next insert collides)\n";
// Key column per table — no longer uniformly "id" since the PK rename.
foreach ([
    'users' => 'user_id',
    'tasks' => 'task_id',
    'students' => 'student_id',
    'hotel_menu_items' => 'hotel_menu_item_id',
    'template_content_fields' => 'template_content_field_id',
    'activity_logs' => 'activity_log_id',
] as $t => $key) {
    $max = (int) $pg->select("select coalesce(max(\"$key\"),0) m from \"$t\"")[0]->m;
    $seq = $pg->select("select pg_get_serial_sequence('public.$t','$key') s")[0]->s;
    $last = (int) $pg->select("select last_value l from $seq")[0]->l;
    $check("$t sequence", $last >= $max, "last=$last max=$max");
}

echo "\nReferential integrity\n";
$orphans = [
    'faculties -> users' => 'select count(*) c from faculties f left join users u on u.user_id = f.user_id where u.user_id is null',
    'students -> users' => 'select count(*) c from students s left join users u on u.user_id = s.user_id where u.user_id is null',
    'student_groups -> students' => 'select count(*) c from student_groups g left join students s on s.student_id = g.student_id where s.student_id is null',
    'tasks -> faculties' => 'select count(*) c from tasks t left join faculties f on f.faculty_id = t.faculty_id where f.faculty_id is null',
    'template_content_fields -> items' => 'select count(*) c from template_content_fields f left join template_content_items i on i.template_content_item_id = f.template_content_item_id where i.template_content_item_id is null',
    'content_items -> parent' => 'select count(*) c from template_content_items i left join template_content_items p on p.template_content_item_id = i.parent_id where i.parent_id is not null and p.template_content_item_id is null',
];
foreach ($orphans as $label => $sql) {
    $n = (int) $pg->select($sql)[0]->c;
    $check($label, $n === 0, "$n orphan(s)");
}

echo "\nContent fidelity\n";
$pgJson = $pg->select("select hotel_room_id, reservation::text r from hotel_rooms where reservation is not null order by hotel_room_id");
$myJson = $my->select('select id, reservation r from hotel_rooms where reservation is not null order by id');
$jsonSame = count($pgJson) === count($myJson);
if ($jsonSame) {
    foreach ($pgJson as $i => $row) {
        if ($row->r !== $myJson[$i]->r) { $jsonSame = false; break; }
    }
}
$check('hotel_rooms.reservation JSON byte-identical', $jsonSame, count($pgJson) . ' row(s)');

// Compare BYTES on both sides. MySQL's LENGTH() is bytes while PostgreSQL's length()
// is characters, so mixing them reports a false mismatch on any multi-byte content
// (this data contains peso signs and dashes).
$pgLen = (int) $pg->select('select coalesce(sum(octet_length(field_value)),0) n from template_content_fields')[0]->n;
$myLen = (int) $my->select('select coalesce(sum(length(field_value)),0) n from template_content_fields')[0]->n;
$check('template_content_fields total bytes', $pgLen === $myLen, "pg=$pgLen mysql=$myLen");

$pgChars = (int) $pg->select('select coalesce(sum(length(field_value)),0) n from template_content_fields')[0]->n;
$myChars = (int) $my->select('select coalesce(sum(char_length(field_value)),0) n from template_content_fields')[0]->n;
$check('template_content_fields total characters', $pgChars === $myChars, "pg=$pgChars mysql=$myChars");

$pgEmail = $pg->select('select email from users order by user_id limit 1')[0]->email;
$check('case-insensitive email lookup works',
    (int) $pg->select('select count(*) c from users where email = ?', [strtoupper($pgEmail)])[0]->c > 0);

echo "\nBooleans stored as real booleans\n";
$trueCount = (int) $pg->select('select count(*) c from template_layouts where is_visible = true')[0]->c;
$myTrue = (int) $my->select('select count(*) c from template_layouts where is_visible = 1')[0]->c;
$check('template_layouts.is_visible true count', $trueCount === $myTrue, "pg=$trueCount mysql=$myTrue");

$rev = $pg->select("select data_type d from information_schema.columns where table_name='tasks' and column_name='revision_count'")[0]->d;
$check('tasks.revision_count stayed numeric', str_contains($rev, 'int'), $rev);

echo "\n" . ($fail === 0 ? "IMPORT VERIFIED — Supabase matches MySQL\n" : "$fail check(s) failed\n");
exit($fail === 0 ? 0 : 1);
