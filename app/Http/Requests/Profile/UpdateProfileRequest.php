<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'experience' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['string', 'url'],
            'avatar' => ['nullable', 'string', 'url'],
        ];
    }
}
