<?php

namespace App\Http\Requests\Auth;

use App\Rules\RecaptchaValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'celular' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'recaptcha_token' => ['required', 'string',  new RecaptchaValidationRule()],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'email.unique' =>  __('validation.custom.email_unavailable'),
        ];
    }
}
