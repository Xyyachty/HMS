<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
        'remember_token',
        'avatar',
        'last_seen_at',
    ];

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
     * @var array<int, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Profile image URL, or generated initials avatar when none is uploaded.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            return asset('storage/' . ltrim($this->avatar, '/'));
        }

        $label = trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ]))) ?: ($this->name ?? 'User');

        return 'https://ui-avatars.com/api/?name=' . urlencode($label) . '&background=DB2777&color=fff&size=128&font-size=0.4';
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
            if (empty($user->remember_token)) {
                $user->remember_token = Str::random(60);
            }
        });

        static::updating(function (User $user) {
            if ($user->email_verified_at === null) {
                $user->email_verified_at = now();
            }
            if ($user->remember_token === null || $user->remember_token === '') {
                $user->remember_token = Str::random(60);
            }
        });
    }

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
