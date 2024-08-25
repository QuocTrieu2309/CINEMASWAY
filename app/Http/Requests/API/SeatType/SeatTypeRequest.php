<?php

namespace App\Http\Requests\API\SeatType;

use App\Models\SeatType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class SeatTypeRequest extends FormRequest
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
                                Rule::in([
                                    SeatType::SEAT_TYPE_C_2D,
                                    SeatType::SEAT_TYPE_N_2D,
                                    SeatType::SEAT_TYPE_V_2D,
                                    SeatType::SEAT_TYPE_C_3D,
                                    SeatType::SEAT_TYPE_V_3D,
                                    SeatType::SEAT_TYPE_N_3D,
                                    SeatType::SEAT_TYPE_C_4D,
                                    SeatType::SEAT_TYPE_V_4D,
                                    SeatType::SEAT_TYPE_N_4D,
                                ]),
                                Rule::unique('seat_types')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'price' => 'required|numeric|min:10000',
                            'promotion_price' => [
                                'required',
                                'numeric',
                                'min:0',
                                function ($attribute, $value, $fail) {
                                    if ($value < $this->input('price')) {
                                        $fail('Trường ' . $attribute . ' phải có giá trị lớn hơn giá gốc.');
                                    }
                                },
                            ],
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
                                Rule::in([
                                    SeatType::SEAT_TYPE_C_2D,
                                    SeatType::SEAT_TYPE_N_2D,
                                    SeatType::SEAT_TYPE_V_2D,
                                    SeatType::SEAT_TYPE_C_3D,
                                    SeatType::SEAT_TYPE_V_3D,
                                    SeatType::SEAT_TYPE_N_3D,
                                    SeatType::SEAT_TYPE_C_4D,
                                    SeatType::SEAT_TYPE_V_4D,
                                    SeatType::SEAT_TYPE_N_4D,
                                ]),
                                Rule::unique('seat_types')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                }),
                            ],
                            'price' => 'required|numeric|min:10000',
                            'promotion_price' => [
                                'required',
                                'numeric',
                                'min:0',
                                function ($attribute, $value, $fail) {
                                    if ($value < $this->input('price')) {
                                        $fail('Trường ' . $attribute . ' phải có giá trị lớn hơn giá gốc.');
                                    }
                                },
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
            'unique' => ':attribute đã tồn tại',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Loại ghế'
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errors);
        throw (new ValidationException($validator, $response));
    }
}
