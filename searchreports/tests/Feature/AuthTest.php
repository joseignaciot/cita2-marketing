<?php

use App\Jobs\SyncGscPropertiesJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    Queue::fake();
});

it('redirects to google oauth', function () {
    $this->get(route('auth.google'))
        ->assertRedirect();
});

it('creates user from google callback', function () {
    $fakeGoogleUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
    $fakeGoogleUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
    $fakeGoogleUser->shouldReceive('getName')->andReturn('New User');
    $fakeGoogleUser->shouldReceive('getId')->andReturn('google-123');
    $fakeGoogleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $fakeGoogleUser->token = 'fake-token';
    $fakeGoogleUser->refreshToken = 'fake-refresh';
    $fakeGoogleUser->expiresIn = 3600;

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($fakeGoogleUser);
    $provider->shouldReceive('scopes')->andReturnSelf();
    $provider->shouldReceive('with')->andReturnSelf();

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard'));

    expect(User::where('email', 'newuser@example.com')->exists())->toBeTrue();
});

it('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

it('guests are redirected from protected routes', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
