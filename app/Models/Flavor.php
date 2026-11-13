<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flavor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'bg_color',
        'text_color',
        'is_favorite',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

