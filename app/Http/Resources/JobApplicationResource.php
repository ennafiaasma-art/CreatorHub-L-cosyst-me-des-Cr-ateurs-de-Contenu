<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id'=>$this->id,

            'status'=>$this->status,

            'applicant'=>[
                'id'=>$this->user->id,
                'name'=>$this->user->name,
                'profile'=>$this->user->profile,
                'latest_projects'=>$this->user
                    ->projects()
                    ->latest()
                    ->take(3)
                    ->get(),
            ],

            'created_at'=>$this->created_at,

        ];
    }
}
