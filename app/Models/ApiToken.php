<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    // Add this method to set default values
    protected static function booted()
    {
        static::creating(function ($token) {
            // Set default abilities if not set
            if (empty($token->abilities)) {
                $token->abilities = ['*'];
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createToken(User $user, string $name, array $abilities = ['*'], ?\DateTime $expiresAt = null): array
    {
        $plainTextToken = Str::random(64);

        $token = new static();
        $token->user_id = $user->id;
        $token->name = $name;
        $token->token = hash('sha256', $plainTextToken);
        $token->abilities = $abilities;
        $token->expires_at = $expiresAt;
        $token->save();

        // Store plain text token for one-time display
        $token->plain_text_token = $plainTextToken;

        return [
            'token' => $token,
            'plain_text_token' => $plainTextToken,
        ];
    }
}