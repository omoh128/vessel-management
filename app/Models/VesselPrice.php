<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselPrice extends Model
{
    protected $fillable = ['vessel_id', 'amount', 'currency', 'vat_included', 'price_on_request'];

    protected $casts = [
        'amount'           => 'float',
        'vat_included'     => 'boolean',
        'price_on_request' => 'boolean',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}