<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Job\UpdateApplicationStatusRequest;
use App\Http\Resources\JobApplicationResource;
use App\Models\Job;
use App\Models\JobApplication;

class JobApplicationController extends Controller
{
    public function apply(Job $job)
    {
        $user = auth()->user();
        if($job->creator_id == auth()->id()){

    return response()->json([
        'message'=>'You cannot apply to your own job.'
    ],403);

}

        $exists = JobApplication::where('job_id',$job->id)
            ->where('user_id',$user->id)
            ->exists();

        if($exists){
            return response()->json([
                'message'=>'You already applied for this job.'
            ],422);
        }

        $application = JobApplication::create([
            'job_id'=>$job->id,
            'user_id'=>$user->id,
            'status'=>'pending'
        ]);

        return response()->json([
            'message'=>'Application submitted successfully.',
            'application'=>new JobApplicationResource(
                $application->load([
                    'user.profile',
                    'user.projects',
                    'job'
                ])
            )
        ],201);
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, JobApplication $application)
    {
        if($application->job->creator_id != auth()->id()){
            return response()->json([
                'message'=>'Unauthorized'
            ],403);
        }

        $application->update([
            'status'=>$request->status
        ]);

        return response()->json([
            'message'=>'Application updated successfully.',
         'application' => new JobApplicationResource(
    $application->load([
        'user.profile',
        'job'
    ])
)
        ]);
    }
}
