<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SsoLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->only('login');
    }

    public function issueToken(Request $request)
    {
        $apiKey = (string) config('services.sso.issue_api_key');
        $provided = (string) $request->header('X-SSO-API-KEY', '');

        if ($apiKey === '' || !hash_equals($apiKey, $provided)) {
            abort(401, 'Unauthorized.');
        }

        $identity = trim((string) $request->input('identity', ''));
        $password = (string) $request->input('password', '');

        if ($identity === '' || $password === '') {
            return response()->json([
                'message' => 'identity and password are required',
            ], 422);
        }

        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!Auth::validate([$field => $identity, 'password' => $password])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = User::where($field, $identity)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $ttl = (int) config('services.sso.token_ttl_seconds', 60);
        $payload = [
            'user_id' => (int) $user->id,
            'identity' => $identity,
            'email' => (string) $user->email,
            'username' => (string) $user->username,
            'iat' => time(),
            'exp' => time() + max(10, $ttl),
            'nonce' => bin2hex(random_bytes(16)),
        ];

        return response()->json([
            'token' => $this->signPayload($payload),
            'redirect' => (string) config('services.sso.default_redirect', '/dashboard'),
        ]);
    }

    public function login(Request $request)
    {
        $token = (string) $request->query('token', '');
        $secret = (string) config('services.sso.shared_secret');

        if ($secret === '') {
            Log::warning('SSO login blocked: missing SSO_SHARED_SECRET');
            abort(503, 'SSO is not configured.');
        }

        $payload = $this->validateAndDecodeToken($token, $secret);
        if (!$payload) {
            abort(403, 'Invalid SSO token.');
        }

        if (!$this->validateExpiry($payload)) {
            abort(403, 'Expired SSO token.');
        }

        if (!$this->reserveNonce($payload)) {
            abort(403, 'SSO token already used.');
        }

        $user = $this->resolveUser($payload);
        if (!$user) {
            abort(403, 'SSO user not found.');
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect($this->resolveRedirect($request));
    }

    private function validateAndDecodeToken($token, $secret)
    {
        if ($token === '' || strpos($token, '.') === false) {
            return null;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        list($data, $providedSignature) = $parts;
        $expectedSignature = hash_hmac('sha256', $data, $secret);

        if (!hash_equals($expectedSignature, $providedSignature)) {
            Log::warning('SSO signature mismatch.');
            return null;
        }

        $decoded = $this->decodePayload($data);
        if (!$decoded || !is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function decodePayload($data)
    {
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        }

        if ($decoded === false) {
            return null;
        }

        return json_decode($decoded, true);
    }

    private function validateExpiry(array $payload)
    {
        if (!isset($payload['exp']) || !is_numeric($payload['exp'])) {
            return false;
        }

        $clockSkew = (int) config('services.sso.clock_skew_seconds', 60);
        $expiresAt = (int) $payload['exp'];
        $now = time();

        if ($expiresAt + $clockSkew < $now) {
            return false;
        }

        if (isset($payload['iat']) && is_numeric($payload['iat'])) {
            $issuedAt = (int) $payload['iat'];
            if ($issuedAt - $clockSkew > $now) {
                return false;
            }
        }

        return true;
    }

    private function reserveNonce(array $payload)
    {
        if (empty($payload['nonce'])) {
            return true;
        }

        $nonce = (string) $payload['nonce'];
        $ttlSeconds = 120;

        if (!empty($payload['exp']) && is_numeric($payload['exp'])) {
            $ttlSeconds = max(1, (int) $payload['exp'] - time() + 30);
        }

        return Cache::add('sso_nonce:' . $nonce, 1, now()->addSeconds($ttlSeconds));
    }

    private function resolveUser(array $payload)
    {
        if (!empty($payload['user_id']) && is_numeric($payload['user_id'])) {
            $user = User::find((int) $payload['user_id']);
            if ($user) {
                return $user;
            }
        }

        if (!empty($payload['email'])) {
            $user = User::where('email', $payload['email'])->first();
            if ($user) {
                return $user;
            }
        }

        if (!empty($payload['username'])) {
            $user = User::where('username', $payload['username'])->first();
            if ($user) {
                return $user;
            }
        }

        if (!empty($payload['identity'])) {
            $identity = $payload['identity'];
            $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            return User::where($field, $identity)->first();
        }

        return null;
    }

    private function resolveRedirect(Request $request)
    {
        $redirect = (string) $request->query('redirect', '');
        if ($redirect !== '' && strpos($redirect, '/') === 0 && strpos($redirect, '//') !== 0) {
            return $redirect;
        }

        return (string) config('services.sso.default_redirect', '/dashboard');
    }

    private function signPayload(array $payload)
    {
        $secret = (string) config('services.sso.shared_secret');
        $data = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $data, $secret);

        return $data . '.' . $signature;
    }
}
