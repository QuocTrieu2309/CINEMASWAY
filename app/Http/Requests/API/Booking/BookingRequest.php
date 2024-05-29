<?php

namespace App\Http\Requests\API\Booking;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
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
                            'user_id' => [
                                'required',
                                'numeric',
                                Rule::exists('users', 'id'),
                            ],
                            'ticket_type_id' => [
                                'required',
                                'numeric',
                                Rule::exists('ticket_types', 'id')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'showtime_id' => [
                                'required',
                                'numeric',
                                Rule::exists('showtimes', 'id')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'quantity' => [
                                'required',
                                'numeric',
                                'min:1',
                                'max:60',
                            ],
                            'status' => [
                                'required',
                                'string',
                                'max:60',
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
            'format' => ":attribute không được để trống",
            'required' => ":attribute không được để trống",
            'string' => ":attribute phải là chữ",
            'exists' => ":attribute không tồn tại",
            'numeric' => ":attribute phải là một số",
            'min' => ":attribute tối thiểu là :min",
            'max' => ":attribute tối đa là :max kí tự ",
            'after_or_equal' => ":attribute phải từ ngày hôm nay trở đi",
        ];
    }

    public function attributes()
    {
        return [
            'user_id' => "Tài khoản",
            'ticket_type_id' => "Loại vé",
            'showtime_id' => "Xuất chiếu",
            'quantity' => "Số lượng",
            'subtotal' => "Tổng tiền",
            'status' => "Trạng thái",
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
