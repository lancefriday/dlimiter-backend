<?php

namespace App\Policies;

use App\Models\FileItem;
use App\Models\User;

/**
 * FileItemPolicy
 *
 * Centralized authorization for file actions.
 *
 * Rule
 * - Admin user: full access
 * - Regular user: access only to own files (owner_id == user.id)
 *
 * Controllers typically call:
 * - $this->authorize('view', $file)
 * - $this->authorize('update', $file)
 * - $this->authorize('delete', $file)
 */
class FileItemPolicy
{
    /**
     * View permission.
     */
    public function view(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }

    /**
     * Update permission (covers link creation and edits).
     */
    public function update(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }

    /**
     * Delete permission.
     */
    public function delete(User $user, FileItem $file): bool
    {
        return $user->is_admin || $file->owner_id === $user->id;
    }
}