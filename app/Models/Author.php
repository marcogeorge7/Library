<?php

namespace App\Models;

class Author extends BaseModel {

    public function books()
    {
        return $this->belongsToMany(Book::class, 'author_books_pivot_table','author_id','book_id');
    }
}
