<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Copy everything under storage/app/public up to Supabase Storage, keeping the same
 * relative paths so the values already stored in the database keep resolving.
 *
 * Run once before the first deploy. Render's filesystem is ephemeral, so without this
 * every template and room image 404s as soon as the app moves.
 */
class UploadMediaToSupabase extends Command
{
    protected $signature = 'hms:upload-media
        {--disk=supabase : Destination disk}
        {--dry-run : List what would be uploaded without sending anything}
        {--overwrite : Replace files that already exist on the destination}';

    protected $description = 'Copy local media files to Supabase Storage';

    public function handle(): int
    {
        $target = $this->option('disk');
        $dryRun = (bool) $this->option('dry-run');

        if (!config("filesystems.disks.$target")) {
            $this->error("No '$target' disk is configured.");

            return self::FAILURE;
        }

        $source = Storage::disk('public');
        $files = collect($source->allFiles())
            ->reject(fn ($path) => str_ends_with($path, '.gitignore'))
            ->values();

        if ($files->isEmpty()) {
            $this->info('Nothing to upload.');

            return self::SUCCESS;
        }

        $this->line(sprintf('%d file(s) in storage/app/public', $files->count()));
        if ($dryRun) {
            $this->warn('Dry run — nothing will be sent.');
        }

        $uploaded = 0;
        $skipped = 0;
        $failed = 0;
        $bytes = 0;

        foreach ($files as $path) {
            $size = $source->size($path);

            if ($dryRun) {
                $this->line(sprintf('  would upload  %s (%s)', $path, $this->humanBytes($size)));
                $uploaded++;
                $bytes += $size;
                continue;
            }

            try {
                if (!$this->option('overwrite') && Storage::disk($target)->exists($path)) {
                    $this->line("  exists, skipped  $path");
                    $skipped++;
                    continue;
                }

                // Streamed rather than read into memory: some template images are large.
                Storage::disk($target)->writeStream($path, $source->readStream($path));

                $this->line(sprintf('  uploaded  %s (%s)', $path, $this->humanBytes($size)));
                $uploaded++;
                $bytes += $size;
            } catch (\Throwable $e) {
                $this->error(sprintf('  FAILED  %s — %s', $path, trim(explode("\n", $e->getMessage())[0])));
                $failed++;
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d file(s), %s. %d skipped, %d failed.',
            $dryRun ? 'Would upload' : 'Uploaded',
            $uploaded,
            $this->humanBytes($bytes),
            $skipped,
            $failed
        ));

        if (!$dryRun && $failed === 0 && $uploaded > 0) {
            $sample = $files->first();
            $this->line('Check one in a browser: ' . Storage::disk($target)->url($sample));
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return $bytes >= 1024 ? round($bytes / 1024, 1) . ' KB' : $bytes . ' B';
    }
}
