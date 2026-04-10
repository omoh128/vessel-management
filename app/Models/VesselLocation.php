<?php

// ─── VesselLocation ───────────────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselLocation extends Model
{
    protected $fillable = ['vessel_id', 'country', 'region', 'port', 'latitude', 'longitude'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}