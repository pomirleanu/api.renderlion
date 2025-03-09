<?php

namespace App\Jobs;

use App\Models\VideoGenerationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jobRecord;

    /**
     * Create a new job instance.
     */
    public function __construct(VideoGenerationJob $jobRecord)
    {
        $this->jobRecord = $jobRecord;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update job status
        $this->jobRecord->update([
            'status' => 'processing',
            'current_step' => 'video_generation',
        ]);

        try {
            // Get the processed data
            $processedData = $this->jobRecord->processed_data;

            if (empty($processedData)) {
                throw new \Exception('No processed data available for video generation');
            }

            // TODO: Implement actual video generation logic here
            // This is a placeholder that simulates video generation
            sleep(5); // Simulate processing time

            // Generate a fake video URL (replace with actual implementation)
            $videoUrl = 'https://example.com/videos/' . $this->jobRecord->workflow_id . '.mp4';

            // Mark the job as completed
            $this->jobRecord->update([
                'status' => 'completed',
                'current_step' => 'completed',
                'result' => $videoUrl,
                'metadata' => array_merge($this->jobRecord->metadata ?? [], [
                    'completed_at' => now(),
                    'processing_time' => now()->diffInSeconds($this->jobRecord->created_at),
                ]),
            ]);

        } catch (Throwable $e) {
            $this->handleFailure($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        $this->handleFailure($exception);
    }

    private function handleFailure(Throwable $exception): void
    {
        // Log the error
        \Log::error('Video generation failed: ' . $exception->getMessage(), [
            'workflow_id' => $this->jobRecord->workflow_id,
            'exception' => $exception,
        ]);

        // Update the job record with the error
        $this->jobRecord->update([
            'status' => 'failed',
            'error' => 'Video generation failed: ' . $exception->getMessage(),
        ]);
    }
}