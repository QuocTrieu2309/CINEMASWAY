<?php

namespace App\Http\Requests\API\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserRequest extends FormRequest
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
        $action = $this->route()->getActionMethod();
        $rules = [];
        switch ($this->method()) {
            case 'POST':
                switch ($action) {
                    case 'store':
                        $rules = [
                            'role_id' => [
                                'required',
                                'numeric',
                                Rule::exists('roles', 'id')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                }),
                            ],
                            'full_name' => [
                                'required',
                                'string',
                                'max:60'
                            ],
                            'phone' => [
                                'required',
                                'string',
                                'min:10',
                                'max:60',
                            ],
                            'email' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::unique('users', 'email')
                            ],
                            'password' => [
                                'required',
                                'string',
                                'min:8',
                                'max:60'
                            ],
                            'gender' => [
                                'required',
                                'string'
                            ],
                            'birth_date' => [
                                'required',
                                'date'
                            ],
                            // 'avatar' => [],
                            'status' => [
                                'required',
                                'string',
                                'max:60'
                            ],
                        ];
                        break;
                }
            case 'PUT':
                switch ($action) {
                    case 'update':
                        $rules = [
                            'status' => [
                                'required',
                                'string',
                                'max:60'
                            ],
                        ];
                        break;
                }
                break;
                break;
        }


        return $rules;
    }

    public function messages()
    {
        return [
            'required' => ':attribute không được để trống',
            'string' => ':attribute phải là chuỗi',
            'unique' => ':attribute đã tồn tại',
            'numeric' => ':attribute phải là số',
            'min' => ':attribute phải nhiều hơn :min kí tự',
            'max' => ':attribute phải ít hơn :max kí tự',
            'exists' => ':attribute không tồn tại',
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => 'Quyền',
            'full_name' => 'Họ và tên',
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'gender' => 'Giới tính',
            'birth_date' => "Ngày sinh nhật",
            'status' => 'Trạng thái',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errors);
        throw (new ValidationException($validator, $response));
    }
}
