<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    /**
     * Default kanban columns every new workspace gets automatically.
     */
    public const DEFAULT_COLUMNS = ['To Do', 'In Progress', 'Review', 'Done'];

    protected static function booted(): void
    {
        static::created(function (Workspace $workspace) {
            // Add the owner as a member of their own workspace.
            $workspace->members()->create([
                'user_id' => $workspace->owner_id,
                'role' => 'owner',
            ]);

            // Create the 4 default kanban columns.
            foreach (self::DEFAULT_COLUMNS as $index => $name) {
                $workspace->columns()->create([
                    'name' => $name,
                    'position' => $index,
                ]);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function columns()
    {
        return $this->hasMany(WorkspaceColumn::class)->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Simple, beginner-friendly authorization helper: is this user allowed
     * inside this private workspace?
     */
    public function isMember(int $userId): bool
    {
        return $this->owner_id === $userId
            || $this->members()->where('user_id', $userId)->exists();
    }
}
