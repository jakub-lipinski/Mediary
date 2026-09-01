<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    public function view(User $user, File $file): Response
    {
        return $this->owns($user, $file);
    }

    public function delete(User $user, File $file): Response
    {
        return $this->owns($user, $file);
    }

    private function owns(User $user, File $file): Response
    {
        return $file->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
