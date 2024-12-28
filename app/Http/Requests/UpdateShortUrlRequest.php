<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateShortUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic if needed
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'original_url'          =>  'required|url',
            'currentCustomPath'     =>  'required|min:8|max:50|regex:/^[a-zA-Z0-9_-]+$/',
            'customPath'            => 'required|min:8|max:50|regex:/^[a-zA-Z0-9_-]+$/',
            'title'                 => 'nullable|string|min:0|max:30',
            'description'           => 'nullable|string|min:0|max:100',
            'expires_at'            => 'nullable|date|after:' . Carbon::now()->addMinutes(10) .'|date_format:Y-m-d H:i:s'
        ];

        return $rules;
    }
}
