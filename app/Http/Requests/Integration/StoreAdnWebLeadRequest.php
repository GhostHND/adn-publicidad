<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdnWebLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_number' => [
                'required',
                'string',
                'regex:/^SOL-WEB-\d{6,}$/',
                'max:40',
            ],

            'client_name' => [
                'required',
                'string',
                'max:150',
            ],

            'company' => [
                'nullable',
                'string',
                'max:180',
            ],

            'phone' => [
                'required',
                'string',
                'max:40',
            ],

            'whatsapp' => [
                'nullable',
                'string',
                'max:40',
            ],

            'email' => [
                'nullable',
                'email',
                'max:180',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'client_ip' => [
                'nullable',
                'ip',
            ],

            'client_user_agent' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'submitted_at' => [
                'nullable',
                'date',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:20',
            ],

            'items.*.product_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.app_reference_code' => [
                'nullable',
                'string',
                'max:180',
            ],

            'items.*.product_name' => [
                'required',
                'string',
                'max:180',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.0001',
            ],

            'items.*.unit' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.quote_mode' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.values' => [
                'nullable',
                'array',
                'max:100',
            ],

            'items.*.values.*.label' => [
                'required',
                'string',
                'max:180',
            ],

            'items.*.values.*.value' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items.*.values.*.unit' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.files' => [
                'nullable',
                'array',
                'max:50',
            ],

            'items.*.files.*.original_name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.files.*.mime_type' => [
                'nullable',
                'string',
                'max:150',
            ],

            'items.*.files.*.size' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}