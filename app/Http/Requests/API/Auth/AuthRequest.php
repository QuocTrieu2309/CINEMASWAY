<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Models\User;

class AuthRequest extends FormRequest
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
        switch ($this->getMethod()) {
            case 'POST':
                switch ($currentMethod) {
                    case 'register':
                        $rules = [
                            'role_id' => 'required',
                            'full_name' => 'required|string|regex:/^[\p{L}\s]+$/u|min:5',
                            'phone' => ['required', 'regex:/^0\d{9}$/'],
                            'email' => ['required', 'email', Rule::unique('users')],
                            'password' => 'required|string|min:8|max:10|confirmed',
                            'gender' => ['required', Rule::in([User::GENDER_MALE, User::GENDER_FEMALE])],
                            'birth_date' => 'required|date|before_or_equal:' . \Carbon\Carbon::now()->subYears(14)->format('Y-m-d'),
                        ];
                        break;
                    case 'login':
                        $rules = [
                            'email' => 'required|email',
                            'password' => 'required|string|min:8|max:10'
                        ];
                        break;
                    default:
                        return $rules;
                }
            default:
                return $rules;
        }
    }

    public function messages()
    {
        return [
            'required' => ":attribute không được để trống",
            'string' => ":attribute phải là chữ",
            'regex' => ":attribute không đúng định dạng",
            'date' => ":attribute phải là ngày-tháng-năm",
            'email' => ":attribute không đúng định dạng",
            'image' => ":attribute không đúng định dạng",
            'min' => ":attribute tối thiểu là :min kí tự",
            'max' => ":attribute tối đa là :max kí tự",
            'confirmed' => ":attribute phải trùng nhau",
            'mimes' => ":attribute phải có định dạng jpeg, png, jpg hoặc gif",
            'before_or_equal' => ":attribute phải từ 14 tuổi trở lên",
            'unique' => ":attribute đã tồn tại",
            'in' => ':attribute phải nằm trong :in',
        ];
    }

    public function attributes()
    {
        return [
            'role_id' => 'Vai trò',
            'full_name' => 'Họ và tên',
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'gender' => 'Giới tính',
            'birth_date' => 'Ngày sinh',
            'avatar' => 'Hình ảnh',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
