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

    protected $fillable = [
        'group_name',
        'faculty_id',
        'name',
        'category',
        'price',
        'stock',
        'description',
        'image',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
    ];

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
        return [
            'id'       => 'db-' . $this->id,
            'dbId'     => $this->id,
            'name'     => $this->name,
            'category' => $this->category,
            'price'    => (int) $this->price,
            'stock'    => (int) $this->stock,
            'sub'      => $this->description ?? '',
            'img'      => \App\Support\HotelImageStore::url($this->image),
        ];
    }
}
