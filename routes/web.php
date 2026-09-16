<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrackingPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Tracking page (public — accessed by target via SMS link)
Route::get('/track/{token}', [TrackingPageController::class, 'capture'])
    ->name('tracking.capture')
    ->where('token', '[A-Za-z0-9]{64}');

// Admin auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (auth()->attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->onlyInput('email');
})->name('login.submit');

Route::post('/logout', function (Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// Admin dashboard (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Generate API token for dashboard AJAX
    Route::post('/api-token', function (Request $request) {
        $request->user()->tokens()->delete();
        $token = $request->user()->createToken('dashboard')->plainTextToken;

        return response()->json(['token' => $token]);
    })->name('api.token');
});
