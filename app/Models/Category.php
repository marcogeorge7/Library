<?php

namespace App\Models;

class Category extends BaseModel
{
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
