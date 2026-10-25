<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalMeeting extends Model
{
    use HasCode;

    const CODE_PREFIX = 'MTG';
    const CODE_START = 4000;

    protected $fillable = ['code', 'stock_need_id', 'bid_id', 'supplier_id', 'date', 'time', 'venue', 'status', 'notes'];

    protected $casts = ['date' => 'date:Y-m-d'];

    public function stockNeed(): BelongsTo
    {
        return $this->belongsTo(StockNeed::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
