<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotePolicy
{
    public function delete(User $user, Note $note): Response
    {
        return $note->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
