<?php

namespace App\Http\Requests\API\Ticket;

use App\Models\Ticket;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
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
            case 'PUT':
                switch($currentMethod) {
                    case 'update':
                        $rules = [                      
                            'status' => [
                                'required',
                                Rule::in([
                                    Ticket::STATUS_AVAILABLE,
                                    Ticket::STATUS_HELD,
                                    Ticket::STATUS_RESERVED,
                                    Ticket::STATUS_SELECTED,
                                    Ticket::STATUS_PAID,                                                               
                                ])
                            ],
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
            'in' => ':attribute không hợp lệ',
        ];
    }

    public function attributes()
    {
        return [
            'status' => 'Trạng thái',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        $response = ApiResponse(false,null,Response::HTTP_BAD_REQUEST,$validator->errors());
        throw (new ValidationException($validator,$response));
    }
}
