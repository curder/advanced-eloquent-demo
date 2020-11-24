<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Customer
 * @method orderByName
 *
 * @package App\Models
 */
class Customer extends Model
{
    use HasFactory;

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function company() : BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function interactions() : HasMany
    {
        return $this->hasMany(Interaction::class);
    }
    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    public function scopeOrderByName(Builder $query): void
    {
        $query->orderBy('last_name')->orderBy('first_name');
    }
}
