<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelMenuItem extends Model
{
    public const CATEGORIES = [
        'Main Dishes',
        'Appetizers',
        'Soups',
        'Desserts',
        'Beverages',
    ];

    protected $primaryKey = 'hotel_menu_item_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'name',
        'category',
        'price',
        'stock',
        'description',
        'image',
        'best_seller',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'best_seller' => 'boolean',
    ];

    /**
     * Whether this database has the best_seller column yet. Asked because it
     * arrives in a migration of its own; until it has run, a dish simply is not
     * one the team picked. Answered once per request.
     */
    public static function supportsBestSeller(): bool
    {
        static $has = null;

        if ($has === null) {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('hotel_menu_items', 'best_seller');
        }

        return $has;
    }

    public static function normalizeCategory(?string $value): string
    {
        $raw = strtolower(trim((string) $value));
        foreach (self::CATEGORIES as $category) {
            if (strtolower($category) === $raw) {
                return $category;
            }
        }

        return 'Main Dishes';
    }

    /** Shape sent to the hotel template front-end. */
    public function toTemplateArray(): array
    {
        // "id"/"dbId" are the front-end's keys for a menu row, not column names.
        return [
            'id'       => 'db-' . $this->hotel_menu_item_id,
            'dbId'     => $this->hotel_menu_item_id,
            'name'     => $this->name,
            'category' => $this->category,
            'price'    => (int) $this->price,
            'stock'    => (int) $this->stock,
            'sub'      => $this->description ?? '',
            'img'      => \App\Support\HotelImageStore::url($this->image),
            // Picked for the Best Seller section (RS TASK 2).
            'bestSeller' => self::supportsBestSeller() && (bool) $this->best_seller,
        ];
    }
}
