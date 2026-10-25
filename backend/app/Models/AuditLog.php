<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'details'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Convenience helper: AuditLog::record('PR Approved', 'PR-1002 approved'). */
    public static function record(string $action, ?string $details = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
