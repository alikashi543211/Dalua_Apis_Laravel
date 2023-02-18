<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function login()
    {
        return view('admin.auth.login');
    }

    public function authenticateUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $inputs = $request->all();
        if ($user  = $this->user->newQuery()->where('email', $inputs['email'])->where('role_id', USER_ADMIN)->first()) {
            if (Auth::attempt($request->only(['email', 'password']), !empty($inputs['remember_me']) ? true : false)) {
                return redirect()->route('admin.dashboard');
            } else return redirect()->back()->with('error', __('auth.invalidCredentials'))->withInput();
        } else return redirect()->back()->with('error', __('auth.invalidCredentials'))->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
