<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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

        // 1. Try local authentication first (for local admin/users with normal password)
        $user = \App\Models\User::where('email', $email)->first();
        if ($user && $user->password !== '!external_api_login!') {
            if (Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        // 2. Try external API authentication
        $apiUrl = env('EXTERNAL_LOGIN_API_URL', 'https://salary.cpn1.go.th/api/external/login');
        $apiKey = env('EXTERNAL_LOGIN_API_KEY', 'ea940c1b1968568de50720ceda9084abd8d66e259664fa5631811a982fc72532');

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                'Accept' => 'application/json',
                'X-API-KEY' => $apiKey
            ])->asForm()->post($apiUrl, [
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
            \Illuminate\Support\Facades\Log::error('External API authentication failed: ' . $e->getMessage());
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
