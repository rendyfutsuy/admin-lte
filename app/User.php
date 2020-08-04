<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /** Level */

    Const GUEST = 0;
    Const NORMAL = 1;
    Const ADMIN = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'meta',
        'email_verified_at',
        'banned_at',
        'level',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'meta' => 'array',
        'is_banned' => 'boolean'
    ];

    /** show user banned status */
    public function isBanned(): boolean
    {
        return $this->is_banned;
    }

    /**  show user activation status */
    public function isActivated(): boolean
    {
        return ! $this->activation_code &&
            $this->email_verified_at;
    }

    public function resetPhones(): HasMany
    {
        return $this->hasMany(ResetPhone::class, 'user_id', 'id');
    }

    public function resetEmails(): HasMany
    {
        return $this->hasMany(ResetEmail::class, 'user_id', 'id');
    }

    public function resetPhonesWithSoftDeletes(): HasMany
    {
        return $this->hasMany(ResetPhone::class, 'user_id', 'id')
            ->withTrashed();
    }

    public function resetEmailsWithSoftDeletes(): HasMany
    {
        return $this->hasMany(ResetEmail::class, 'user_id', 'id')
            ->withTrashed();
    }

    public function getMeta(string $label = null): array
    {
        if ($label == null) {
            return $this->meta;
        }

        return $this->meta[$label];
    }
}
