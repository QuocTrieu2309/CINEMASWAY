<?php

namespace App\Http\Requests\API\Voucher;

use App\Models\Voucher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
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
                            'code' => [
                                'required',
                                'string',
                                'max:60',
                                Rule::unique('vouchers')->where(function ($query) {
                                    return $query->where('deleted', 0);
                                })
                            ],
                            'type' => [
                                'required',
                                Rule::in([
                                    Voucher::TYPE_BIRTHDAY,
                                    Voucher::TYPE_MEMBER_NORMAL,
                                    Voucher::TYPE_MEMBER_VIP,
                                    Voucher::TYPE_MEMBER_PREMIUM,
                                ])
                            ],
                            'value' => 'required|numeric',
                            'start_date' => 'required|date_format:Y-m-d H:i:s',
                            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
                            'status' => [
                                'required',
                                Rule::in([
                                    Voucher::STATUS_ACTIVE,
                                    Voucher::STATUS_EXPIRED,
                                ])
                            ],
                            'description' => 'string|max:1000',
                        ];
                        break;
                }
                break;
            case 'PUT':
                switch ($currentMethod) {
                    case 'update':
                        $rules = [
                            'code' => [
                                'required',
                                'string',
                                'min:5',
                                'max:60',
                                Rule::unique('vouchers')->where(function ($query) {
                                    return $query->where('deleted', 0)->where('id', '!=', $this->id);
                                })
                            ],
                            'type' => [
                                'required',
                                Rule::in([
                                    Voucher::TYPE_BIRTHDAY,
                                    Voucher::TYPE_MEMBER_NORMAL,
                                    Voucher::TYPE_MEMBER_VIP,
                                    Voucher::TYPE_MEMBER_PREMIUM,
                                ])
                            ],
                            'value' => 'required|numeric',
                            'start_date' => 'required|date_format:Y-m-d H:i:s',
                            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
                            'status' => [
                                'required',
                                Rule::in([
                                    Voucher::STATUS_ACTIVE,
                                    Voucher::STATUS_EXPIRED,
                                ])
                            ],
                            'description' => 'string|max:1000',
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
            'required' => 'Trường :attribute là bắt buộc.',
            'string' => 'Trường :attribute phải là chuỗi ký tự.',
            'min' => 'Trường :attribute tối thiểu :min ký tự.',
            'max' => 'Trường :attribute không được dài quá :max ký tự.',
            'unique' => 'Trường :attribute đã tồn tại.',
            'numeric' => 'Trường :attribute phải là số.',
            'date' => 'Trường :attribute phải là ngày hợp lệ.',
            'end_date.after' => 'Trường :attribute phải là ngày sau ngày bắt đầu.',
            'in' => ':attribute phải nằm trong :in',
        ];
    }

    public function attributes()
    {
        return [
            'code' => 'Mã',
            'type' => 'Loại',
            'value' => 'Giá trị',
            'start_date' => 'Ngày bắt đầu',
            'end_date' => 'Ngày kết thúc',
            'status' => 'Trạng thái',
            'description' => 'Mô tả',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $errors);
        throw (new ValidationException($validator, $response));
    }
}
