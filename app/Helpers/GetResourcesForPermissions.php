<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetResourcesForPermissions
{
    public static function generateResourcePermissions(string $resourceName): Collection
    {
        return collect([
            'viewAny' . $resourceName,
            'view' . $resourceName,
            'update' . $resourceName,
            'create' . $resourceName,
            'delete' . $resourceName,
            'forceDelete' . $resourceName,
            'restore' . $resourceName,
            'destroy' . $resourceName,
        ]);
    }
}
