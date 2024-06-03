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
            'max' => 'Trường :attribute không được dài quá :max ký tự.',
            'unique' => 'Trường :attribute đã tồn tại.',
            'numeric' => 'Trường :attribute phải là số.',
            'date' => 'Trường :attribute phải là ngày hợp lệ.',
            'end_date.after' => 'Trường :attribute phải là ngày sau ngày bắt đầu.',
            'in' => ':attribute phải nằm trong :in',
            'vouchers.array' => 'Danh sách :attribute phải là một mảng.',
            'vouchers.*.code.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.code.string' => 'Trường :attribute phải là chuỗi ký tự.',
            'vouchers.*.code.max' => 'Trường :attribute không được dài quá :max ký tự.',
            'vouchers.*.code.unique' => 'Trường :attribute đã tồn tại.',
            'vouchers.*.pin.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.pin.string' => 'Trường :attribute phải là chuỗi ký tự.',
            'vouchers.*.pin.max' => 'Trường :attribute không được dài quá :max ký tự.',
            'vouchers.*.pin.unique' => 'Trường :attribute đã tồn tại.',
            'vouchers.*.type.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.type.string' => 'Trường :attribute phải là chuỗi ký tự.',
            'vouchers.*.type.max' => 'Trường :attribute không được dài quá :max ký tự.',
            'vouchers.*.value.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.value.numeric' => 'Trường :attribute phải là số.',
            'vouchers.*.value.max' => 'Trường :attribute không được vượt quá :max.',
            'vouchers.*.start_date.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.start_date.date' => 'Trường :attribute phải là ngày hợp lệ.',
            'vouchers.*.end_date.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.end_date.date' => 'Trường :attribute phải là ngày hợp lệ.',
            'vouchers.*.end_date.after' => 'Trường :attribute phải là ngày sau ngày bắt đầu.',
            'vouchers.*.status.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.status.in' => 'Trường :attribute phải nằm trong :in.',
            'vouchers.*.description.required' => 'Trường :attribute là bắt buộc.',
            'vouchers.*.description.string' => 'Trường :attribute phải là chuỗi ký tự.',
            'vouchers.*.description.max' => 'Trường :attribute không được dài quá :max ký tự.',
        ];
    }

    public function attributes()
    {
        return [
            'code' => 'Mã',
            'pin' => 'Mã pin',
            'type' => 'Loại',
            'value' => 'Giá trị',
            'start_date' => 'Ngày bắt đầu',
            'end_date' => 'Ngày kết thúc',
            'status' => 'Trạng thái',
            'description' => 'Mô tả',
            'vouchers' => 'voucher',
            'vouchers.*.code' => 'mã',
            'vouchers.*.pin' => 'mã pin',
            'vouchers.*.type' => 'loại',
            'vouchers.*.value' => 'giá trị',
            'vouchers.*.start_date' => 'ngày bắt đầu',
            'vouchers.*.end_date' => 'ngày kết thúc',
            'vouchers.*.status' => 'trạng thái',
            'vouchers.*.description' => 'mô tả',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false, null, Response::HTTP_BAD_REQUEST, $validator->errors());
        throw (new ValidationException($validator, $response));
    }
}
