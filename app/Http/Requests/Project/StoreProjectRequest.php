<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any logged in user can post a project (enforced by auth:sanctum middleware).
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_url' => ['required', 'string', 'url'],
            'media_type' => ['required', 'string', 'in:image,video,link'],
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'media_url.required' => 'At least one media (media_url) is required.',
            'tags.required' => 'At least one tag is required.',
        ];
    }
}
