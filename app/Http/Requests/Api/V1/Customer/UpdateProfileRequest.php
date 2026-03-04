<?php

namespace Modules\Customer\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->user()->id;

        return [
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $customerId,
            'phone' => 'sometimes|string|unique:customers,phone,' . $customerId,
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'avatar'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already in use.',
            'phone.unique' => 'This phone number is already in use.',
            'avatar.image' => 'Avatar must be an image.',
            'avatar.mimes' => 'Avatar must be a jpeg, png, jpg, gif, or webp file.',
            'avatar.max'   => 'Avatar size must not exceed 2MB.',
        ];
    }
}
