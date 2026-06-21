<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;

class GitHubAuthController extends Controller
{
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => env('SOCIALITE_VERIFY_SSL', true)]))
            ->user();

        $user = User::updateOrCreate([
            'github_id'=> $githubUser->id,
        ], [
            'name' => $githubUser->name,
            'password' => Hash::make(Str::random(50)),
            'email' => $githubUser->email,
            'github_token' => $githubUser->token,
        ]);

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

}
