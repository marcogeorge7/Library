<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookPolicy
{
    use HandlesAuthorization, AuthorizesViaPermissions;
}
