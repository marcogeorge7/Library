<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesViaPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranslatorPolicy
{
    use HandlesAuthorization, AuthorizesViaPermissions;
}
