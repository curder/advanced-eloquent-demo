<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @property integer id
 * @property integer club_id
 * @property string name
 * @property string email
 * @property string email_verified_at
 * @property string password
 * @property string remember_token
 *
 * @property \App\Models\Club club
 * @property \Illuminate\Support\Collection trips
 * @property \Illuminate\Support\Collection buddies
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    /**
     * 用户与俱乐部表关联关系，用户依附于俱乐部
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * 用户与旅行表，用户又多次旅行
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * 用户与好友表，用户有多个好友
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function buddies()
    {
        return $this->belongsToMany(__CLASS__, 'buddies', 'user_id', 'buddy_id')->withTimestamps();
    }

    /**
     * 用户对数据的可见性
     *
     * @param Builder $query
     * @param User    $user
     */
    public function scopeVisibleTo(Builder $query, User $user) : void
    {
        $query->where(function ($query) use ($user) {
            $query->where('club_id', $user->club_id)
                  ->orWhereIn('id', $user->buddies->pluck('id'));
        });
    }
}
