<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealEstateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', 'in:sale,rent,sell'],
            'property_type' => ['required', 'in:house,apartment,land,commercial,farm'],
            'title' => ['required', 'string', 'max:140'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'size:2'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:180'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:50'],
            'area_m2' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:6000'],
            'video_platform' => ['nullable', 'in:youtube,facebook'],
            'video_orientation' => ['nullable', 'in:landscape,portrait'],
            'video_embed_code' => ['nullable', 'string', 'max:12000'],
            'photos' => ['nullable', 'array', 'max:16'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'city' => is_string($this->city) ? trim($this->city) : $this->city,
            'state' => is_string($this->state) ? strtoupper(trim($this->state)) : $this->state,
            'neighborhood' => is_string($this->neighborhood) ? trim($this->neighborhood) : $this->neighborhood,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
        ]);
    }
}
