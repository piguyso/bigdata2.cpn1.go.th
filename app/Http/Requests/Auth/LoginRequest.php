<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
        $password = $this->input('password');

        \Illuminate\Support\Facades\Log::info("Login attempt: Email: {$email}, Password length: " . strlen($password));

        // 1. Try local authentication first (for local admin/users with normal password)
        $user = \App\Models\User::where('email', $email)->first();
        if ($user) {
            \Illuminate\Support\Facades\Log::info("Local user found: Email: {$user->email}, DB Password hash: " . substr($user->password, 0, 15));
            if ($user->password !== '!external_api_login!') {
                $attempt = Auth::attempt($this->only('email', 'password'), $this->boolean('remember'));
                \Illuminate\Support\Facades\Log::info("Local Auth attempt result: " . ($attempt ? 'SUCCESS' : 'FAILED'));
                if ($attempt) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            } else {
                \Illuminate\Support\Facades\Log::info("Local user has !external_api_login! password flag. Skipping local auth.");
            }
        } else {
            \Illuminate\Support\Facades\Log::info("Local user not found in database.");
        }

        // 2. Try external API authentication
        $disableApiAuth = '0';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $disableApiAuth = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'disable_api_auth')->value('value') ?? '0';
            }
        } catch (\Exception $e) {
            Log::error('Failed to check disable_api_auth setting: ' . $e->getMessage());
        }

        $apiUrl = (string) config('services.external_login.url', '');
        $apiKey = (string) config('services.external_login.key', '');
        $apiTimeout = max(3, (int) config('services.external_login.timeout', 10));

        if ($disableApiAuth === '1') {
            Log::info('External API authentication is disabled via database setting.');
        } elseif ($apiUrl === '' || $apiKey === '' || ! filter_var($apiUrl, FILTER_VALIDATE_URL) || parse_url($apiUrl, PHP_URL_SCHEME) !== 'https') {
            Log::warning('External API authentication skipped because configuration is missing or insecure.');
        } else {
            try {
                $response = Http::acceptJson()
                    ->timeout($apiTimeout)
                    ->withHeaders([
                'Accept' => 'application/json',
                'X-API-KEY' => $apiKey
                    ])
                    ->asForm()
                    ->post($apiUrl, [
                        'username' => $email,
                        'password' => $password
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $apiUser = $data['user'] ?? null;

                    if ($apiUser && isset($apiUser['id'])) {
                        $fname = trim($apiUser['fname'] ?? '');
                        $lname = trim($apiUser['lname'] ?? '');
                        $fullName = trim($fname . ' ' . $lname);
                        if ($fullName === '') {
                            $fullName = $apiUser['username'] ?? $email;
                        }

                        $dbUser = \App\Models\User::updateOrCreate(
                            ['email' => $apiUser['email'] ?? $email],
                            [
                                'name' => $fullName,
                                'password' => '!external_api_login!',
                                'role' => ($apiUser['user_level'] ?? 0) === 99 ? 'admin' : 'viewer',
                            ]
                        );

                        Auth::login($dbUser, $this->boolean('remember'));
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                }
            } catch (\Exception $e) {
                Log::error('External API authentication failed: ' . $e->getMessage());
            }
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
