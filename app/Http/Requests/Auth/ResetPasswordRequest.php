<?php

namespace App\Http\Requests\Auth;

use App\Rules\RecaptchaValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'recaptcha_token' => ['required', 'string', new RecaptchaValidationRule()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.exists' => __('validation.custom.email_unavailable'),
            'password.confirmed' => __('validation.custom.password_confirmation_mismatch'),
            'password.min' => __('validation.custom.password_min_length'),
        ];
    }
}
