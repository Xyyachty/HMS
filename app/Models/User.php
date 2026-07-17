<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Faculty;
use App\Models\Student;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'status',
        'email_verified_at',
    ];

    /**
     * Normalize optional text: empty / "None" / "null" become "" (avoid SQL NULL).
     */
    public static function cleanOptional(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strcasecmp($value, 'none') === 0 || strcasecmp($value, 'null') === 0) {
            return '';
        }

        return $value;
    }

    /** @deprecated Use cleanOptional() — kept so older calls still avoid NULL. */
    public static function blankToNull(?string $value): string
    {
        return static::cleanOptional($value);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function faculty()
    {
        return $this->hasOne(Faculty::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

}
