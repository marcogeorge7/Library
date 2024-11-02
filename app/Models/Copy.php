<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Copy extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_printed' => 'boolean',
        'is_borrowed' => 'boolean',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function scopeBorrowed(Builder $query)
    {
        return $query->where('is_borrowed', true);
    }
}
