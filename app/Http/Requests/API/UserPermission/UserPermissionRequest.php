<?php

namespace App\Http\Requests\API\UserPermission;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UserPermissionRequest extends FormRequest
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
                            'user_id' => 'required|exists:users,id',
                            'permission_id' => 'required|exists:permissions,id',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch($currentMethod) {
                    case 'update':
                        $rules = [
                            'user_id' => 'required|exists:users,id',
                            'permission_id' => 'required|exists:permissions,id',
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
            'user_id' => 'Người dùng',
            'permission_id' => 'Quyền hạn'
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errors);
        throw (new ValidationException($validator, $response));
    }
}
