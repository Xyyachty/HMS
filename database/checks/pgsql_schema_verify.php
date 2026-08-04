<?php
/*
 * Verifies the schema Laravel built on Supabase.
 *
 *   php database/checks/pgsql_schema_verify.php
 *
 * Checks the table list against MySQL, that citext landed on the five columns that
 * need case-insensitive comparison, and that timestamp precision is 0 (a non-zero
 * precision makes HotelTemplateBuilder's sync version churn on every request).
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$fail = 0;
$check = function (string $label, bool $ok, string $detail = '') use (&$fail) {
    printf("  %-44s %s%s\n", $label, $ok ? 'PASS' : 'FAIL', $detail !== '' ? "  ($detail)" : '');
    if (!$ok) { $fail++; }
};

$pg = DB::connection('pgsql');

echo "Tables\n";
$pgTables = collect($pg->select(
    "select table_name from information_schema.tables where table_schema='public' and table_type='BASE TABLE'"
))->pluck('table_name')->sort()->values()->all();

$myTables = collect(DB::connection('mysql')->select('show tables'))
    ->map(fn ($r) => array_values((array) $r)[0])->sort()->values()->all();

$check('table count matches MySQL', count($pgTables) === count($myTables),
    'postgres=' . count($pgTables) . ' mysql=' . count($myTables));

$missing = array_diff($myTables, $pgTables);
$extra   = array_diff($pgTables, $myTables);
$check('no tables missing', $missing === [], $missing ? implode(', ', $missing) : '');
$check('no unexpected extra tables', $extra === [], $extra ? implode(', ', $extra) : '');

echo "\ncitext (case-insensitive columns)\n";
$expected = [
    'users' => 'email',
    'student_groups' => 'group_name',
    'group_settings' => 'group_name',
    'team_role_templates' => 'group_name',
    'hotel_menu_items' => 'name',
];
foreach ($expected as $table => $column) {
    $row = $pg->select(
        "select udt_name from information_schema.columns where table_schema='public' and table_name=? and column_name=?",
        [$table, $column]
    );
    $type = $row ? $row[0]->udt_name : 'MISSING';
    $check("$table.$column is citext", $type === 'citext', $type);
}

echo "\nBehaviour\n";
$ci = $pg->select("select 'Team Alpha'::citext = 'team alpha'::citext as ok")[0]->ok;
$check('citext compares case-insensitively', $ci === true || $ci === 't');

echo "\nTimestamp precision\n";
$prec = $pg->select(
    "select datetime_precision p from information_schema.columns
     where table_schema='public' and table_name='team_role_templates' and column_name='updated_at'"
);
$check('team_role_templates.updated_at precision is 0',
    $prec && (int) $prec[0]->p === 0, $prec ? 'got ' . $prec[0]->p : 'column missing');

echo "\nForeign keys\n";
$fkCount = $pg->select(
    "select count(*) c from information_schema.table_constraints
     where table_schema='public' and constraint_type='FOREIGN KEY'"
)[0]->c;
$myFk = DB::connection('mysql')->select(
    "select count(*) c from information_schema.referential_constraints where constraint_schema = database()"
)[0]->c;
$check('foreign key count matches MySQL', (int) $fkCount === (int) $myFk,
    "postgres=$fkCount mysql=$myFk");

echo "\nRow counts (expected empty before import)\n";
$rows = 0;
foreach ($pgTables as $t) {
    if ($t === 'migrations') { continue; }
    $rows += (int) $pg->select("select count(*) c from \"$t\"")[0]->c;
}
$check('schema is empty, ready for import', $rows === 0, "$rows rows");

echo "\n" . ($fail === 0 ? "SCHEMA VERIFIED\n" : "$fail check(s) failed\n");
exit($fail === 0 ? 0 : 1);
