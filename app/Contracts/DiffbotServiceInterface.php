<?php

namespace App\Contracts;

interface DiffbotServiceInterface
{
    public function fetchWebsiteData(string $url);

    public function replaceImageUrlsInDiffbotData(array $data, array $imageUrls);
}