<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasCode;

    const CODE_PREFIX = 'PAY';
    const CODE_START = 9000;

    protected $fillable = ['code', 'purchase_order_id', 'total_amount', 'delivery_verified', 'payment_amount', 'payment_method', 'payment_date', 'status', 'ref_number', 'remarks', 'processed_by'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'delivery_verified' => 'boolean',
        'payment_date' => 'date:Y-m-d',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
