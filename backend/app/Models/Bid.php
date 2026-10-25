<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasCode;

    const CODE_PREFIX = 'BID';
    const CODE_START = 3000;

    protected $fillable = ['code', 'stock_need_id', 'supplier_id', 'unit_price', 'total_cost', 'lead_time_days', 'notes', 'status', 'submitted_at'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function stockNeed(): BelongsTo
    {
        return $this->belongsTo(StockNeed::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
