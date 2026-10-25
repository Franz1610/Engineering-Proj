<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockNeed extends Model
{
    use HasCode;

    const CODE_PREFIX = 'SN';
    const CODE_START = 2000;

    protected $fillable = ['code', 'title', 'target_qty', 'budget_cap', 'tech_specs', 'status', 'created_by'];

    protected $casts = ['budget_cap' => 'decimal:2'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ProposalMeeting::class);
    }
}
