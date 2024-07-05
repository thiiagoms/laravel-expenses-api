<?php

namespace App\Http\Requests\User;

use App\Enums\Auth\PasswordEnum;
use App\Enums\User\NameEnum;
use App\Messages\User\UserMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('api')->check();
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

    private function patch(): array
    {
        return [
            'name' => [
                'sometimes',
                'min:'.NameEnum::MIN_LENGTH->value,
                'max:'.NameEnum::MAX_LENGTH->value,
                'string',
            ],
            'email' => [
                'sometimes',
                'email:rfc,dns',
                'unique:users,email',
                'string',
            ],
            'password' => [
                'sometimes',
                Password::min(PasswordEnum::MIN_LENGTH->value)
                    ->numbers()
                    ->symbols()
                    ->mixedCase(),
            ],
        ];
    }

    private function put(): array
    {
        return [
            'name' => [
                'required',
                'min:'.NameEnum::MIN_LENGTH->value,
                'max:'.NameEnum::MAX_LENGTH->value,
                'string',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                'string',
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->method()) {
            'PATCH' => $this->patch(),
            default => $this->put()
        };
    }

    private function getNameMessages(): array
    {
        return [
            'name.required' => UserMessage::nameIsRequired(),
            'name.min' => UserMessage::nameMinLength(),
            'name.max' => UserMessage::nameMaxLength(),
            'name.string' => UserMessage::nameType(),
        ];
    }

    private function getEmailMessages(): array
    {
        return [
            'email.required' => UserMessage::emailIsRequired(),
            'email.email' => UserMessage::emailInvalid(),
            'email.unique' => UserMessage::emailAlreadyExists(),
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
        return array_merge(
            $this->getNameMessages(),
            $this->getEmailMessages(),
            $this->getPasswordMessages(),
        );
    }
}
