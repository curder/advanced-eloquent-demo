<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

}
