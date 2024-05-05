<?php

namespace App\Http\Requests\API\TicketType;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TicketTypeRequest extends FormRequest
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
                            'seat_type_id' => [
                                'required',
                                'numeric',
                                Rule::exists('seat_types', 'id')->where(function ($query) {
                                    $query->where('deleted', 0);
                                }),
                            ],
                            'name' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::unique('ticket_types')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'price' => 'required|numeric|min:0',
                            'promotion_price' => 'required|numeric|min:0',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'seat_type_id' => [
                                'required',
                                'numeric',
                                Rule::exists('seat_types', 'id')->where(function ($query) {
                                    $query->where('deleted', 0);
                                }),
                            ],
                            'name' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::unique('ticket_types')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                })
                            ],
                            'price' => 'required|numeric|min:0',
                            'promotion_price' => 'required|numeric|min:0',
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
            'exists' => ":attribute không tồn tại",
            'unique' => ":attribute đã tồn tại",
            'numeric' => ":attribute phải là một số",
            'min' => ":attribute phải lớn hơn :min",
            'max' => ":attribute tối đa là :max kí tự ",

        ];
    }

    public function attributes()
    {
        return [
            'seat_type_id' => "Loại ghế",
            'name' => 'Tên loại vé',
            'price' => 'Giá loại vé',
            'promotion_price' => 'Giá vé khi áp dụng khuyến mãi',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
