<?php

namespace App\Jobs;

use App\Contracts\DiffbotServiceInterface;
use App\Models\VideoGenerationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessDiffbotData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected VideoGenerationJob $jobRecord;

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
    public function handle(DiffbotServiceInterface $diffbotService): void
    {
        // Update job status
        $this->jobRecord->update([
            'status' => 'processing',
            'current_step' => 'diffbot',
        ]);

        try {
            // Fetch data from Diffbot
            $diffbotData = $diffbotService->fetchWebsiteData($this->jobRecord->url);

            // Store the data in the job record
            $this->jobRecord->update([
                'diffbot_data' => $diffbotData,
                'current_step' => 'chatgpt',
            ]);

            // Dispatch the next job in the workflow
//            ProcessChatGptType::dispatch($this->jobRecord);
        } catch (Throwable $e) {
            \Log::error($e->getMessage());

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
        \Log::error('Diffbot processing failed: ' . $exception->getMessage(), [
            'workflow_id' => $this->jobRecord->workflow_id,
            'exception' => $exception,
        ]);

        // Update the job record with the error
        $this->jobRecord->update([
            'status' => 'failed',
            'error' => 'Diffbot processing failed: ' . $exception->getMessage(),
        ]);
    }
}