<?php

namespace App\Services;

use App\Events\LocationUpdated;
use App\Models\LocationLog;
use App\Models\TrackingSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TrackingService
{
    public function __construct(
        private SmsService $smsService,
    ) {}

    /**
     * Create a new tracking session and send SMS with tracking link.
     *
     * @param  array{phone_number: string, label?: string}  $data
     * @return array{session: TrackingSession, token: string, sms_sent: bool}
     */
    public function createSession(array $data, int $adminId): array
    {
        $plainToken = Str::random(64);

        $session = TrackingSession::create([
            'phone_number' => $data['phone_number'],
            'token_hash' => Hash::make($plainToken),
            'label' => $data['label'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addHour(),
            'created_by' => $adminId,
        ]);

        $trackingUrl = url("/track/{$plainToken}");

        $message = "Anda diminta untuk berbagi lokasi. Buka tautan ini untuk memulai: {$trackingUrl}\n\nTautan berlaku 1 jam.";

        $smsSent = $this->smsService->send($data['phone_number'], $message);

        return [
            'session' => $session,
            'token' => $plainToken,
            'sms_sent' => $smsSent,
        ];
    }

    /**
     * Find and validate a tracking session by its plain token.
     */
    public function findSessionByToken(string $plainToken): ?TrackingSession
    {
        $sessions = TrackingSession::whereIn('status', ['pending', 'active'])
            ->where('expires_at', '>', now())
            ->get();

        foreach ($sessions as $session) {
            if (Hash::check($plainToken, $session->token_hash)) {
                return $session;
            }
        }

        return null;
    }

    /**
     * Activate a pending session when the target opens the tracking link.
     */
    public function activateSession(TrackingSession $session, string $ipAddress, string $userAgent): TrackingSession
    {
        if ($session->status === 'pending') {
            $session->update([
                'status' => 'active',
                'activated_at' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        }

        return $session->fresh();
    }

    /**
     * Record a location update from the target device.
     *
     * @param  array{latitude: float, longitude: float, accuracy?: float, altitude?: float, speed?: float, heading?: float, recorded_at?: string}  $data
     */
    public function recordLocation(TrackingSession $session, array $data): LocationLog
    {
        $logData = [
            'session_id' => $session->id,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'accuracy' => $data['accuracy'] ?? null,
            'altitude' => $data['altitude'] ?? null,
            'speed' => $data['speed'] ?? null,
            'heading' => $data['heading'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ];

        $log = LocationLog::create($logData);

        // Update spatial column if using MySQL
        if (config('database.default') === 'mysql') {
            DB::statement(
                'UPDATE location_logs SET coordinates = ST_GeomFromText(?, 4326) WHERE id = ?',
                ["POINT({$data['longitude']} {$data['latitude']})", $log->id]
            );
        }

        // Broadcast location update to admin dashboard
        event(new LocationUpdated(
            sessionId: $session->id,
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            accuracy: (float) ($data['accuracy'] ?? 0),
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null,
            speed: isset($data['speed']) ? (float) $data['speed'] : null,
            heading: isset($data['heading']) ? (float) $data['heading'] : null,
            recordedAt: $log->recorded_at?->toISOString() ?? now()->toISOString(),
            phoneNumber: $session->phone_number,
            label: $session->label,
        ));

        return $log;
    }

    /**
     * Revoke an active tracking session.
     */
    public function revokeSession(TrackingSession $session): void
    {
        $session->update(['status' => 'revoked']);
    }

    /**
     * Mark expired sessions in the database.
     */
    public function cleanupExpiredSessions(): int
    {
        return TrackingSession::whereIn('status', ['pending', 'active'])
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
    }
}
