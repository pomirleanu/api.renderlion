<?php

namespace App\Contracts;

interface ChatGptServiceInterface
{
    public function getStandardizedJson($parsedJson, $assistantId);
}