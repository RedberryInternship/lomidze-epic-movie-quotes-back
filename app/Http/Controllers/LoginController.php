<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
	public function login(Request $request)
	{
		$user = User::where('username', $request->email)->first();
		$email = Email::where('email', $request->email)->first();
		$password = $user?->password ?? $email?->user->password;

		if (!$user && !$email || !Hash::check(request()->password, $password))
		{
			return response()->json([
				'errors' => ['email' => __('main.invalid')],
			], 401);
		}

		$user ??= $email->user;

		auth()->login($user, (bool)$request->remember_me);
		request()->session()->regenerate();
		return response(['user' => auth()->user()]);
	}

	public function logout()
	{
		auth()->logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();
		return response(['message' => 'Logged out']);
	}

	public function getUser()
	{
		return response(['user' => auth()->user()]);
	}

	public function checkLoggedIn()
	{
		return response(['message'=>'authenticated']);
	}
}
