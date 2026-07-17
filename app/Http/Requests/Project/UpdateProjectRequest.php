<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership check happens in the controller (only the creator can update).
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_url' => ['sometimes', 'required', 'string', 'url'],
            'media_type' => ['sometimes', 'required', 'string', 'in:image,video,link'],
            'tags' => ['sometimes', 'required', 'array', 'min:1'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
