<?php

namespace App\Http\Requests\Auth;

use App\Enums\Auth\PasswordEnum;
use App\Messages\User\UserMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Password;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException((
            response()->json($validator->errors(), Response::HTTP_BAD_REQUEST))
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,dns',
            ],
            'password' => [
                'required',
                Password::min(PasswordEnum::MIN_LENGTH->value)
                    ->numbers()
                    ->symbols()
                    ->mixedCase(),
            ],
        ];
    }

    private function getEmailMessages(): array
    {
        return [
            'email.required' => UserMessage::emailIsRequired(),
            'email.email' => UserMessage::emailInvalid(),
        ];
    }

    private function getPasswordMessages(): array
    {
        return [
            'password.required' => UserMessage::passwordIsRequired(),
            'password.min' => UserMessage::passwordMinLength(),
            'password.numbers' => UserMessage::passwordNumbers(),
            'password.symbols' => UserMessage::passwordSymbols(),
            'password.mixedCase' => UserMessage::passwordMixedCase(),
        ];
    }

    public function messages(): array
    {
        return array_merge($this->getEmailMessages(), $this->getPasswordMessages());
    }
}
