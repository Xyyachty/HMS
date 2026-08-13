<?php
/* Executes the generated export against Supabase inside a transaction and rolls it
   back. Proves the file parses and every row inserts, without committing anything. */

require 'C:/xampp/htdocs/HMS/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/HMS/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sql = file_get_contents('C:/xampp/htdocs/HMS/database/hms_pgsql.sql');

// Strip the file's own transaction control — we supply our own so we can roll back.
// Leaving its COMMIT in place would commit the import for real.
$sql = preg_replace('/^\s*(BEGIN|COMMIT)\s*;\s*$/mi', '', $sql);
if (preg_match('/^\s*COMMIT\s*;/mi', $sql)) {
    exit("refusing to run: COMMIT still present\n");
}

$pg = DB::connection('pgsql');
$pg->beginTransaction();

$exit = 0;
try {
    $started = microtime(true);
    $pg->unprepared($sql);
    $elapsed = microtime(true) - $started;

    printf("executed in %.1f s\n\n", $elapsed);

    $expected = [
        'users' => 46, 'students' => 44, 'tasks' => 22, 'student_groups' => 40,
        'student_group_roles' => 41, 'activity_logs' => 62, 'faculties' => 1,
        'faculty_classes' => 2, 'group_settings' => 1, 'hotel_customers' => 2,
        'hotel_menu_items' => 14, 'hotel_rooms' => 6, 'team_role_templates' => 4,
        'team_role_template_versions' => 11, 'template_content_fields' => 226,
        'template_content_items' => 66, 'template_elements' => 114,
        'template_images' => 24, 'template_layouts' => 80,
        'hotel_food_orders' => 0, 'team_template_edit_grants' => 0,
        'front_desk_canvases' => 0, 'front_desk_activities' => 0,
        'reservation_notifications' => 0,
    ];

    $bad = 0;
    foreach ($expected as $table => $want) {
        $got = (int) $pg->select("select count(*) c from \"$table\"")[0]->c;
        $ok = $got === $want;
        if (!$ok) { $bad++; }
        printf("  %-30s %4d / %-4d %s\n", $table, $got, $want, $ok ? '' : '  MISMATCH');
    }

    // Sequences must be past the highest imported id or the first insert collides.
    $seqBad = 0;
    foreach ([
        'users' => 'user_id',
        'tasks' => 'task_id',
        'hotel_menu_items' => 'hotel_menu_item_id',
        'template_content_fields' => 'template_content_field_id',
    ] as $table => $key) {
        $max = (int) $pg->select("select coalesce(max(\"$key\"),0) m from \"$table\"")[0]->m;
        // pg_get_serial_sequence returns the sequence's NAME; select from that name.
        $seq = $pg->select("select pg_get_serial_sequence('public.$table','$key') s")[0]->s;
        $next = (int) $pg->select("select last_value l from $seq")[0]->l;
        $ok = $next >= $max;
        if (!$ok) { $seqBad++; }
        printf("  seq %-26s last=%-6d max=%-6d %s\n", $table, $next, $max, $ok ? '' : '  TOO LOW');
    }

    // citext must still behave after the load.
    $u = $pg->select("select email from users limit 1")[0]->email;
    $ci = (int) $pg->select('select count(*) c from users where email = ?', [strtoupper($u)])[0]->c;
    printf("\n  case-insensitive email lookup: %s\n", $ci > 0 ? 'works' : 'FAILED');

    $exit = ($bad === 0 && $seqBad === 0 && $ci > 0) ? 0 : 1;
    echo "\n" . ($exit === 0 ? "DRY RUN PASSED — file is good\n" : "problems above\n");
} catch (\Throwable $e) {
    echo "FAILED: " . trim(explode("\n", $e->getMessage())[0]) . "\n";
    $exit = 1;
} finally {
    $pg->rollBack();
    echo "rolled back — Supabase is unchanged\n";
}

exit($exit);
