<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmHelper
{
    /**
     * Send push notification to all devices registered by a specific User ID.
     */
    public static function sendToUser($userId, $title, $body, $data = [])
    {
        $tokens = DB::table('users_fcm_tokens')
            ->where('user_id', $userId)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("FcmHelper: No tokens found for User ID: " . $userId);
            return false;
        }

        return self::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to multiple FCM Registration Tokens via Firebase HTTP v1 API.
     */
    public static function sendToTokens(array $tokens, $title, $body, $data = [])
    {
        $credentialsPath = storage_path('app/firebase_credentials.json');

        if (!file_exists($credentialsPath)) {
            Log::warning("FcmHelper: Firebase HTTP v1 Credentials file not found at " . $credentialsPath);
            return false;
        }

        $projectId = null;
        $accessToken = self::getAccessToken($credentialsPath, $projectId);
        if (!$accessToken || !$projectId) {
            Log::error("FcmHelper: Failed to generate Google Access Token for Firebase HTTP v1.");
            return false;
        }

        $iconUrl = url('/adminlte/img/user2.png');
        $actionUrl = isset($data['action_url']) ? url($data['action_url']) : url('/dashboard');

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $successCount = 0;
        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'image' => $iconUrl
                    ],
                    'data' => array_merge([
                        'title' => (string)$title,
                        'body' => (string)$body,
                        'icon' => (string)$iconUrl,
                        'action_url' => (string)$actionUrl
                    ], array_map('strval', $data)),
                    'webpush' => [
                        'headers' => [
                            'Urgency' => 'high'
                        ],
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'icon' => $iconUrl,
                            'click_action' => $actionUrl
                        ]
                    ]
                ]
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ])->post($url, $payload);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    Log::error("FcmHelper HTTP v1 error for token: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("FcmHelper HTTP v1 exception: " . $e->getMessage());
            }
        }

        return $successCount > 0;
    }

    /**
     * Generate OAuth2 Access Token for Google APIs via RS256 JWT.
     */
    private static function getAccessToken($credentialsPath, &$projectId)
    {
        $json = json_decode(file_get_contents($credentialsPath), true);
        if (!$json || !isset($json['private_key'], $json['client_email'], $json['project_id'])) {
            return null;
        }

        $projectId = $json['project_id'];
        $privateKey = $json['private_key'];
        $clientEmail = $json['client_email'];

        $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $claim = self::base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signatureInput = $header . '.' . $claim;
        openssl_sign($signatureInput, $signature, $privateKey, 'SHA256');
        $jwt = $signatureInput . '.' . self::base64UrlEncode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error("Google OAuth token request failed: " . $response->body());
        return null;
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
