<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    const METHOD_CASH = 'cash';

    const METHOD_CARD = 'card';

    const METHOD_TRANSFER = 'transfer';

    const METHOD_INSURANCE = 'insurance';

    const METHOD_OTHER = 'other';

    protected $fillable = [
        'invoice_id',
        'recorded_by',
        'amount',
        'currency',
        'method',
        'reference',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return __('invoices.payment_method_'.$this->method);
    }
}
