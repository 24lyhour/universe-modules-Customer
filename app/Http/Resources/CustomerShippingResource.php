<?php

namespace Modules\Customer\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerShippingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone_number' => $this->phone_number,
            'province' => $this->province,
            'district' => $this->district,
            'commune' => $this->commune,
            'street_address' => $this->street_address,
            'house_number' => $this->house_number,
            'floor' => $this->floor,
            'landmark' => $this->landmark,
            'note' => $this->note,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'is_default' => (bool) $this->is_default,
            'full_address' => $this->full_address,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
