<?php
// ─── VesselEngine ─────────────────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselEngine extends Model
{
    protected $fillable = ['vessel_id', 'make', 'model', 'power_hp', 'hours', 'fuel_type', 'year'];

    protected $casts = [
        'power_hp' => 'integer',
        'hours'    => 'integer',
        'year'     => 'integer',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}