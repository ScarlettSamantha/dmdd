<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Scarlett\DMDD\GUI\Http\Controllers\GuiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class AuthController extends GuiController
{
    public function get()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        // Attempt to authenticate the user
        if (Auth::attempt($request->only('email', 'password'))) {
            // Regenerate session and redirect to dashboard
            $request->session()->regenerate();
            return redirect('/')->with('success', 'Login successful.');
        }

        // Redirect back with error message if authentication fails
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        // Log the user out
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'Logout successful.');
    }
}