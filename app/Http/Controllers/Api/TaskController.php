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

/**
 * @OA\Tag(name="Tasks", description="Kanban tasks inside a private workspace")
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="List tasks for a workspace (members only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="workspace_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of tasks"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
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
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Create a task inside a workspace (members only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"workspace_id","title"},
     *             @OA\Property(property="workspace_id", type="integer", example=1),
     *             @OA\Property(property="workspace_column_id", type="integer", example=2, description="Defaults to the first column (To Do) if omitted"),
     *             @OA\Property(property="title", type="string", example="Edit intro video"),
     *             @OA\Property(property="description", type="string", example="Trim first 10 seconds and add logo"),
     *             @OA\Property(property="assigned_user_id", type="integer", example=5),
     *             @OA\Property(property="priority", type="string", enum={"low","medium","high"}),
     *             @OA\Property(property="deadline", type="string", format="date", example="2026-08-01"),
     *             @OA\Property(property="attachment_url", type="string", example="https://drive.google.com/...")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Task created"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
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
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Update a task (members only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Task updated"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
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
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Delete a task (members only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Task deleted"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('view', $task->workspace);

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}/move",
     *     tags={"Tasks"},
     *     summary="Move a task to a different kanban column",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(required={"workspace_column_id"}, @OA\Property(property="workspace_column_id", type="integer", example=3))
     *     ),
     *     @OA\Response(response=200, description="Task moved"),
     *     @OA\Response(response=403, description="Not a member of this workspace"),
     *     @OA\Response(response=422, description="Column does not belong to this task's workspace")
     * )
     */
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
