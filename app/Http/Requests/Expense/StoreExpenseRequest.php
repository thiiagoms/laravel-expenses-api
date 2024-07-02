<?php

namespace App\Http\Requests\Expense;

use App\Enums\Expense\DescriptionEnum;
use App\Messages\Expense\ExpenseMessage;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreExpenseRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'max:'.DescriptionEnum::MAX_LENGTH->value,
                'string',
            ],
            'price' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'before:tomorrow',
            ],
        ];
    }

    private function getDescriptionMessages(): array
    {
        return [
            'description.required' => ExpenseMessage::descriptionIsRequired(),
            'description.max' => ExpenseMessage::descriptionMaxLength(),
            'description.string' => ExpenseMessage::descriptionType(),
        ];
    }

    private function getPriceMessages(): array
    {
        return [
            'price.required' => ExpenseMessage::priceIsRequired(),
            'price.numeric' => ExpenseMessage::priceType(),
            'price.gt' => ExpenseMessage::priceIsNotValid(),
        ];
    }

    private function getDateMessages(): array
    {
        return [
            'date.required' => ExpenseMessage::dateIsRequired(),
            'date.date_format' => ExpenseMessage::dateIsInvalid(),
            'date.before' => ExpenseMessage::dateIsInvalid(),
        ];
    }

    public function messages(): array
    {
        return array_merge(
            $this->getDescriptionMessages(),
            $this->getPriceMessages(),
            $this->getDateMessages()
        );
    }
}
