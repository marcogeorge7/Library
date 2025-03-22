<?php

namespace App\Models;

class Publisher extends BaseModel {

    public function editions()
    {
        return $this->hasMany(Edition::class);
    }

    public function books()
    {
        return $this->editions->load('book')->unique('book_id')->pluck('book');
    }

}
