<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends BaseModel
{
    use HasFactory, SoftDeletes;

    public function books()
    {
        return $this->hasMany(Book::class, 'series_id', 'id')->orderBy('created_at', 'asc');
    }

    public function bookOrderInSeries($bookId)
    {
        $position = $this->books()->pluck('id')->search($bookId);
        return $position !== false ? $position + 1 : 0;
    }
}
