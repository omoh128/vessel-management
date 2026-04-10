<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vessel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'external_id',
        'source',
        'name',
        'category',
        'make',
        'model',
        'year_built',
        'status',
        'description',
    ];

    protected $casts = [
        'year_built' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function dimensions(): HasOne
    {
        return $this->hasOne(VesselDimension::class);
    }

    public function engine(): HasOne
    {
        return $this->hasOne(VesselEngine::class);
    }

    public function price(): HasOne
    {
        return $this->hasOne(VesselPrice::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(VesselLocation::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFromXml($query)
    {
        return $query->where('source', 'xml');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price) {
            return 'Price on request';
        }

        return number_format($this->price->amount, 0, '.', ',')
            . ' ' . $this->price->currency;
    }
}