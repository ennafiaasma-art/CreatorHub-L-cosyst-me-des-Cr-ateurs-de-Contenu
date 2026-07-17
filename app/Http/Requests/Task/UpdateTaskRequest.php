<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'deadline' => ['nullable', 'date'],
            'attachment_url' => ['nullable', 'string', 'url'],
            'is_validated' => ['sometimes', 'boolean'],
        ];
    }
}
