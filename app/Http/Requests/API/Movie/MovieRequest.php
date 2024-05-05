<?php

namespace App\Http\Requests\API\Movie;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Models\Movie;
use RuntimeException;

class MovieRequest extends FormRequest
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
                            'title' => [
                                'required',
                                Rule::unique('movies')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'genre' => 'required|string|max:60',
                            'director' => 'required|string|max:60',
                            'actor' => 'required|string|max:60',
                            'duration' => 'required|numeric|min:90',
                            'release_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d'),
                            'status' => [
                                'required',
                                Rule::in([
                                    Movie::STATUS_COMING,
                                    Movie::STATUS_CURRENTLY,
                                ])
                            ],
                            'rated' => [
                                'required',
                                Rule::in([
                                    Movie::RATED_P,
                                    Movie::RATED_C13,
                                    Movie::RATED_C16,
                                    Movie::RATED_C18,
                                ])
                            ],
                            'description' => 'string',
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
                                Rule::unique('movies')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                })
                            ],
                            'genre' => 'required|string|max:60',
                            'director' => 'required|string|max:60',
                            'actor' => 'required|string|max:60',
                            'duration' => 'required|numeric|min:90',
                            'release_date' => 'required|date|after_or_equal:' . \Carbon\Carbon::now()->format('Y-m-d'),
                            'status' => [
                                'required',
                                Rule::in([
                                    Movie::STATUS_COMING,
                                    Movie::STATUS_CURRENTLY,
                                ])
                            ],
                            'rated' => [
                                'required',
                                Rule::in([
                                    Movie::RATED_P,
                                    Movie::RATED_C13,
                                    Movie::RATED_C16,
                                    Movie::RATED_C18,
                                ])
                            ],
                            'description' => 'string',
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
            'date' => ":attribute phải là ngày-tháng-năm",
            'duration.min' => ":attribute tối thiểu là :min phút",
            'max' => ":attribute tối đa là :max kí tự",
            'after_or_equal' => ":attribute phải từ 14 tuổi trở lên",
            'unique' => ":attribute đã tồn tại",
            'in' => ':attribute phải nằm trong :in',
            'numeric' => ':attribute phải là số'
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'Tên Film',
            'genre' => 'Thể loại',
            'director' => 'Tác giả',
            'actor' => 'Diễn viên',
            'duration' => 'Thời lượng',
            'release_date' => 'Thời gian ra mắt',
            'status' => 'Trạng thái',
            'rated' => 'Điều kiện độ tuổi',
            'description' => 'Miêu tả',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
