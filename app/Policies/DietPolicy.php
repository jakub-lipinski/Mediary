<?php

namespace App\Policies;

use App\Models\Diet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DietPolicy
{
    public function delete(User $user, Diet $diet): Response
    {
        return $diet->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
