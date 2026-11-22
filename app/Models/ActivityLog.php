<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'actor_email', 'action', 'details'];

    public static function record(string $action, ?string $details = null): void
    {
        try {
            self::create([
                'user_id' => auth()->id(),
                'actor_email' => auth()->user()?->email ?? 'system',
                'action' => $action,
                'details' => $details,
            ]);
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }
}
