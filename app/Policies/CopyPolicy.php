<?php

namespace App\Policies;

use App\Models\Copy;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CopyPolicy
{
    use HandlesAuthorization;
}
