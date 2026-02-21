<?php

namespace App\Policies;

use App\Models\FileItem;
use App\Models\User;

class FileItemPolicy
{
    public function view(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }

    public function update(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }

    public function delete(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }
}
