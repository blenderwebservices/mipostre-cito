<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'tag',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'zip_code',
        'references',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        $int = $this->interior_number ? " Int. {$this->interior_number}" : '';
        return "{$this->street} #{$this->exterior_number}{$int}, Col. {$this->neighborhood}, CP {$this->zip_code}";
    }
}

