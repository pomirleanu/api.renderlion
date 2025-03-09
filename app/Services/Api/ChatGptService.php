<?php

namespace App\Services\Api;

use App\Contracts\ChatGptServiceInterface;
use OpenAI\Laravel\Facades\OpenAI;

class ChatGptService implements ChatGptServiceInterface
{
    public function getStandardizedJson($parsedJson, $assistantId)
    {
        $messages = [
            [
                'role' => 'user',
                'content' => 'Please use the following JSON data: '.json_encode($parsedJson),
            ],
        ];

        // Create and run the thread
        $response = OpenAI::threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' => $messages,
            ],
        ]);

        // Get the thread ID from the response
        $threadId = $response->threadId;

        // Fetch the messages from the thread until we find an assistant response
        while (true) {
            $allThreadMessages = OpenAI::threads()->messages()->list($threadId, [
                'limit' => 100,
            ]);

            foreach ($allThreadMessages['data'] as $threadMessage) {
                if ($threadMessage['role'] === 'assistant' && ! empty($threadMessage['content'])) {
                    $message = $threadMessage['content'][0]['text']['value'];
                    break 2; // Break out of both loops
                }
            }

            // Sleep for a short duration before checking again
            sleep(2); // Wait for 2 seconds before the next check
        }

        return json_decode($message, true);
    }
}