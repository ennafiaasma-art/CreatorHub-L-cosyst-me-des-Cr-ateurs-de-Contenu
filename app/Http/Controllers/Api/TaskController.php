<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\MoveTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class TaskController extends Controller
{
            use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        $request->validate([
            'workspace_id' => ['required', 'integer', 'exists:workspaces,id'],
        ]);

        $workspace = Workspace::findOrFail($request->workspace_id);
        $this->authorize('view', $workspace);

        $tasks = $workspace->tasks()
            ->with(['column', 'creator', 'assignedUser'])
            ->latest()
            ->paginate(20);

        return TaskResource::collection($tasks);
    }


    /**
     * Store a newly created resource in storage.
     */
       public function store(StoreTaskRequest $request)
    {
        $workspace = Workspace::findOrFail($request->workspace_id);
        $this->authorize('view', $workspace);

        // Make sure the assigned user (if any) is actually a member of the workspace.
        if ($request->filled('assigned_user_id') && ! $workspace->isMember((int) $request->assigned_user_id)) {
            throw ValidationException::withMessages([
                'assigned_user_id' => 'The assigned user must be a member of this workspace.',
            ]);
        }

        $columnId = $request->workspace_column_id
            ?? $workspace->columns()->orderBy('position')->value('id');

        $task = $workspace->tasks()->create([
            'workspace_column_id' => $columnId,
            'creator_id' => $request->user()->id,
            'assigned_user_id' => $request->assigned_user_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->input('priority', 'medium'),
            'deadline' => $request->deadline,
            'attachment_url' => $request->attachment_url,
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => new TaskResource($task->load(['column', 'creator', 'assignedUser'])),
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('view', $task->workspace);

        if ($request->filled('assigned_user_id') && ! $task->workspace->isMember((int) $request->assigned_user_id)) {
            throw ValidationException::withMessages([
                'assigned_user_id' => 'The assigned user must be a member of this workspace.',
            ]);
        }

        // Only the workspace owner or the task creator can validate a delivery.
        if ($request->has('is_validated') && $request->user()->id !== $task->workspace->owner_id && $request->user()->id !== $task->creator_id) {
            return response()->json([
                'message' => 'Only the workspace owner or the task creator can validate this task.',
            ], 403);
        }

        $task->update($request->validated());

        return response()->json([
            'message' => 'Task updated successfully.',
            'task' => new TaskResource($task->load(['column', 'creator', 'assignedUser'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
     public function destroy(Task $task)
    {
        $this->authorize('view', $task->workspace);

        $task->delete();

        return response()->json(null, 204);
    }
      public function move(MoveTaskRequest $request, Task $task)
    {
        $this->authorize('view', $task->workspace);

        $columnBelongsToWorkspace = $task->workspace->columns()
            ->where('id', $request->workspace_column_id)
            ->exists();

        if (! $columnBelongsToWorkspace) {
            throw ValidationException::withMessages([
                'workspace_column_id' => 'This column does not belong to the task\'s workspace.',
            ]);
        }

        $task->update([
            'workspace_column_id' => $request->workspace_column_id,
        ]);

        return response()->json([
            'message' => 'Task moved successfully.',
            'task' => new TaskResource($task->load(['column', 'creator', 'assignedUser'])),
        ]);
    }
}
