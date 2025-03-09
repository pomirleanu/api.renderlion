<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDiffbotData;
use App\Models\VideoGenerationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiVideoGeneratorController extends Controller
{
    public function generateVideo(Request $request)
    {
        $type = $request->input('type');
        if ($type === 'prompt') {
            $jobId = $this->generateVideoFromPrompt($request);
        } else {
            $jobId = $this->generateVideoFromUrl($request);
        }
        return response()->json(['job' => $jobId]);
    }

    private function generateVideoFromPrompt(Request $request)
    {
        // Implement your video generation logic here
        // This is a placeholder implementation
        return 'https://example.com/generated-video.mp4';
    }

    private function generateVideoFromUrl(Request $request)
    {
        // Validate URL
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');

        // Create a unique workflow ID
        $workflowId = (string) Str::uuid();

        // Create a record in the database to track this job
        $jobRecord = VideoGenerationJob::create([
            'workflow_id' => $workflowId,
            'url' => $url,
            'status' => 'pending',
            'current_step' => 'diffbot',
            'metadata' => [
                'type' => 'url',
                'created_at' => now(),
            ],
        ]);

        // Dispatch the first job in the workflow (Diffbot)
        ProcessDiffbotData::dispatch($jobRecord);

        return $workflowId;
    }

    public function getJobStatus($workflowId)
    {
        $job = VideoGenerationJob::where('workflow_id', $workflowId)->firstOrFail();

        $response = [
            'workflow_id' => $job->workflow_id,
            'status' => $job->status,
            'current_step' => $job->current_step,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
        ];

        // Include the result URL if the job is completed
        if ($job->status === 'completed') {
            $response['result'] = $job->result;
        }

        return response()->json($response);
    }
}
