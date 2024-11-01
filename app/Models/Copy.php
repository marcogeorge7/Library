<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Copy extends Model
{
    use HasFactory, SoftDeletes;

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
