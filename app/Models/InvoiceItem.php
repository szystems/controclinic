<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    const TYPE_CONSULTATION = 'consultation';

    const TYPE_PROCEDURE = 'procedure';

    const TYPE_MEDICATION = 'medication';

    const TYPE_LAB = 'lab';

    const TYPE_OTHER = 'other';

    protected $fillable = [
        'invoice_id',
        'catalog_item_id',
        'order',
        'type',
        'description',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'catalog_item_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return __('invoices.item_type_'.$this->type);
    }

    /**
     * Calcula el total de la línea:
     * ((unit_price × quantity) - discount) + tax
     */
    public function calculateTotal(): float
    {
        $base = (float) $this->unit_price * (float) $this->quantity;
        $net = $base - (float) $this->discount_amount;
        $tax = $net * ((float) $this->tax_rate / 100);

        return round($net + $tax, 2);
    }
}
