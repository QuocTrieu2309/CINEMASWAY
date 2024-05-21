<?php

namespace App\Http\Requests\API\SeatMap;

use App\Models\Seat;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeatMapRequest extends FormRequest
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
                            'cinema_screen_id' => [
                                'required', 'exists:cinema_screens,id',
                                Rule::unique('seat_maps')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'total_row' => 'required|integer|min:4|max:12',
                            'total_column' => 'required|integer|min:4|max:12',
                            'layout' => ['required', 'string', 
                            'regex:/^(?:[NVCX]+\|?)*[NVCX]+$/'
                        ],
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'cinema_screen_id' => [
                                'required', 'exists:cinema_screens,id',
                                // Rule::unique('seat_maps')->where(function ($query) {
                                //     return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                // })
                            ],
                            'total_row' => 'required|integer|min:4|max:12',
                            'total_column' => 'required|integer|min:4|max:12',
                            'layout' => ['required', 'string', 'regex:/^(?:[NVCX]+\|?)*[NVCX]+$/'],
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
            'integer' => ":attribute phải là số nguyên",
            'in' => ':attribute phải nằm trong :in',
            'unique' => ':attribute đã tồn tại',
            'min' => ':attribute phải lớn hơn hoặc bằng :min',
            'max' => ':attribute phải nhỏ hơn hoặc bằng :max ',
            'layout.min' =>':attribute có độ dài kí tự ít nhất là :min kí tự',
            'exists' => ':attribute không tồn tại trong bảng quan hệ ',
            'regex' => ':attribute không đúng định dạng',
            'string' => ':attribute phải là chuỗi'
        ];
    }
    public function attributes()
    {
        return [
            'cinema_screens_id' => 'Màn ảnh rạp chiếu',
            'total_row' => 'Số hàng ghế',
            'total_column' => 'Số dãy ghế',
            'layout' => 'Sơ đồ ghế',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
