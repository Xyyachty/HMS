<?php
/*
 * Phase 1 regression for the Supabase migration.
 *
 * Exercises every path made engine-agnostic before the cutover. Deliberately
 * engine-neutral so it can be re-run unchanged against PostgreSQL afterwards.
 *
 *   php database/checks/phase1_regression.php
 *
 * Exits non-zero if anything fails.
 */
require 'C:/xampp/htdocs/HMS/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/HMS/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;
use App\Support\HotelImageStore;
use Illuminate\Support\Facades\Storage;

$fail = 0;
$check = function (string $label, bool $ok, string $detail = '') use (&$fail) {
    printf("  %-46s %s%s\n", $label, $ok ? 'PASS' : 'FAIL', $detail !== '' ? "  ($detail)" : '');
    if (!$ok) { $fail++; }
};

echo "Task priority ordering\n";
$p = Task::query()->active()->orderByPriority()->pluck('priority')->all();
$rank = ['high' => 1, 'medium' => 2, 'low' => 3];
$sorted = true;
for ($i = 1; $i < count($p); $i++) {
    if (($rank[$p[$i-1]] ?? 4) > ($rank[$p[$i]] ?? 4)) { $sorted = false; break; }
}
$check('scopeOrderByPriority sorts high->low', $sorted, count($p) . ' tasks');

echo "\nDean dashboard aggregates\n";
$new = StudentGroup::query()->select('faculty_id','group_name')->distinct()->get()->count();

if (Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
    // Only meaningful on MySQL: this is the expression the controller used to run,
    // kept as a reference value. On PostgreSQL it raises `column "::" does not exist`,
    // which is the exact bug the replacement fixed.
    $old = (int) StudentGroup::query()
        ->selectRaw('COUNT(DISTINCT CONCAT(COALESCE(faculty_id, 0), "::", group_name)) as aggregate')
        ->value('aggregate');
    $check('totalTeams matches previous MySQL result', $old === $new, "old=$old new=$new");
} else {
    // Cross-checked against MySQL when this suite last ran there.
    $check('totalTeams counts distinct (faculty, name) pairs', $new === 10, "got $new");
}

echo "\nEmail normalization\n";
$u = User::first();
$check('lookup by UPPERCASE email finds user', User::whereEmail(strtoupper($u->email))->exists());
$m = new User(); $m->email = '  MiXeD@HMS.edu ';
$check('mutator lowercases and trims', $m->email === 'mixed@hms.edu', $m->email);
$mixed = User::pluck('email')->filter(fn ($e) => $e !== mb_strtolower((string) $e))->count();
$check('no mixed-case emails stored', $mixed === 0);

echo "\nHotel images on disk\n";
$missing = 0; $checked = 0; $b64 = 0;
foreach ([HotelRoom::class, HotelMenuItem::class] as $model) {
    foreach ($model::whereNotNull('image')->get() as $row) {
        $img = (string) $row->image;
        if ($img === '' || str_starts_with($img, 'http')) { continue; }
        if (str_starts_with($img, 'data:')) { $b64++; continue; }
        $checked++;
        if (!Storage::disk('public')->exists($img)) { $missing++; }
    }
}
$check('every stored image path exists on disk', $missing === 0, "$checked checked");
$check('no base64 left in room/menu columns', $b64 === 0);
$check('url() builds a /storage path',
    str_contains(HotelImageStore::url(HotelRoom::first()->image), '/storage/'));
$check('url() passes remote URLs through',
    HotelImageStore::url('https://picsum.photos/x.jpg') === 'https://picsum.photos/x.jpg');
$check('persist() passes remote URLs through',
    HotelImageStore::persist('https://picsum.photos/x.jpg', 1, 'T') === 'https://picsum.photos/x.jpg');
$check('persist() null-safe', HotelImageStore::persist(null, 1, 'T') === null);

echo "\nOrder line matching\n";
$item = HotelMenuItem::first();
$membership = StudentGroup::where('group_name', $item->group_name)
    ->where('faculty_id', $item->faculty_id)->first();
$lines = App\Models\HotelFoodOrder::sanitizeItems([
    ['menu_item_id' => $item->hotel_menu_item_id, 'name' => $item->name, 'price' => $item->price, 'qty' => 1],
]);
$check('sanitizeItems keeps menu_item_id', ($lines[0]['menu_item_id'] ?? null) === $item->hotel_menu_item_id);
$locked = App\Support\HotelOrderAccess::lockMenuItemsFor($membership, $lines);
$check('matchMenuItem resolves by id',
    optional(App\Support\HotelOrderAccess::matchMenuItem($locked, $lines[0]))->hotel_menu_item_id === $item->hotel_menu_item_id);
$legacy = ['menu_item_id' => null, 'name' => mb_strtoupper($item->name), 'qty' => 1];
$locked2 = App\Support\HotelOrderAccess::lockMenuItemsFor($membership, [$legacy]);
$check('matchMenuItem falls back to name, any case',
    optional(App\Support\HotelOrderAccess::matchMenuItem($locked2, $legacy))->hotel_menu_item_id === $item->hotel_menu_item_id);

echo "\nUser data (must never drift)\n";
$baseline = [
    'users' => 46, 'students' => 44, 'tasks' => 22, 'student_groups' => 40,
    'student_group_roles' => 41, 'faculties' => 1, 'faculty_classes' => 2,
    'hotel_rooms' => 6, 'hotel_menu_items' => 14, 'hotel_customers' => 2,
];
foreach ($baseline as $table => $expected) {
    $actual = Illuminate\Support\Facades\DB::table($table)->count();
    $check("$table unchanged", $actual === $expected, "expected $expected, got $actual");
}

echo "\nLive template rows (version_id = 0)\n";
// Template table TOTALS are deliberately not asserted: every manual save writes a
// version snapshot and prunes old ones, so they legitimately rise and fall. The live
// set is what renders the current site, and that must survive a save unchanged.
$live = [
    'template_layouts' => 20,
    'template_elements' => 8,
    'template_content_items' => 15,
    'template_images' => 12,
];
foreach ($live as $table => $expected) {
    $actual = Illuminate\Support\Facades\DB::table($table)->where('version_id', 0)->count();
    $check("$table live rows", $actual === $expected, "expected $expected, got $actual");
}

// Append-only and grows with real use, so assert the floor rather than equality.
$logs = Illuminate\Support\Facades\DB::table('activity_logs')->count();
$check('activity_logs at or above migration baseline', $logs >= 62, "$logs rows");

echo "\n" . ($fail === 0 ? "ALL CHECKS PASSED" : "$fail CHECK(S) FAILED") . "\n";
exit($fail === 0 ? 0 : 1);
