<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends BaseModel
{
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

    public function numberInSeries()
    {
        return $this->series()->orderBy('created_at')->pluck('id')->search($this->id) + 1;
    }
}
