<?php

namespace App\Http\Requests\API\Translation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TranslationRequest extends FormRequest
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
            case 'POST':
                switch ($currentMethod) {
                    case 'store':
                            $rules = [
                                'name' => [
                                    'required',
                                    Rule::unique('translations')->where(function ($query) {
                                        return $query->where('deleted', 0);
                                    })
                                ],

                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules =[
                            'name' => [
                                'required',
                                Rule::unique('translations')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
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
            'required' => ":attribute không được để trống",
            'string' => ":attribute phải là chữ",
            'unique' => ':attribute đã tồn tại',

        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null,Response ::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
