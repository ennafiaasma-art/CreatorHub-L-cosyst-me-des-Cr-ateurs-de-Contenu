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
     * Display the specified resource.
     */
     public function show(Request $request, Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        return new WorkspaceResource($workspace->load(['owner', 'columns', 'members.user']));
    }

    /**
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
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace)
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return response()->json(null, 204);
    }
      public function members(Workspace $workspace)
    {
        $this->authorize('view', $workspace);

        return WorkspaceMemberResource::collection(
            $workspace->members()->with('user')->get()
        );
    }
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
