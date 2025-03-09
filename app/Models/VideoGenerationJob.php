<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoGenerationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'url',
        'status',
        'current_step',
        'diffbot_data',
        'processed_data',
        'result',
        'metadata',
        'error',
    ];

    protected $casts = [
        'diffbot_data' => 'array',
        'processed_data' => 'array',
        'metadata' => 'array',
    ];
}