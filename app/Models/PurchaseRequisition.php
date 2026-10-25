<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PurchaseRequisition extends Model
{
    use HasCode;

    const CODE_PREFIX = 'PR';
    const CODE_START = 1000;

    protected $fillable = ['code', 'department_id', 'requested_by', 'item_title', 'specs', 'target_date', 'unit_price', 'quantity', 'total_cost', 'status', 'remarks'];

    protected $casts = [
        'target_date' => 'date:Y-m-d',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }
}
