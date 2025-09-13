<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaValidationRule implements ValidationRule
{
    /**
     * @var string RECAPTCHA_URL
     */
    protected const RECAPTCHA_URL = "https://www.google.com/recaptcha/api";

    /**
     * @var string RECAPTCHA_ENDPOINT
     */
    protected const RECAPTCHA_ENDPOINT = "siteverify";

    /**
     * Secret token do reCAPTCHA
     *
     * @var string
     */
    protected string $secretToken;

    /**
     * Ambiente
     *
     * @var string
     */
    protected string $env;

    /**
     * Construtor da Rule
     */
    public function __construct()
    {
        $this->secretToken = config('app.recaptcha_secret_token_v3');
        $this->env = config('app.env');
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (in_array($this->env, ["development", "local"])) {
            return;
        }

        $data = [
            'secret'    => $this->secretToken,
            'response'  => $value
        ];

        $response = Http::post(self::RECAPTCHA_URL . '/' . self::RECAPTCHA_ENDPOINT, $data);
        $result = $response->json();

        if ($result['success'] === true) {
            if ($result['score'] >= config('app.recaptcha_score')) {
                return;
            }
            Log::info(__('auth.recaptcha_score'), array_merge($result, [
                'response'  => $value,
            ]));
        } else {
            Log::info(__('auth.recaptcha_token'), array_merge($result, [
                'response'  => $value,
            ]));
        }

        $fail(__('auth.recaptcha'));
    }
}
