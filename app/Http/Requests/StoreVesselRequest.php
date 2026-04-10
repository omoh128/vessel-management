<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreVesselRequest
 *
 * Validates all vessel form inputs.
 * Used for both create (POST) and update (PUT/PATCH).
 */
class StoreVesselRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add Gate/Policy check here for production
    }

    public function rules(): array
    {
        return [
            // Basic
            'category'     => ['required', 'string', 'max:80'],
            'make'         => ['required', 'string', 'max:100'],
            'model'        => ['required', 'string', 'max:100'],
            'year_built'   => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'status'       => ['required', 'in:available,under_offer,sold'],
            'description'  => ['nullable', 'string', 'max:5000'],

            // Dimensions
            'loa_m'        => ['nullable', 'numeric', 'min:0', 'max:500'],
            'beam_m'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'draft_m'      => ['nullable', 'numeric', 'min:0', 'max:50'],

            // Engine
            'engine_make'     => ['nullable', 'string', 'max:100'],
            'engine_model'    => ['nullable', 'string', 'max:100'],
            'engine_power_hp' => ['nullable', 'integer', 'min:0'],
            'fuel_type'       => ['nullable', 'in:diesel,petrol,electric,hybrid'],

            // Price
            'price'    => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],

            // Location
            'country' => ['nullable', 'string', 'max:80'],
            'port'    => ['nullable', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'make.required'     => 'Please enter the vessel manufacturer.',
            'model.required'    => 'Please enter the vessel model.',
            'category.required' => 'Please select a vessel category.',
            'price.required'    => 'Please enter a price.',
            'currency.size'     => 'Currency must be a 3-letter code (e.g. EUR).',
        ];
    }
}