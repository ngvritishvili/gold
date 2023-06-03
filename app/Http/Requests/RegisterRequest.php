<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email'           => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users'],
            'phone'           => ['required_without:email', 'digits:9', 'unique:users', 'starts_with:5'],
            'password'        => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->rules(['max:64'])],
            'identification'  => ['string'],
            'first_name'      => ['required','string','max:55', 'min:3'],
            'last_name'       => ['required','string','max:55', 'min:3'],
        ];
    }

    /**
     * Prepare data for validation
     * Distinguish email and phone
     *
     * @return void
     * @throws ValidationException
     */
    public function prepareForValidation(): void
    {
        $identification = $this->get('identification');

        $key = filter_var($identification, FILTER_VALIDATE_EMAIL)
            ? 'email' : 'phone';

        $this->merge([$key => $identification]);
    }
}
