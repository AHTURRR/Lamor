<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackingSession;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(
        private TrackingService $trackingService,
    ) {}

    /**
     * List all tracking sessions for the authenticated admin.
     */
    public function index(Request $request): JsonResponse
    {
        $sessions = TrackingSession::with('latestLocation')
            ->where('created_by', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (TrackingSession $session) {
                return [
                    'id' => $session->id,
                    'phone_number' => $session->phone_number,
                    'label' => $session->label,
                    'status' => $session->status,
                    'is_expired' => $session->is_expired,
                    'expires_at' => $session->expires_at->toISOString(),
                    'activated_at' => $session->activated_at?->toISOString(),
                    'created_at' => $session->created_at->toISOString(),
                    'latest_location' => $session->latestLocation ? [
                        'latitude' => $session->latestLocation->latitude,
                        'longitude' => $session->latestLocation->longitude,
                        'accuracy' => $session->latestLocation->accuracy,
                        'recorded_at' => $session->latestLocation->recorded_at?->toISOString(),
                    ] : null,
                ];
            });

        return response()->json(['data' => $sessions]);
    }

    /**
     * Create a new tracking session and send SMS.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->trackingService->createSession($validated, $request->user()->id);

        return response()->json([
            'data' => [
                'id' => $result['session']->id,
                'phone_number' => $result['session']->phone_number,
                'label' => $result['session']->label,
                'status' => $result['session']->status,
                'tracking_url' => $result['session']->getTrackingUrl($result['token']),
                'expires_at' => $result['session']->expires_at->toISOString(),
                'sms_sent' => $result['sms_sent'],
            ],
        ], 201);
    }

    /**
     * Get details of a specific session with location history.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $session = TrackingSession::with(['locationLogs' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('created_by', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $session->id,
                'phone_number' => $session->phone_number,
                'label' => $session->label,
                'status' => $session->status,
                'is_expired' => $session->is_expired,
                'expires_at' => $session->expires_at->toISOString(),
                'activated_at' => $session->activated_at?->toISOString(),
                'created_at' => $session->created_at->toISOString(),
                'location_logs' => $session->locationLogs->map(fn ($log) => [
                    'latitude' => $log->latitude,
                    'longitude' => $log->longitude,
                    'accuracy' => $log->accuracy,
                    'altitude' => $log->altitude,
                    'speed' => $log->speed,
                    'heading' => $log->heading,
                    'recorded_at' => $log->recorded_at?->toISOString(),
                ]),
            ],
        ]);
    }

    /**
     * Revoke/stop a tracking session.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $session = TrackingSession::where('created_by', $request->user()->id)
            ->findOrFail($id);

        $this->trackingService->revokeSession($session);

        return response()->json(['message' => 'Sesi pelacakan dihentikan.']);
    }
}
