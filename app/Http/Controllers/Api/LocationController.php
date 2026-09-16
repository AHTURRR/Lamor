<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationLog;
use App\Models\TrackingSession;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        private TrackingService $trackingService,
    ) {}

    /**
     * Receive and store a location update from the target client.
     * Authenticated via ValidateTrackingToken middleware.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'altitude' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        /** @var TrackingSession $session */
        $session = $request->get('tracking_session');

        $log = $this->trackingService->recordLocation($session, $validated);

        return response()->json([
            'data' => [
                'id' => $log->id,
                'recorded' => true,
            ],
        ], 201);
    }

    /**
     * Get location history for a session (admin auth required).
     */
    public function history(Request $request, int $sessionId): JsonResponse
    {
        $session = TrackingSession::where('created_by', $request->user()->id)
            ->findOrFail($sessionId);

        $logs = LocationLog::where('session_id', $session->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (LocationLog $log) => [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'accuracy' => $log->accuracy,
                'altitude' => $log->altitude,
                'speed' => $log->speed,
                'heading' => $log->heading,
                'recorded_at' => $log->recorded_at?->toISOString(),
                'created_at' => $log->created_at?->toISOString(),
            ]);

        return response()->json(['data' => $logs]);
    }

    /**
     * Get the latest location for a session (admin auth required).
     */
    public function latest(Request $request, int $sessionId): JsonResponse
    {
        $session = TrackingSession::where('created_by', $request->user()->id)
            ->findOrFail($sessionId);

        $log = LocationLog::where('session_id', $session->id)
            ->latest('created_at')
            ->first();

        if (! $log) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'latitude' => $log->latitude,
                'longitude' => $log->longitude,
                'accuracy' => $log->accuracy,
                'altitude' => $log->altitude,
                'speed' => $log->speed,
                'heading' => $log->heading,
                'recorded_at' => $log->recorded_at?->toISOString(),
                'created_at' => $log->created_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Get all active sessions with their latest locations (admin dashboard polling).
     */
    public function allLatest(Request $request): JsonResponse
    {
        $sessions = TrackingSession::with('latestLocation')
            ->where('created_by', $request->user()->id)
            ->whereIn('status', ['active', 'pending'])
            ->where('expires_at', '>', now())
            ->get()
            ->map(function (TrackingSession $session) {
                return [
                    'session_id' => $session->id,
                    'phone_number' => $session->phone_number,
                    'label' => $session->label,
                    'status' => $session->status,
                    'expires_at' => $session->expires_at->toISOString(),
                    'location' => $session->latestLocation ? [
                        'latitude' => $session->latestLocation->latitude,
                        'longitude' => $session->latestLocation->longitude,
                        'accuracy' => $session->latestLocation->accuracy,
                        'speed' => $session->latestLocation->speed,
                        'heading' => $session->latestLocation->heading,
                        'recorded_at' => $session->latestLocation->recorded_at?->toISOString(),
                    ] : null,
                ];
            });

        return response()->json(['data' => $sessions]);
    }
}
