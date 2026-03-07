<?php

namespace App\Observers;

use App\Models\Borrower;

class BorrowerObserver
{
    public function created(Borrower $borrower): void
    {
        $borrower->member_id = 'LIB-' . str_pad($borrower->id, 5, '0', STR_PAD_LEFT);
        $borrower->saveQuietly();
    }
}
