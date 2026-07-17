<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
         $query = Project::query()
            ->with('creator')
            ->withCount(['likes', 'bookmarks']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
    }
      if ($request->filled('tags')) {
            $tags = explode(',', $request->string('tags'));
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', trim($tag));
                }
            });
              $projects = $query->latest()->paginate(15);

        return ProjectResource::collection($projects);
    }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
         $project = $request->user()->projects()->create($request->validated());

        return response()->json([
            'message' => 'Project created successfully.',
            'project' => new ProjectResource($project->load('creator')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project
    )
    {
        //
         $project->load('creator')->loadCount(['likes', 'bookmarks']);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
         if ($request->user()->id !== $project->creator_id) {
            return response()->json([
                'message' => 'You can only update your own projects.',
            ], 403);
        }

        $project->update($request->validated());

        return response()->json([
            'message' => 'Project updated successfully.',
            'project' => new ProjectResource($project->load('creator')),
        ]);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project)
    {
        if ($request->user()->id !== $project->creator_id) {
            return response()->json([
                'message' => 'You can only delete your own projects.',
            ], 403);
        }

        $project->delete();

        return response()->json(null, 204);
        //
    }

   public function like(Request $request, Project $project)
    {
        $like = $project->likes()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => $like->wasRecentlyCreated ? 'Project liked.' : 'Project already liked.',
        ], $like->wasRecentlyCreated ? 201 : 200);
    }
     public function unlike(Request $request, Project $project)
    {
        $project->likes()->where('user_id', $request->user()->id)->delete();

        return response()->json(null, 204);
    }
      public function bookmark(Request $request, Project $project)
    {
        $bookmark = $project->bookmarks()->firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => $bookmark->wasRecentlyCreated ? 'Project bookmarked.' : 'Project already bookmarked.',
        ], $bookmark->wasRecentlyCreated ? 201 : 200);
    }

      public function unbookmark(Request $request, Project $project)
    {
        $project->bookmarks()->where('user_id', $request->user()->id)->delete();

        return response()->json(null, 204);
    }
      public function myBookmarks(Request $request)
    {
        $projects = Project::query()
            ->whereHas('bookmarks', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with('creator')
            ->withCount(['likes', 'bookmarks'])
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }
}
