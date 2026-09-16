<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    /**
     * Send an SMS message to the given phone number.
     * Uses log driver for development — replace with Twilio/Fonnte in production.
     */
    public function send(string $phoneNumber, string $message): bool
    {
        $driver = config('services.sms.driver', 'log');

        return match ($driver) {
            'twilio' => $this->sendViaTwilio($phoneNumber, $message),
            'fonnte' => $this->sendViaFonnte($phoneNumber, $message),
            default => $this->sendViaLog($phoneNumber, $message),
        };
    }

    /**
     * Log the SMS message (development mode).
     */
    private function sendViaLog(string $phoneNumber, string $message): bool
    {
        Log::channel('sms')->info("SMS to {$phoneNumber}: {$message}");

        return true;
    }

    /**
     * Send SMS via Twilio.
     * Requires TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM in .env.
     */
    private function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            $client = new Client($sid, $token);
            $client->messages->create($phoneNumber, [
                'from' => $from,
                'body' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Twilio SMS failed: {$e->getMessage()}", [
                'phone' => $phoneNumber,
            ]);

            return false;
        }
    }

    /**
     * Send SMS via Fonnte API.
     * Requires FONNTE_TOKEN in .env.
     */
    private function sendViaFonnte(string $phoneNumber, string $message): bool
    {
        try {
            $token = config('services.fonnte.token');

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phoneNumber,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Fonnte SMS failed: {$e->getMessage()}", [
                'phone' => $phoneNumber,
            ]);

            return false;
        }
    }
}
