<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelCustomer extends Model
{
    protected $primaryKey = 'hotel_customer_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        // Composed from first_name + last_name, and what every screen greets the
        // guest by. Kept as its own column so those reads stay one field.
        'name',
        'first_name',
        'last_name',
        'contact_number',
        'email',
        // A path on the media disk to the ID the guest uploaded when signing up.
        'id_document',
        'password',
    ];

    /**
     * Whether this database has the `id_document` column yet.
     *
     * It arrives in a migration of its own, so between pulling the code and
     * running it a sign-up carrying an ID would be an INSERT against a column
     * that is not there. Answered once per request.
     */
    public static function supportsIdDocument(): bool
    {
        static $has = null;

        if ($has === null) {
            $has = \Illuminate\Support\Facades\Schema::hasColumn('hotel_customers', 'id_document');
        }

        return $has;
    }

    /** The uploaded ID as a URL the staff screens can open, or '' when there is none. */
    public function idDocumentUrl(): string
    {
        return self::supportsIdDocument()
            ? \App\Support\HotelImageStore::url($this->id_document)
            : '';
    }

    protected $hidden = [
        'password',
    ];
}
