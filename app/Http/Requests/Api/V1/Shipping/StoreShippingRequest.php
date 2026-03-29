<?php

namespace Modules\Customer\Http\Requests\Api\V1\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRequest extends FormRequest
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
            'label' => ['required', 'string', 'in:Home,Office,Other'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'commune' => ['required', 'string', 'max:255'],
            'street_address' => ['required', 'string', 'max:500'],
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
            'label.required' => 'Please select an address type',
            'label.in' => 'Address type must be Home, Office, or Other',
            'recipient_name.required' => 'Recipient name is required',
            'phone_number.required' => 'Phone number is required',
            'province.required' => 'Province is required',
            'district.required' => 'District is required',
            'commune.required' => 'Commune is required',
            'street_address.required' => 'Street address is required',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ];
    }
}
