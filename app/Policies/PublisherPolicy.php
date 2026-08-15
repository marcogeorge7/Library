<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublisherPolicy
{
    use HandlesAuthorization, AuthorizesViaPermissions;
}
