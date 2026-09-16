<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Private channel for admin tracking dashboard.
// Only authenticated users can subscribe.
Broadcast::channel('tracking', function ($user) {
    return $user !== null;
});
