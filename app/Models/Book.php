<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;

class Book extends BaseModel
{
    use HasSlug;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(Revisor::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function author()
    {
        return $this->belongsToMany(Author::class, 'author_books_pivot_table', 'book_id', 'author_id')
            ->withTimestamps();
    }

    public function editions()
    {
        return $this->hasMany(Edition::class);
    }

    public function copies()
    {
        return $this->hasManyThrough(Copy::class, Edition::class);
    }

    public function editionOrderInBook($editionId)
    {
        return $this->editions()
            ->orderBy('created_at')
            ->pluck('id')
            ->search($editionId) + 1;
    }

    public function hasSeries()
    {
        return boolval($this->series_id);
    }

    public static function getNextOrderInCategory($categoryId)
    {
        $max = Book::query()->where('category_id', $categoryId)->max('order');

        return ($max ?? 0) + 1;
    }
}
