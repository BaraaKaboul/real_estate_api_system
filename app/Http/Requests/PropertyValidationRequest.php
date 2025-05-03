<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyValidationRequest extends FormRequest
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
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['house', 'apartment', 'land', 'commercial'])],
            'purpose' => ['required', Rule::in(['sale', 'rent'])],
            'phone' => 'required|string|max:20',
            'balconies' => 'integer|min:0',
            'bedrooms' => 'integer|min:0',
            'bathrooms' => 'integer|min:0',
            'livingRooms' => 'integer|min:0',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lon' => 'required|numeric|between:-180,180',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'required|string',
        ];

        // For update operations
        if ($this->isMethod('patch')) {
        $rules['id'] = 'required|exists:properties,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'type.in' => 'Property type must be one of: house, apartment, land, commercial',
            'purpose.in' => 'Purpose must be either sale or rent',
            'location_lat.between' => 'Latitude must be between -90 and 90 degrees',
            'images.*.max' => 'Each image must not be larger than 2MB',
        ];
    }
}
