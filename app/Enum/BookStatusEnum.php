<?php

namespace App\Enum;

enum BookStatusEnum: string
{
    case EXTERNAL_BORROWING = 'external_borrowing';
    case INTERNAL_BORROWING = 'internal_borrowing';
}
