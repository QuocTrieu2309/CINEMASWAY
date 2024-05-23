<?php

namespace App\Http\Requests\Api\BookingService;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;


class BookingServiceRequest extends FormRequest
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
                            'booking_id' => 'required|exists:bookings,id',
                            'service_id' => 'required|exists:services,id',
                            'subtotal' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
                            'quantity' => 'required|numeric|min:1'
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
            'exists' => ":attribute không tồn tại",
            'numeric' => ":attribute phải là một số",
            'regex' => ':attribute định dạng hợp lệ với tối đa 2 chữ số thập phân',
            'min' => ':attribute phải > 1 ',
        ];
    }
    public function attributes()
    {
        return [
            'booking_id' => "Đặt vé",
            'service_id' => "Dịch vụ",
            'subtotal' => "Tổng tiền",
            'quantity' => "Số lượng",
        ];
    }
    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
