<?php

namespace App\Http\Requests\API\CinemaScreen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class CinemaScreenRequest extends FormRequest
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
                            'cinema_id' => 'required|exists:cinemas,id',
                            'screen_id' => 'required|exists:screens,id',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch($currentMethod) {
                    case 'update':
                        $rules = [
                            'cinema_id' => 'required|exists:cinemas,id',
                            'screen_id' => 'required|exists:screens,id',
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
            'exists' => ':attribute không hợp lệ',
        ];
    }

    public function attributes()
    {
        return [
            'cinema_id' => 'Rạp',
            'screen_id' => 'Màn Hình'
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false,null,Response::HTTP_BAD_REQUEST,$validator->errors());
        throw (new ValidationException($validator,$response));
    }
}
