<?php

namespace App\Console\Commands;

use App\Models\HotelMenuItem;
use App\Models\HotelRoom;
use App\Support\HotelImageStore;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * One-off backfill: rewrite base64 data-URLs held in hotel_rooms.image and
 * hotel_menu_items.image as files on the public disk, leaving only the path in
 * the database. Safe to re-run — rows already holding a path are skipped.
 */
class ConvertHotelImagesToFiles extends Command
{
    protected $signature = 'hms:convert-hotel-images {--dry-run : Report what would change without writing}';

    protected $description = 'Move base64 room and menu images out of the database and onto the public disk';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — nothing will be written.');
        }

        $totalRows = 0;
        $totalBytes = 0;

        foreach ([HotelRoom::class, HotelMenuItem::class] as $modelClass) {
            [$rows, $bytes] = $this->convert($modelClass, $dryRun);
            $totalRows += $rows;
            $totalBytes += $bytes;
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d row(s), %s freed from the database.',
            $dryRun ? 'Would convert' : 'Converted',
            $totalRows,
            $this->humanBytes($totalBytes)
        ));

        return self::SUCCESS;
    }

    /** @return array{0:int,1:int} rows converted, bytes removed */
    private function convert(string $modelClass, bool $dryRun): array
    {
        $table = (new $modelClass)->getTable();
        $rows = 0;
        $bytes = 0;

        // No explicit orderBy: chunkById sorts by the model's own key, and that key is
        // named per table now (hotel_room_id, hotel_menu_item_id), not "id".
        $modelClass::query()
            ->where('image', 'like', 'data:image%')
            ->chunkById(20, function ($items) use (&$rows, &$bytes, $dryRun, $table) {
                /** @var Model $item */
                foreach ($items as $item) {
                    $original = (string) $item->image;
                    $size = strlen($original);

                    if ($dryRun) {
                        $this->line(sprintf('  %s #%d — %s', $table, $item->getKey(), $this->humanBytes($size)));
                        $rows++;
                        $bytes += $size;
                        continue;
                    }

                    $path = HotelImageStore::persist($original, $item->faculty_id, $item->group_name);

                    if ($path === null || $path === $original) {
                        $this->warn(sprintf('  %s #%d — could not decode, left as-is', $table, $item->getKey()));
                        continue;
                    }

                    $item->image = $path;
                    $item->save();

                    $this->line(sprintf('  %s #%d — %s -> %s', $table, $item->getKey(), $this->humanBytes($size), $path));
                    $rows++;
                    $bytes += $size - strlen($path);
                }
            });

        return [$rows, $bytes];
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
