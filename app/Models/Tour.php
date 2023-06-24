<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tour extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'starting_at',
        'ending_at',
        'price',
    ];

    protected $casts = [
        'starting_at' => 'date',
        'ending_at' => 'date',
    ];

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value / 100,
            set: fn ($value) => $value * 100,
        );
    }

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
