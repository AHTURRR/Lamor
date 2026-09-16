<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard with the interactive map.
     */
    public function index(Request $request): View
    {
        return view('dashboard.index');
    }
}
