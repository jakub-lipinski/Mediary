<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesja logowania Google wygasła. Spróbuj ponownie.',
            ]);
        }

        $email = Str::of((string) $googleUser->getEmail())->trim()->lower()->toString();
        $googleId = (string) $googleUser->getId();
        $isVerifiedEmail = filter_var(
            data_get($googleUser->getRaw(), 'email_verified'),
            FILTER_VALIDATE_BOOL,
        );

        if (blank($email) || blank($googleId) || ! $isVerifiedEmail) {
            throw ValidationException::withMessages([
                'email' => 'Google nie zwrócił zweryfikowanego adresu e-mail.',
            ]);
        }

        $name = $googleUser->getName() ?: $googleUser->getNickname() ?: Str::before($email, '@');
        $firstName = Str::before($name, ' ') ?: $name;

        $user = DB::transaction(function () use ($email, $firstName, $googleId): User {
            $user = User::query()->where('google_id', $googleId)->first();

            if (! $user) {
                $user = User::query()->where('email', $email)->lockForUpdate()->first();
            }

            if ($user && filled($user->google_id) && $user->google_id !== $googleId) {
                throw ValidationException::withMessages([
                    'email' => 'Ten adres e-mail jest już połączony z innym kontem Google.',
                ]);
            }

            if ($user) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                return $user;
            }

            return User::query()->forceCreate([
                'name' => $firstName,
                'email' => $email,
                'google_id' => $googleId,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
