<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class JwtService
{
    /**
     * Secret key for signing tokens.
     */
    protected string $secret;

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret ?: (string) (config('app.jwt_secret') ?: env('JWT_SECRET', config('app.key')));
    }

    /**
     * Generate a JWT token for a user.
     */
    public function generateToken(User $user, int $ttl = 86400): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $now = time();
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $now + $ttl,
            'jti' => bin2hex(random_bytes(16)),
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Validate a JWT token and return its payload, or null if invalid.
     */
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);
        $providedSignature = $this->base64UrlDecode($signatureEncoded);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($payloadEncoded);
        $payload = json_decode($payloadJson, true);

        if (! is_array($payload) || ! isset($payload['exp']) || ! isset($payload['sub'])) {
            return null;
        }

        if (time() >= $payload['exp']) {
            return null;
        }

        // Check if token or jti is blacklisted
        $tokenKey = 'jwt_blacklist:'.md5($token);
        if (Cache::has($tokenKey)) {
            return null;
        }

        if (isset($payload['jti']) && Cache::has('jwt_blacklist:'.$payload['jti'])) {
            return null;
        }

        return $payload;
    }

    /**
     * Invalidate (blacklist) a JWT token.
     */
    public function invalidateToken(string $token): void
    {
        $payload = $this->validateToken($token);
        $ttl = 86400;

        if ($payload && isset($payload['exp'])) {
            $ttl = max(60, $payload['exp'] - time());
        }

        Cache::put('jwt_blacklist:'.md5($token), true, $ttl);

        if ($payload && isset($payload['jti'])) {
            Cache::put('jwt_blacklist:'.$payload['jti'], true, $ttl);
        }
    }

    /**
     * Encode string to base64url.
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode base64url string.
     */
    protected function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}
