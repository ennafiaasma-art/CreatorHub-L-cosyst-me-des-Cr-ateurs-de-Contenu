<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Workspace membership is checked in the controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
            'workspace_column_id' => ['nullable', 'integer', 'exists:workspace_columns,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'deadline' => ['nullable', 'date'],
            'attachment_url' => ['nullable', 'string', 'url'],
        ];
    }
}
