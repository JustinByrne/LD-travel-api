<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Travel extends Model
{
    use HasFactory, HasUuids, HasSlug;

    protected $table = 'travels';

    protected $fillable = [
        'name',
        'slug',
        'is_public',
        'description',
        'number_of_days',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function numberOfNights(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['number_of_days'] - 1,
        );
    }

    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class);
    }
}
