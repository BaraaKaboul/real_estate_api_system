<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PremiumRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'office_name'      => 'required|string|max:255',
            'office_location'  => 'required|string|max:255',
            //'phone'            => 'required|string|max:20', // ممكن تعمل regex لو بدك رقم سوري مثلاً
            'about'            => 'nullable|string|max:1000',

            'plan'             => 'required|in:standard,pro,golden',
            'duration'         => 'required|in:month,three month,year',

            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',

            'phone'            => 'required|regex:/^09[0-9]{8}$/'
        ];
    }

    public function messages(): array
    {
        return [
            'office_name.required' => 'Office name is required',
            'office_location.required' => 'Office location is required',
            'phone.required' => 'Phone number is required',
            'plan.in' => 'Plan subscribe is invalide',
            'duration.in' => 'Duration subscribe is invalide',
            'end_date.after_or_equal' => 'End date must be after or equal start date',
        ];
    }
}
