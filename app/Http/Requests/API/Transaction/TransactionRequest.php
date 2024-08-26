<?php

namespace App\Http\Requests\API\Transaction;

use App\Models\Transaction;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currentMethod = $this->route()->getActionMethod();
        $rules = [];
        switch ($this->method()) {
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'status' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::in([
                                    Transaction::STATUS_SUCCESS,
                                    Transaction::STATUS_FAIL,
                                ])
                            ],
                        ];
                        break;
                }
                break;
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'in' => ':attribute phải nằm trong :in',
            'string' => ":attribute phải là chữ",
            'max' => ":attribute tối đa là :max kí tự",
        ];
    }

    public function attributes()
    {
        return [
            'status' => 'Trạng thái',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errors);
        throw (new ValidationException($validator, $response));
    }
}
