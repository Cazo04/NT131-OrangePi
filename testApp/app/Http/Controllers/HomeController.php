<?php

namespace App\Http\Controllers;

use App\Models\SystemStatus;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index()
    {
        $systemStatus = SystemStatus::first();
        if (Auth::check()) return view('home', ['systemStatus' => $systemStatus]);
        return view('home');
    }
    public function login()
    {
        return view('login');
    }
    public function subLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $remember = $request->has('remember');
        if (auth()->attempt($credentials, $remember)) {
            return redirect()->route('home');
        }
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.'
        ]);
    }
    public function register()
    {
        return view('register');
    }
    public function subRegister(Request $request)
    {
        $messages = [
            'name.required' => 'You must enter a name.',
            'email.required' => 'You must enter an email address.',
            'email.email' => 'The email format is invalid.',
            'email.unique' => 'The email has already been used.',
            'password.required' => 'You must enter a password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ], $messages);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login');
    }
    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }
}
