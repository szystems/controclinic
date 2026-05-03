<?php

namespace App\Models;

use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCatalog extends Model
{
    use BelongsToClinic, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'service_catalog';

    protected $keyType = 'string';

    public $incrementing = false;

    const TYPE_SERVICE = 'service';

    const TYPE_PRODUCT = 'product';

    protected $fillable = [
        'clinic_id',
        'name',
        'type',
        'sku',
        'description',
        'default_price',
        'tax_rate_override',
        'unit',
        'is_active',
        // forward-compat Level C
        'track_stock',
        'stock_quantity',
        'stock_alert_at',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'tax_rate_override' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'decimal:3',
        'stock_alert_at' => 'decimal:3',
    ];

    // ==================== RELATIONS ====================

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'catalog_item_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeServices($query)
    {
        return $query->where('type', self::TYPE_SERVICE);
    }

    public function scopeProducts($query)
    {
        return $query->where('type', self::TYPE_PRODUCT);
    }

    // ==================== ACCESSORS ====================

    public function getTypeLabelAttribute(): string
    {
        return __('catalog.'.$this->type);
    }

    public function getIsLowStockAttribute(): bool
    {
        if (! $this->track_stock || $this->stock_quantity === null) {
            return false;
        }

        return $this->stock_alert_at !== null && $this->stock_quantity <= $this->stock_alert_at;
    }
}
