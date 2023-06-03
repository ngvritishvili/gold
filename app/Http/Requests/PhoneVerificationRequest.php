<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PhoneVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->route('user');

        return (isset($user) && isset($user->otp->code));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * set user verified by phone.
     *
     * @return bool
     * @throws ValidationException
     */
    public function fulfillPhone()
    {
        $user = $this->route('user');

        if ($user->otp->code === $this->route('code')
            && $user->otp->updated_at > now()->utc()->subSeconds(180))
        {
            $user->otp()->delete();

            return $user->update(['phone_verified_at' => now()->utc()->toDateTimeString()]);
        }

        if ($user->otp->code !== $this->route('code'))
        {
            throw ValidationException::withMessages([
                501 => 'OTP code is invalid!',
            ]);
        }
        if ($user->otp->code === $this->route('code')
            && !($user->otp->updated_at > now()->utc()->subSeconds(180)))
        {
            throw ValidationException::withMessages([
                501 => 'OTP Code expired!',
            ]);
        }

        throw ValidationException::withMessages([
            503 => 'Something went wrong please contact support!',
        ]);

    }
}
