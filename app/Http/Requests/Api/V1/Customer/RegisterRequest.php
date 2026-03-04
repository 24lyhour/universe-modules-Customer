<?php

namespace Modules\Customer\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:customers,email',
            'phone' => 'required_without:email|string|unique:customers,phone',
            'password'  => 'required|string|min:6|confirmed',
            'gender'    => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'address'   => 'nullable|string|max:500',
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required_without' => 'Email is required when phone is not provided.',
            'email.unique'  => 'This email is already registered.',
            'phone.required_without' => 'Phone is required when email is not provided.',
            'phone.unique'  => 'This phone number is already registered.',
            'password.required' => 'Password is required.',
            'password.min'  => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'avatar.image'  => 'Avatar must be an image.',
            'avatar.mimes'  => 'Avatar must be a jpeg, png, jpg, gif, or webp file.',
            'avatar.max'    => 'Avatar size must not exceed 2MB.',
        ];
    }
}
