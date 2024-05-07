<?php

namespace App\Http\Requests\API\Ticket;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
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
        switch($this->method()) {
            case 'POST':
                switch($currentMethod) {
                    case 'store':
                        $rules = [
                            'booking_id' => 'required|exists:bookings,id',
                            'showtime_id' => 'required|exists:showtimes,id',
                            'seat_id' => 'required|exists:seats,id',
                            'code' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::unique('tickets')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'status' => 'required|string|max:60',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch($currentMethod) {
                    case 'update':
                        $rules = [
                            'booking_id' => 'required|exists:bookings,id',
                            'showtime_id' => 'required|exists:showtimes,id',
                            'seat_id' => 'required|exists:seats,id',
                            'code' => [
                                'required',
                                'string',
                                'max:60'
                            ],
                            'status' => 'required|string|max:60',
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
            'exists' => ":attribute không hợp lệ",
            'max' => ":attribute tối đa là :max kí tự",
            'string' => ":attribute phải là chữ",
            'unique' => ":attribute đã tồn tại"
        ];
    }

    public function attributes()
    {
        return [
            'booking_id' => 'Mã đặt trước',
            'showtime_id' => 'Thời gian chiếu',
            'seat_id' => 'Ghế',
            'code' => 'Mã vé',
            'status' => 'Trạng thái',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false,null,Response::HTTP_BAD_REQUEST,$validator->errors());
        throw (new ValidationException($validator,$response));
    }
}
