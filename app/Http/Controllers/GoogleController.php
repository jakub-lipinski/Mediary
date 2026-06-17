<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $email = $googleUser->getEmail();

        if (blank($email)) {
            throw ValidationException::withMessages([
                'email' => 'Google nie zwrócił adresu e-mail.',
            ]);
        }

        $name = $googleUser->getName() ?: $googleUser->getNickname() ?: Str::before($email, '@');
        $firstName = Str::before($name, ' ') ?: $name;

        $user = User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => $firstName,
            'password' => Hash::make(Str::random(40)),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
