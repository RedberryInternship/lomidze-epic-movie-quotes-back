<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
	public function login(Request $request)
	{
		$email = Email::where('email', $request->email)->first();
		if (!$email || !Hash::check(request()->password, $email->user->password))
		{
			return response()->json([
				'errors' => ['email' => __('main.invalid')],
			], 401);
		}

		auth()->login($email->user);
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
