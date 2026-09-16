<?php

namespace App\Http\Controllers;

use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingPageController extends Controller
{
    public function __construct(
        private TrackingService $trackingService,
    ) {}

    /**
     * Render the location capture page for the target user.
     * Validates the one-time token and activates the session.
     */
    public function capture(Request $request, string $token): View
    {
        $session = $this->trackingService->findSessionByToken($token);

        if (! $session || $session->is_expired) {
            return view('tracking.expired');
        }

        // Activate session on first visit
        $this->trackingService->activateSession(
            $session,
            $request->ip(),
            $request->userAgent() ?? 'Unknown'
        );

        return view('tracking.capture', [
            'token' => $token,
            'sessionId' => $session->id,
            'expiresAt' => $session->expires_at->toISOString(),
        ]);
    }
}
