<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = User::find($this->route('id'));

        return hash_equals(
            (string)$this->route('hash'), sha1($user->email)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfillEmail(): void
    {
        $user = User::find($this->route('id'));

        if (!$user->hasVerifiedEmail())
            $user->markEmailAsVerified();
    }
}
