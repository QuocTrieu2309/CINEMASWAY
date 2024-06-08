<?php

namespace App\Http\Requests\API\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;

class ClientRequest extends FormRequest
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
                    case 'createBooking':
                        $rules = [
                            'showtime_id' => 'required|exists:showtimes,id',
                            'quantity' => 'required|integer|min:1|max:8',
                            'subtotal'=> 'required|numeric|min:50000|max:2000000' 
                        ];
                        break;
                        case 'createBooingService':
                            $rules = [
                                'booking_id' => 'required|exists:bookings,id',
                                'services' => 'required',
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
            'min' =>":attribute có giá trị tối thiểu là:min",
            'max' =>":attribute có giá trị tối đa là:max",
        ];
    }
    public function attributes()
    {
        return [
            'showtime_id' => 'Suất chiếu',
            'quantity' => 'Số lượng',
            'subtotal' => 'Số tiền',
            'booking_id' => 'Booking',
            'services'=> 'Dịch vụ'
        ];
    }
    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
