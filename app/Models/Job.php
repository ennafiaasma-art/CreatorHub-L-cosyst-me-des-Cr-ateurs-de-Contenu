<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'budget',
        'required_skills',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'deadline' => 'date',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
    
}
