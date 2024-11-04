<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Edition extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'internal_borrowing' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function translators(): BelongsToMany
    {
        return $this->belongsToMany(Translator::class, 'edition_translator_pivot_table', 'edition_id', 'translator_id')
            ->withTimestamps();
    }

    public function translator()
    {
        return $this->translators()->latest();
    }

    public function copies()
    {
        return $this->hasMany(Copy::class, 'edition_id', 'id');
    }

    public function getPartNumberAttribute()
    {
        return match ($this->partCode) {
            default => 'الجزء 1',
            2 => 'الجزء 2',
            3 => 'الجزء 3',
            4 => 'الجزء 4',
            5 => 'الجزء 5',
            6 => 'الجزء 6',
            7 => 'الجزء 7',
            8 => 'الجزء 8',
            9 => 'الجزء 9',
            10 => 'الجزء 10',
        };
    }

    public function scopeInternalBorrowing(Builder $query)
    {
        return $query->where('internal_borrowing', true);
    }

    public function numberInBook()
    {
        return $this->book()->orderBy('created_at')->pluck('id')->search($this->id) + 1;
    }

    public function copyOrderInEdition($copyId)
    {
        $position = $this->copies()->pluck('id')->search($copyId);
        return $position !== false ? $position + 1 : 0;
    }
}
