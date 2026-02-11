<?php

namespace Modules\Customer\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
            'type' => ['sometimes', 'string', 'in:registration,login,password_reset,phone_verification'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'code.required' => 'OTP code is required.',
            'code.size' => 'OTP code must be 6 digits.',
            'type.in' => 'Invalid OTP type.',
        ];
    }
}
