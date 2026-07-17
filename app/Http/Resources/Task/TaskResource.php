<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'deadline' => $this->deadline,
            'attachment_url' => $this->attachment_url,
            'is_validated' => $this->is_validated,
            'column' => new WorkspaceColumnResource($this->whenLoaded('column')),
            'status' => $this->whenLoaded('column', fn () => $this->column->name),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
