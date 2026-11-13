<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable = [
        'source_type',
        'source_id',
        'title',
        'content',
        'embedding',
        'metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

