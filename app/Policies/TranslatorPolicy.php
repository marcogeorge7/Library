<?php

namespace App\Policies;

use App\Models\Translator;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranslatorPolicy
{
    use HandlesAuthorization;
}
