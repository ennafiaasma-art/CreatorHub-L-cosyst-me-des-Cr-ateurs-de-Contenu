<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'bio' => $this->bio,
            'skills' => $this->skills ?? [],
            'hourly_rate' => $this->hourly_rate,
            'experience' => $this->experience,
            'country' => $this->country,
            'city' => $this->city,
            'social_links' => $this->social_links ?? [],
            'avatar' => $this->avatar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
