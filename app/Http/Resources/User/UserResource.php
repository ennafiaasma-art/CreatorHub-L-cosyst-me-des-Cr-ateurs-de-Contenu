<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'projects_count' => $this->whenCounted('projects'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
