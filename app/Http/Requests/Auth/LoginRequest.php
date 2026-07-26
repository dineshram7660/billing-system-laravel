<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Verify credentials and log the request into the web session guard.
     * Web (Blade) login only — App\Http\Controllers\Api\AuthController
     * uses resolveUser() directly instead, since a token API must not
     * have the side effect of starting a session guard login.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = $this->resolveUser();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Verify the request's credentials and return the matching user, with
     * no side effect beyond the legacy-MD5-to-bcrypt upgrade (see
     * attemptLegacyMd5Login()) — no session/guard login. Shared by the
     * web login flow (authenticate(), above) and the API login endpoint.
     *
     * @throws ValidationException
     */
    public function resolveUser(): ?User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->string('email'))->first();

        if (! $user) {
            return null;
        }

        return Hash::isHashed($user->password)
            ? ($this->verifyBcryptPassword($user) ? $user : null)
            : ($this->upgradeLegacyMd5Password($user) ? $user : null);
    }

    /**
     * The legacy app hashed passwords with unsalted MD5 (see
     * admin/login.php in the old codebase), which isn't a format
     * Hash::check() accepts — it throws rather than just failing for a
     * non-bcrypt value. Route accounts still on the old hash here
     * instead of calling that at all.
     */
    protected function verifyBcryptPassword(User $user): bool
    {
        return Hash::check($this->string('password'), $user->password);
    }

    /**
     * Verify against the legacy MD5 hash once, then rehash to bcrypt via
     * the User model's 'hashed' cast so this path is never needed again
     * for that account.
     */
    protected function upgradeLegacyMd5Password(User $user): bool
    {
        if (! hash_equals($user->password, md5((string) $this->string('password')))) {
            return false;
        }

        $user->password = (string) $this->string('password');
        $user->save();

        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
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
