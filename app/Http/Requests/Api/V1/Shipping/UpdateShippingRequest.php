<?php

namespace Modules\Customer\Http\Requests\Api\V1\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRequest extends FormRequest
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
            'label' => ['sometimes', 'string', 'in:Home,Office,Other'],
            'recipient_name' => ['sometimes', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'string', 'max:20'],
            'province' => ['sometimes', 'string', 'max:255'],
            'district' => ['sometimes', 'string', 'max:255'],
            'commune' => ['sometimes', 'string', 'max:255'],
            'street_address' => ['sometimes', 'string', 'max:500'],
            'house_number' => ['nullable', 'string', 'max:50'],
            'floor' => ['nullable', 'string', 'max:50'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'label.in' => 'Address type must be Home, Office, or Other',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ];
    }
}
