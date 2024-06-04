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
        return [
            'showtime_id' => 'required|exists:showtimes,id',
            'seats' => 'required|array',
            'seats.*' => 'required|exists:seats,id',
            'services' => 'sometimes|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute là bắt buộc.',
            'exists' => ':attribute không tồn tại.',
            'array' => ':attribute không hợp lệ.',
            'integer' => ':attribute phải là số nguyên.',
            'min' => ':attribute ít nhất là :min.',
        ];
    }

    public function attributes()
    {
        return [
            'showtime_id' => 'suất chiếu',
            'seats' => 'danh sách ghế',
            'seats.*' => 'ghế',
            'services' => 'danh sách dịch vụ',
            'services.*.service_id' => 'dịch vụ',
            'services.*.quantity' => 'số lượng dịch vụ',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
