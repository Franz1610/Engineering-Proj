<?php

namespace App\Models\Concerns;

/**
 * Auto-generates a readable, sequential `code` (e.g. PR-1001) on create.
 * Models define: const CODE_PREFIX = 'PR'; const CODE_START = 1000;
 */
trait HasCode
{
    protected static function bootHasCode(): void
    {
        static::creating(function ($model) {
            if (! empty($model->code)) {
                return;
            }

            $prefix = static::CODE_PREFIX;
            // The unique index on `code` guards against duplicates under concurrency.
            $last = static::query()
                ->withoutGlobalScopes()
                ->where('code', 'like', $prefix.'-%')
                ->selectRaw("MAX(CAST(SUBSTRING(code FROM '[0-9]+$') AS INTEGER)) AS n")
                ->value('n');

            $model->code = $prefix.'-'.(($last ?: static::CODE_START) + 1);
        });
    }
}
