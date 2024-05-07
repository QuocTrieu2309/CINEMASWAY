<?php

namespace App\Http\Requests\API\Showtime;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ShowtimeRequest extends FormRequest
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
                            'movie_id' => [
                                'required',
                                'numeric',
                                Rule::exists('movies', 'id')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'cinema_screen_id' => [
                                'required',
                                'numeric',
                                Rule::exists('cinema_screens', 'id')->where(function ($query) {
                                    $query->where('deleted', 0);
                                }),
                            ],
                            'translation_id' => [
                                'required',
                                'numeric',
                                // Rule::exists('translations', 'id')->where(function ($query) {
                                //     $query->where('deleted', 0);
                                // }),
                            ],
                            'show_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d'),
                            'show_time' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                            'status' => 'required|string|max:60',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'movie_id' => [
                                'required',
                                'numeric',
                                Rule::exists('movies', 'id')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'cinema_screen_id' => [
                                'required',
                                'numeric',
                                Rule::exists('cinema_screens', 'id')->where(function ($query) {
                                    $query->where('deleted', 0);
                                }),
                            ],
                            'translation_id' => [
                                'required',
                                'numeric',
                                // Rule::exists('translations', 'id')->where(function ($query) {
                                //     $query->where('deleted', 0);
                                // }),
                            ],
                            'show_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d'),
                            'show_time' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
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
            'format' => ":attribute không được để trống",
            'required' => ":attribute không được để trống",
            'string' => ":attribute phải là chữ",
            'exists' => ":attribute không tồn tại",
            'numeric' => ":attribute phải là một số",
            'max' => ":attribute tối đa là :max kí tự ",
            'after_or_equal' => ":attribute phải từ ngày hôm nay trở đi",
        ];
    }

    public function attributes()
    {
        return [
            'movie_id' => "Phim",
            'cinema_screen_id' => "Trung gian rạp chiếu và màn chiếu",
            'translation_id' => "Loại ghế",
            'show_date' => "Ngày có phim",
            'show_time' => "Thời gian có phim",
            'status' => "Trạng thái",
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
