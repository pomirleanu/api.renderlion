<?php

namespace App\Services\Api;

use App\Contracts\DiffbotServiceInterface;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DiffbotService implements DiffbotServiceInterface
{
    protected Client $client;

    protected string $token;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->token = config('services.diffbot.token');
    }

    public function fetchWebsiteData(string $url): array
    {
        try {
            $response = $this->client->request('GET', 'https://api.diffbot.com/v3/analyze', [
                'query' => [
                    'token' => $this->token,
                    'url' => $url,
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                return $this->recursiveParse(json_decode($response->getBody(), true));
            }

            throw new Exception('Failed to fetch data from Diffbot API');
        } catch (RequestException $e) {
            throw new Exception('Request to Diffbot API failed: '.$e->getMessage());
        }
    }

    public function recursiveParse($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($key) && in_array(strtolower($key), [
                        'html', 'tags', 'diffboturi', 'breadcrumb', 'sentiment', 'naturalheight', 'naturalwidth',
                        'score',
                    ])) {
                    unset($data[$key]);
                } elseif (in_array(strtolower($key), ['posts', 'categories', 'images', 'items']) && is_array($value)) {
                    $data[$key] = array_slice($value, 0, 12);
                    // Also parse the sliced array to clean URLs
                    $data[$key] = array_map([$this, 'recursiveParse'], $data[$key]);
                } elseif (is_string($value) && $this->isImageUrl($value)) {
                    // $data[$key] = 'https://renderlion.com/'.uniqid().'.'.$this->getImageExtension($value);
                } elseif (is_string($value) && $this->isInvalidExtension($value)) {
                    unset($data[$key]);
                } elseif (is_string($key) && strtolower($key) === 'text' && is_string($value)) {
                    // Limit the "text" field to 255 characters
                    $data[$key] = substr($value, 0, 15000);
                } else {
                    $data[$key] = $this->recursiveParse($value);
                }
            }
        } elseif (is_string($data)) {
            if (filter_var($data, FILTER_VALIDATE_URL)) {
                $data = preg_replace('/\?.*/', '', $data);
            }
        }

        return $data;
    }

    private function isImageUrl($url)
    {
        $imageExtensions = ['png', 'jpg', 'jpeg', 'svg', 'webp'];
        $urlPath = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($urlPath, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $imageExtensions);
    }

    private function getImageExtension($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH);

        return pathinfo($urlPath, PATHINFO_EXTENSION);
    }

    private function isInvalidExtension($url)
    {
        $invalidExtensions = ['ico', 'avif', 'gif'];
        $urlPath = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($urlPath, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $invalidExtensions);
    }

    public function replaceImageUrlsInDiffbotData(array $data, array $replacements): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->replaceImageUrlsInDiffbotData($value, $replacements);
            } elseif (is_string($value)) {
                $cleanValue = $this->removeQueryParameters($value);
                foreach ($replacements as $replacement) {
                    $cleanOldUrl = $this->removeQueryParameters($replacement['old_url']);
                    if ($cleanValue === $cleanOldUrl) {
                        $value = $replacement['new_url'];
                    }
                }
            }
        }

        return $data;
    }

    private function removeQueryParameters($url)
    {
        return strtok($url, '?');
    }
}
