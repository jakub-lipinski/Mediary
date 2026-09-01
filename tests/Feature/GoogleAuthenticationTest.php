<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_google_user_can_register_and_sign_in(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Jan Kowalski',
            'email' => 'JAN@example.com',
            'email_verified' => true,
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::query()->sole();

        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('jan@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_login_does_not_replace_an_existing_password(): void
    {
        $password = Hash::make('existing-password');
        $user = User::factory()->create([
            'email' => 'jan@example.com',
            'password' => $password,
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'email' => 'jan@example.com',
            'email_verified' => true,
        ]));

        $this->get(route('google.callback'))->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertSame($password, $user->password);
        $this->assertSame('google-123', $user->google_id);
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'email' => 'jan@example.com',
            'email_verified' => false,
        ]));

        $response = $this->from(route('login'))->get(route('google.callback'));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_email_cannot_be_linked_to_another_google_identity(): void
    {
        User::factory()->create([
            'email' => 'jan@example.com',
            'google_id' => 'google-original',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-attacker',
            'email' => 'jan@example.com',
            'email_verified' => true,
        ]));

        $response = $this->from(route('login'))->get(route('google.callback'));

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
