<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class BorrowRequestPolicy
{
    use HandlesAuthorization, AuthorizesViaPermissions;
}
