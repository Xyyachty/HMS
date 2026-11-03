<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two starting dishes had a photograph of something else: Cayman-Style Lobster
 * showed grilled fish and Honey Yoghurt Cheesecake a quiche. The house menu now
 * points at the right pictures; dishes a team never re-photographed follow it, so
 * the faculty review still reads them as untouched. Any other picture is the
 * team's and stays.
 */
return new class extends Migration
{
    private const SWAPS = [
        'https://www.themealdb.com/images/media/meals/wkhwqr1782774765.jpg'
            => 'https://images.unsplash.com/photo-1590759668628-05b0fc34bb70?auto=format&fit=crop&w=800&h=600&q=80',
        'https://www.themealdb.com/images/media/meals/y2irzl1585563479.jpg'
            => 'https://images.unsplash.com/photo-1524351199678-941a58a3df50?auto=format&fit=crop&w=800&h=600&q=80',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('hotel_menu_items')) {
            return;
        }
        foreach (self::SWAPS as $old => $new) {
            DB::table('hotel_menu_items')->where('image', $old)->update(['image' => $new]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('hotel_menu_items')) {
            return;
        }
        foreach (self::SWAPS as $old => $new) {
            DB::table('hotel_menu_items')->where('image', $new)->update(['image' => $old]);
        }
    }
};
