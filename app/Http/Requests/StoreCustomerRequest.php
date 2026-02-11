<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'outlet_id' => ['nullable', 'exists:outlets,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Customer name is required.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ];
    }
}
