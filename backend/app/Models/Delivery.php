<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasCode;

    const CODE_PREFIX = 'DEL';
    const CODE_START = 8000;

    protected $fillable = ['code', 'purchase_order_id', 'ordered_qty', 'delivered_qty', 'expected_date', 'actual_date', 'delivery_status', 'compliance_status', 'remarks', 'recorded_by'];

    protected $casts = [
        'expected_date' => 'date:Y-m-d',
        'actual_date' => 'date:Y-m-d',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
