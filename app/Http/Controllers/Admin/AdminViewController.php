<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminViewController extends Controller
{
    /**
     * Show the admin login page.
     */
    public function login()
    {
        return view('admin.login');
    }

    /**
     * Show the admin dashboard.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Show the user management page.
     */
    public function users()
    {
        return view('admin.users');
    }
}
