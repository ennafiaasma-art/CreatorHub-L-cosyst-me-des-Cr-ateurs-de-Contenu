<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id'=>$this->id,

            'title'=>$this->title,

            'description'=>$this->description,

            'budget'=>$this->budget,

            'required_skills'=>$this->required_skills,

            'deadline'=>$this->deadline,

            'creator'=>[
                'id'=>$this->creator->id,
                'name'=>$this->creator->name,
            ],

            'created_at'=>$this->created_at,

        ];
    }
}
