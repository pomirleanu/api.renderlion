<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessChatGptType implements ShouldQueue
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
    public function handle(ChatGpt $chatGpt): void
    {
        // Update job status
        $this->jobRecord->update([
            'status' => 'processing',
            'current_step' => 'chatgpt',
        ]);

        try {
            // Get the Diffbot data
            $diffbotData = $this->jobRecord->diffbot_data;

            if (empty($diffbotData)) {
                throw new \Exception('No Diffbot data available for processing');
            }

            // Get the assistant ID from config
            $assistantId = config('services.openai.assistant_id');

            // Process data with ChatGPT
            $processedData = $chatGpt->getStandardizedJson($diffbotData, $assistantId);

            // Store the processed data
            $this->jobRecord->update([
                'processed_data' => $processedData,
                'current_step' => 'video_generation',
            ]);

            // Dispatch the final job in the workflow
            GenerateVideo::dispatch($this->jobRecord);

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
        \Log::error('ChatGPT processing failed: ' . $exception->getMessage(), [
            'workflow_id' => $this->jobRecord->workflow_id,
            'exception' => $exception,
        ]);

        // Update the job record with the error
        $this->jobRecord->update([
            'status' => 'failed',
            'error' => 'ChatGPT processing failed: ' . $exception->getMessage(),
        ]);
    }
}
