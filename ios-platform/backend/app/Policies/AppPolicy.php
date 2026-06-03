<?php

namespace App\Policies;

use App\Models\App as AppModel;
use App\Models\User;

class AppPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdmin();
    }

    public function view(User $user, AppModel $app): bool
    {
        return $user->canAccessAdmin();
    }

    public function create(User $user): bool
    {
        return $user->can('app.create');
    }

    public function update(User $user, AppModel $app): bool
    {
        return $user->can('app.update');
    }

    public function delete(User $user, AppModel $app): bool
    {
        return $user->can('app.delete');
    }

    public function archive(User $user, AppModel $app): bool
    {
        return $user->can('app.archive');
    }

    public function publish(User $user, AppModel $app): bool
    {
        return $user->can('app.publish');
    }

    public function upload(User $user, AppModel $app): bool
    {
        return $user->can('app.upload');
    }
}
