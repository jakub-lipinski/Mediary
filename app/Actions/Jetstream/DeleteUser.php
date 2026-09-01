<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        Storage::disk('medical')->deleteDirectory('files/'.$user->id);
        $user->deleteProfilePhoto();
        $user->tokens()->delete();
        $user->delete();
    }
}
