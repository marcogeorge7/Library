<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Wires a policy's standard actions to the CRUD permissions already seeded by
 * GetResourcesForPermissions/DatabaseSeeder (viewAnyBook, updateBook, ...). The
 * permission name is derived from the policy's own class name (BookPolicy -> Book),
 * so this trait works unmodified for every XPolicy that follows the convention.
 */
trait AuthorizesViaPermissions
{
    protected function resourceName(): string
    {
        return Str::replaceLast('Policy', '', class_basename(static::class));
    }

    public function viewAny(User $user): bool
    {
        return $user->can('viewAny'.$this->resourceName());
    }

    public function view(User $user, $model): bool
    {
        return $user->can('view'.$this->resourceName());
    }

    public function create(User $user): bool
    {
        return $user->can('create'.$this->resourceName());
    }

    public function update(User $user, $model): bool
    {
        return $user->can('update'.$this->resourceName());
    }

    public function delete(User $user, $model): bool
    {
        return $user->can('delete'.$this->resourceName());
    }

    public function restore(User $user, $model): bool
    {
        return $user->can('restore'.$this->resourceName());
    }

    public function forceDelete(User $user, $model): bool
    {
        return $user->can('forceDelete'.$this->resourceName());
    }
}
