<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegisterRequest;
use App\Models\Email;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
	public function register(StoreRegisterRequest $request)
	{
		if (Email::where('email', $request->email)->first() || User::where('gmail', $request->email)->first())
		{
			return response([
                'errors'=> ['email' => __('main.already_registered')
                ]], 422);
		}
		$credentials = $request->validated();
		$credentials['password'] = bcrypt($credentials['password']);
		array_pop($credentials);
		$credentials['avatar'] = '/icons/avatar.png';
		$credentials['primary_email'] = $request->email;

		event(new Registered($user = User::create($credentials)));
		Email::create([
			'user_id' => $user->id,
			'email'   => $user->email,
		]);

		Auth::login($user);

		return response()->json([
			'success' => 200,
		]);
	}

	public function verifyEmail()
	{
		return response()->json([
			'success' => 200,
			'message' => 'email verification sent',
		]);
	}
}
