<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'workspace_column_id',
        'creator_id',
        'assigned_user_id',
        'title',
        'description',
        'priority',
        'deadline',
        'attachment_url',
        'is_validated',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'is_validated' => 'boolean',
        ];
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function column()
    {
        return $this->belongsTo(WorkspaceColumn::class, 'workspace_column_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
