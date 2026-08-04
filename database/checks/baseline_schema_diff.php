<?php
/*
 * Proves the baseline migration reproduces the production schema exactly.
 *
 * MySQL-only (it reads MySQL's information_schema column_type). Used before the
 * Supabase cutover to catch baseline drift while both schemas were still MariaDB:
 *
 *   mysql -u root -e "CREATE DATABASE hms_baseline_test"
 *   DB_DATABASE=hms_baseline_test php artisan migrate --force
 *   php database/checks/baseline_schema_diff.php
 *
 * Compares columns, nullability, indexes and foreign keys. Conversions that are
 * deliberate (enum -> string, unsigned dropped, json) are counted and accepted;
 * anything else is reported and exits non-zero.
 */

$pdo = new PDO('mysql:host=127.0.0.1', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

function columns(PDO $pdo, string $db): array {
    $out = [];
    $sql = "SELECT table_name, column_name, column_type, is_nullable, column_default, column_key
            FROM information_schema.columns WHERE table_schema = ? ORDER BY table_name, ordinal_position";
    $st = $pdo->prepare($sql);
    $st->execute([$db]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $r = array_change_key_case($r, CASE_LOWER);
        $out[$r['table_name']][$r['column_name']] = [
            'type' => $r['column_type'],
            'null' => $r['is_nullable'],
            'default' => $r['column_default'],
        ];
    }
    return $out;
}

function indexes(PDO $pdo, string $db): array {
    $out = [];
    $st = $pdo->prepare("SELECT table_name, index_name, non_unique, GROUP_CONCAT(column_name ORDER BY seq_in_index) cols
                         FROM information_schema.statistics WHERE table_schema = ?
                         GROUP BY table_name, index_name, non_unique");
    $st->execute([$db]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $r = array_change_key_case($r, CASE_LOWER);
        $out[$r['table_name']][$r['cols'] . ($r['non_unique'] ? '' : ' UNIQUE')] = true;
    }
    return $out;
}

function fks(PDO $pdo, string $db): array {
    $out = [];
    $st = $pdo->prepare("SELECT k.table_name, k.column_name, k.referenced_table_name, r.delete_rule
                         FROM information_schema.key_column_usage k
                         JOIN information_schema.referential_constraints r
                           ON r.constraint_name = k.constraint_name AND r.constraint_schema = k.table_schema
                         WHERE k.table_schema = ? AND k.referenced_table_name IS NOT NULL");
    $st->execute([$db]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $r = array_change_key_case($r, CASE_LOWER);
        $out[$r['table_name']][$r['column_name'] . ' -> ' . $r['referenced_table_name'] . ' ' . $r['delete_rule']] = true;
    }
    return $out;
}

$prodCols = columns($pdo, 'hms');
$testCols = columns($pdo, 'hms_baseline_test');
unset($prodCols['migrations'], $testCols['migrations']);

$expected = 0; $problems = [];

$missingTables = array_diff(array_keys($prodCols), array_keys($testCols));
$extraTables   = array_diff(array_keys($testCols), array_keys($prodCols));
foreach ($missingTables as $t) { $problems[] = "TABLE MISSING from baseline: $t"; }
foreach ($extraTables as $t)   { $problems[] = "TABLE EXTRA in baseline: $t"; }

foreach ($prodCols as $table => $cols) {
    if (!isset($testCols[$table])) { continue; }
    foreach ($cols as $name => $prod) {
        if (!isset($testCols[$table][$name])) { $problems[] = "$table.$name MISSING from baseline"; continue; }
        $test = $testCols[$table][$name];

        $pt = $prod['type']; $tt = $test['type'];
        if ($pt === $tt && $prod['null'] === $test['null']) { continue; }

        // Deliberate, documented conversions. Display widths (int(10) vs int(11))
        // are cosmetic in MySQL and differ between the signed and unsigned forms,
        // so compare the base type only.
        $base = fn (string $t) => preg_replace('/\(\d+\)/', '', $t);
        $isEnumToString = str_starts_with($pt, 'enum(') && str_starts_with($tt, 'varchar(');
        $isUnsignedDrop = trim(str_replace(' unsigned', '', $base($pt))) === trim($base($tt))
            && $prod['null'] === $test['null'];
        $isJson         = str_contains($pt, 'longtext') && $tt === 'longtext';
        if ($isEnumToString || $isUnsignedDrop || $isJson) { $expected++; continue; }

        $problems[] = sprintf('%s.%s  prod=%s null=%s  |  baseline=%s null=%s',
            $table, $name, $pt, $prod['null'], $tt, $test['null']);
    }
    foreach ($testCols[$table] as $name => $_) {
        if (!isset($cols[$name])) { $problems[] = "$table.$name EXTRA in baseline"; }
    }
}

$prodIdx = indexes($pdo, 'hms'); $testIdx = indexes($pdo, 'hms_baseline_test');
unset($prodIdx['migrations'], $testIdx['migrations']);
foreach ($prodIdx as $table => $set) {
    foreach (array_keys($set) as $sig) {
        if (!isset($testIdx[$table][$sig])) { $problems[] = "INDEX missing on $table: $sig"; }
    }
}

$prodFk = fks($pdo, 'hms'); $testFk = fks($pdo, 'hms_baseline_test');
foreach ($prodFk as $table => $set) {
    foreach (array_keys($set) as $sig) {
        if (!isset($testFk[$table][$sig])) { $problems[] = "FK missing on $table: $sig"; }
    }
}
foreach ($testFk as $table => $set) {
    foreach (array_keys($set) as $sig) {
        if (!isset($prodFk[$table][$sig])) { $problems[] = "FK EXTRA on $table: $sig"; }
    }
}

printf("tables: prod=%d baseline=%d\n", count($prodCols), count($testCols));
printf("deliberate conversions accepted: %d\n\n", $expected);

if (!$problems) { echo "NO UNEXPECTED DIFFERENCES\n"; exit(0); }
echo count($problems) . " difference(s) to review:\n";
foreach ($problems as $p) { echo "  $p\n"; }
exit(1);
