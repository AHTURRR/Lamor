<?php

namespace App\Http\Middleware;

use App\Services\TrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTrackingToken
{
    public function __construct(
        private TrackingService $trackingService,
    ) {}

    /**
     * Validate the tracking token from the Authorization header.
     * Injects the resolved TrackingSession into the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'error' => 'Token tidak ditemukan.',
                'code' => 'TOKEN_MISSING',
            ], 401);
        }

        $session = $this->trackingService->findSessionByToken($token);

        if (! $session) {
            return response()->json([
                'error' => 'Token tidak valid atau sesi telah berakhir.',
                'code' => 'TOKEN_INVALID',
            ], 401);
        }

        if ($session->is_expired) {
            return response()->json([
                'error' => 'Sesi pelacakan telah kedaluwarsa.',
                'code' => 'SESSION_EXPIRED',
            ], 410);
        }

        $request->merge(['tracking_session' => $session]);

        return $next($request);
    }
}
