<?php

namespace App\Http\Requests\API\Seat;

use App\Models\Seat;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeatRequest extends FormRequest
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
                            'cinema_screen_id' => 'required|exists:cinema_screens,id',
                            'seat_type_id' => 'required|exists:seat_types,id',
                            'seat_number' => [
                                'required',
                                Rule::unique('seats')->where(function ($query) {
                                    return $query->where('deleted', 0)
                                    ->where('seat_type_id',$this->seat_type_id)
                                    ->where('cinema_screen_id',$this->cinema_screen_id);
                                }),
                                'regex:/^[A-Z]([0-9]|1[0-9]|20)$/'
                            ],

                            'status' => [
                                'required',
                                Rule::in([
                                    Seat::STATUS_OCCUPIED,
                                    Seat::STATUS_UNOCCUPIED,                                  
                                ])
                            ],

                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'cinema_screen_id' => 'required|exists:cinema_screens,id',
                            'seat_type_id' => 'required|exists:seat_types,id',
                            'seat_number' => [
                                'required',
                                Rule::unique('seats')->where(function ($query) {
                                    return $query->where('deleted', 0)
                                    ->where('seat_type_id',$this->seat_type_id)
                                    ->where('cinema_screen_id',$this->cinema_screen_id)
                                    ->where('id','!=',$this->id);
                                }),
                                'regex:/^[A-Z]([0-9]|1[0-9]|20)$/'
                            ],
                            'status' => [
                                'required',
                                Rule::in([
                                    Seat::STATUS_OCCUPIED,
                                    Seat::STATUS_UNOCCUPIED, 
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
            'required' => ":attribute không được để trống",
            'in' => ':attribute không hợp lệ',
            'unique' => ':attribute đã tồn tại',
            'exists' => ":attribute không tồn tại",
            'regex'=> ':attribute phải có định dạng bắt đầu bằng kí tự viết hoa kết hợp với 1 số từ 1 đến 20'
        ];
    }
    public function attributes()
    {
        return [
            'seat_type_id' => 'Loai Ghế',
            'cinema_screens_id' => 'Trung gian rạp chiếu và màn chiếu',
            'seat_number' => 'Số ghế',
            'status' => 'Trạng thái ghê',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
