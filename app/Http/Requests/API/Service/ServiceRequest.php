<?php

namespace App\Http\Requests\Api\Service;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class ServiceRequest extends FormRequest
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
                                Rule::unique('services')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'price' => ['required', 'regex:/^\d{1,3}(,\d{3})*(\.\d{1,2})?$/'],
                            'quantity' => 'required|integer|min:1'

                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'name' => [
                                'required',
                                Rule::unique('services')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                })
                            ],
                            'price' => ['required', 'regex:/^\d{1,3}(,\d{3})*(\.\d{1,2})?$/'],
                            'quantity' => 'required|integer|min:1'

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
            'regex' => ':attribute định dạng kiểu tiền việt nam',
            'integer' => ':attribute là kiểu số',
            'unique' => ':attribute đã tồn tại',
            'min' => ':attribute phải > 1 ',
        ];
    }
    public function attributes()
    {
        return [
            'name' => 'Tên Dịch vụ',
            'price' => 'Giá',
            'quantity' => ' số lượng'

        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
