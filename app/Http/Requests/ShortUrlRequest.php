<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ShortUrlRequest extends FormRequest
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
            'original_url' => ['required', 'url']
        ];

        // Check if a user is present in the request (added by your VerifyToken middleware)
        if ($this->has('user') && $this->get('user')) {
            $rules['customPath'] = [
                'required',
                // 'unique:short_urls,custom_path',
                'min:8',  // Minimum 3 characters
                'max:50', // Maximum 50 characters
                'regex:/^[a-zA-Z0-9_-]+$/', // Only alphanumeric, underscore, and hyphen
                // function ($attribute, $value, $fail) {
                //     // Count words (split by space or hyphen)
                //     $wordCount = count(preg_split('/[\s-]/', $value));
                    
                //     if ($wordCount < 1) {
                //         $fail('Custom path must contain at least 1 word.');
                //     }
                    
                //     if ($wordCount > 3) {
                //         $fail('Custom path cannot exceed 3 words.');
                //     }
                // }
            ];
            $rules['title'] = ['nullable', 'string', 'min:0', 'max:30'];
            $rules['description'] = ['nullable', 'string', 'min:0', 'max:100'];
            $rules['expires_at'] = [
                'nullable', 
                'date', // Ensures it's a valid date
                'after:' . Carbon::now()->addMinutes(10), // Must be after current time + 10 minutes
                'date_format:Y-m-d H:i:s'
            ];

        }
        return $rules;
    }
}
