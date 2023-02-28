<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
	public function redirectToProvider()
	{
		$url = Socialite::driver('google')->redirect()->getTargetUrl();
		return response()->json(['url' => $url]);
	}

	public function handleProviderCallback()
	{
		$provider_user = Socialite::driver('google')->stateless()->user();
		if (Email::where('email', $provider_user->email)->first())
		{
			return response()->json(['message' => __('main.already_registered')], 422);
		}
		$user = User::updateOrCreate([
			'gmail' => $provider_user->email,
		], [
			'username'          => $provider_user->name,
			'gmail'             => $provider_user->email,
			'email'             => $provider_user->email,
			'avatar'            => $provider_user->avatar,
			'email_verified_at' => Carbon::now(),
		]);

		if (!Auth::login($user))
		{
			return response(['error' => "Something's wrong, not logged in"]);
		}

		return response()->json(['user' => $user]);
	}
}
