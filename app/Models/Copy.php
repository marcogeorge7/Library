<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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

    /**
     * Get the book that this copy belongs to through the edition.
     */
    public function book(): HasOneThrough
    {
        return $this->hasOneThrough(
            Book::class,
            Edition::class,
            'id', // Foreign key on editions table
            'id', // Foreign key on books table
            'edition_id', // Local key on copies table
            'book_id' // Local key on editions table
        );
    }

    public function scopeBorrowed(Builder $query)
    {
        return $query->where('is_borrowed', true);
    }
}
