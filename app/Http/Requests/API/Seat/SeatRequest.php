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
                            'cinema_screens_id' => 'required|exists:cinema_screens,id',
                            'seat_type_id' => 'required|exists:seat_types,id',
                            'seat_number' => [
                                'required',
                                Rule::unique('seats')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],

                            'status' => [
                                'required',
                                Rule::in([
                                    Seat::STATUS_EMPTYSEAT,
                                    Seat::STATUS_BELINGHOLD,
                                    Seat::STATUS_SELECTED,
                                    Seat::STATUS_SOLD,
                                    Seat::STATUS_RESERVED,
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
                            'seat_number' => [
                                'required',
                                Rule::unique('seats')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                })
                            ],

                            'status' => [
                                'required',
                                Rule::in([
                                    Seat::STATUS_EMPTYSEAT,
                                    Seat::STATUS_BELINGHOLD,
                                    Seat::STATUS_SELECTED,
                                    Seat::STATUS_SOLD,
                                    Seat::STATUS_RESERVED,
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
            'in' => ':attribute phải nằm trong :in',
            'unique' => ':attribute đã tồn tại',


        ];
    }
    public function attributes()
    {
        return [
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
