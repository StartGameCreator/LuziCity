<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'vehicle_type' => ['required', 'in:car,motorcycle,nautical'],
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['required', 'integer', 'min:1950', 'max:'.((int) date('Y') + 1)],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'mileage' => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'fuel' => ['nullable', 'string', 'max:40'],
            'transmission' => ['nullable', 'string', 'max:40'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:5000'],
            'video_platform' => ['nullable', 'in:youtube,facebook'],
            'video_orientation' => ['nullable', 'in:landscape,portrait'],
            'video_embed_code' => ['nullable', 'string', 'max:12000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'brand' => is_string($this->brand) ? trim($this->brand) : $this->brand,
            'model' => is_string($this->model) ? trim($this->model) : $this->model,
            'city' => is_string($this->city) ? trim($this->city) : $this->city,
            'state' => is_string($this->state) ? strtoupper(trim($this->state)) : $this->state,
        ]);
    }
}
