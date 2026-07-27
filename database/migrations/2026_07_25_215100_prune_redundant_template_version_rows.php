<?php

use App\Models\TemplateContentField;
use App\Models\TemplateContentItem;
use App\Models\TemplateImage;
use App\Models\TemplateLayout;
use App\Models\TemplateStyle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('template_styles')) {
            return;
        }

        // 1) Remove ALL historical flat copies (live rows keep version_id NULL)
        $itemIds = TemplateContentItem::query()
            ->whereNotNull('version_id')
            ->pluck('id');

        if ($itemIds->isNotEmpty()) {
            TemplateContentField::whereIn('content_item_id', $itemIds)->delete();
        }

        TemplateContentItem::query()->whereNotNull('version_id')->whereNotNull('parent_id')->delete();
        TemplateContentItem::query()->whereNotNull('version_id')->delete();
        TemplateStyle::query()->whereNotNull('version_id')->delete();
        TemplateImage::query()->whereNotNull('version_id')->delete();
        TemplateLayout::query()->whereNotNull('version_id')->delete();

        // 2) Clear old version metadata (snapshots are gone; avoid broken Restore)
        //    New saves will keep at most HotelTemplateBuilder::MAX_VERSION_SNAPSHOTS.
        if (Schema::hasTable('team_role_template_versions')) {
            DB::table('team_role_template_versions')->delete();
        }
    }

    public function down(): void
    {
        // Irreversible cleanup.
    }
};
