<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGscPropertiesJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(config('gsc.scopes'))
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken ?? null,
                'google_token_expires_at' => isset($googleUser->expiresIn)
                    ? now()->addSeconds($googleUser->expiresIn)
                    : now()->addHour(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->hasAnyRole(['admin', 'user'])) {
            $user->assignRole('user');
        }

        Auth::login($user, true);

        SyncGscPropertiesJob::dispatch($user)->onQueue('gsc-fetch');

        return redirect()->intended(route('dashboard'));
    }
}
