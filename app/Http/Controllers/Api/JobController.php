<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\StoreJobRequest;
use App\Http\Requests\Job\UpdateJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::with('creator')
            ->latest()
            ->paginate(10);

        return JobResource::collection($jobs);
    }

    public function store(StoreJobRequest $request)
    {
        $job = Job::create([
            'creator_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'required_skills' => $request->required_skills,
            'deadline' => $request->deadline,
        ]);

        return response()->json([
            'message' => 'Job created successfully.',
            'job' => new JobResource($job->load('creator'))
        ],201);
    }

    public function show(Job $job)
    {
        return new JobResource(
            $job->load('creator')
        );
    }

    public function update(UpdateJobRequest $request, Job $job)
    {
        if($job->creator_id != auth()->id()){
            return response()->json([
                'message'=>'Unauthorized'
            ],403);
        }

        $job->update($request->validated());

        return response()->json([
            'message'=>'Job updated successfully.',
            'job'=>new JobResource($job->fresh()->load('creator'))
        ]);
    }

    public function destroy(Job $job)
    {
        if($job->creator_id != auth()->id()){
            return response()->json([
                'message'=>'Unauthorized'
            ],403);
        }

        $job->delete();

        return response()->json([
            'message'=>'Job deleted successfully.'
        ]);
    }
}
