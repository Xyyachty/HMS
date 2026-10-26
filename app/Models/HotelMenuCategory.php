<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A menu course belonging to one team.
 *
 * The five starting courses are not constants the team is stuck with: they are
 * written in here as ordinary rows on first visit, so Restaurant Management can
 * rename them and add its own. See App\Support\HotelMenuDefaults, which seeds
 * them and hands the rest of the app one list.
 *
 * Deliberately thinner than HotelRoomCategory: a room category carries a rate, a
 * floor, a gallery and an occupancy because a room is something a guest books. A
 * course is a heading a dish sits under, so a name and its place in the order is
 * all there is to keep.
 */
class HotelMenuCategory extends Model
{
    protected $primaryKey = 'hotel_menu_category_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Whether the table is there yet.
     *
     * Production migrates on deploy, so the code ships before the table does.
     * Every read goes through HotelMenuDefaults, which falls back to the
     * constants while this answers false — the menu keeps working on the five
     * defaults until the migration lands.
     */
    public static function tableReady(): bool
    {
        static $ready = null;

        if ($ready === null) {
            $ready = \Illuminate\Support\Facades\Schema::hasTable('hotel_menu_categories');
        }

        return $ready;
    }
}
