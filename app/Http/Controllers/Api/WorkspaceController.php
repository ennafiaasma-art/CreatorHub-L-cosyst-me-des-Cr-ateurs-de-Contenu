<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\AddWorkspaceMemberRequest;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Workspace\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceMemberResource;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Workspaces", description="Private team workspaces with kanban boards")
 */
class WorkspaceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/workspaces",
     *     tags={"Workspaces"},
     *     summary="List workspaces the current user belongs to",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of workspaces")
     * )
     */
    public function index(Request $request)
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class WorkspaceController extends Controller
{
        use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        $workspaces = Workspace::query()
            ->whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['owner', 'columns'])
            ->latest()
            ->paginate(15);

        return WorkspaceResource::collection($workspaces);
    }

    /**
     * @OA\Post(
     *     path="/api/workspaces",
     *     tags={"Workspaces"},
     *     summary="Create a workspace (4 kanban columns are created automatically)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Client X - Video Project"),
     *             @OA\Property(property="description", type="string", example="All tasks for the client X campaign")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Workspace created")
     * )
     */
    public function store(StoreWorkspaceRequest $request)
     * Store a newly created resource in storage.
     */
      public function store(StoreWorkspaceRequest $request)
    {
        $workspace = Workspace::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Workspace created successfully.',
            'workspace' => new WorkspaceResource($workspace->load(['owner', 'columns', 'members.user'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/workspaces/{id}",
     *     tags={"Workspaces"},
     *     summary="Get a workspace (members only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Workspace details"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
     */
    public function show(Request $request, Workspace $workspace)
     * Display the specified resource.
     */
     public function show(Request $request, Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        return new WorkspaceResource($workspace->load(['owner', 'columns', 'members.user']));
    }

    /**
     * @OA\Put(
     *     path="/api/workspaces/{id}",
     *     tags={"Workspaces"},
     *     summary="Update a workspace (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Workspace updated"),
     *     @OA\Response(response=403, description="Only the owner can update the workspace")
     * )
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
     * Update the specified resource in storage.
     */
  public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $workspace->update($request->validated());

        return response()->json([
            'message' => 'Workspace updated successfully.',
            'workspace' => new WorkspaceResource($workspace->load(['owner', 'columns', 'members.user'])),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/workspaces/{id}",
     *     tags={"Workspaces"},
     *     summary="Delete a workspace (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Workspace deleted"),
     *     @OA\Response(response=403, description="Only the owner can delete the workspace")
     * )
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/workspaces/{id}/members",
     *     tags={"Workspaces"},
     *     summary="List workspace members",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of members"),
     *     @OA\Response(response=403, description="Not a member of this workspace")
     * )
     */
    public function members(Workspace $workspace)
      public function members(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        return WorkspaceMemberResource::collection(
            $workspace->members()->with('user')->get()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/workspaces/{id}/members",
     *     tags={"Workspaces"},
     *     summary="Add a member to the workspace (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(required={"user_id"}, @OA\Property(property="user_id", type="integer", example=3))
     *     ),
     *     @OA\Response(response=201, description="Member added"),
     *     @OA\Response(response=403, description="Only the owner can add members"),
     *     @OA\Response(response=422, description="User is already a member")
     * )
     */
    public function addMember(AddWorkspaceMemberRequest $request, Workspace $workspace)
      public function addMember(AddWorkspaceMemberRequest $request, Workspace $workspace)
    {
        $this->authorize('manageMembers', $workspace);

        if ($workspace->isMember((int) $request->user_id)) {
            return response()->json([
                'message' => 'This user is already a member of the workspace.',
            ], 422);
        }

        $member = $workspace->members()->create([
            'user_id' => $request->user_id,
            'role' => 'member',
        ]);

        return response()->json([
            'message' => 'Member added successfully.',
            'member' => new WorkspaceMemberResource($member->load('user')),
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/workspaces/{id}/members/{user}",
     *     tags={"Workspaces"},
     *     summary="Remove a member from the workspace (owner only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Member removed"),
     *     @OA\Response(response=403, description="Only the owner can remove members"),
     *     @OA\Response(response=422, description="Cannot remove the workspace owner")
     * )
     */
    public function removeMember(Workspace $workspace, int $user)
        public function removeMember(Workspace $workspace, int $user)
    {
        $this->authorize('manageMembers', $workspace);

        if ($workspace->owner_id === $user) {
            return response()->json([
                'message' => 'The workspace owner cannot be removed.',
            ], 422);
        }

        $workspace->members()->where('user_id', $user)->delete();

        return response()->json(null, 204);
    }

}
