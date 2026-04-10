<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselDimension extends Model
{
    protected $fillable = ['vessel_id', 'loa_m', 'beam_m', 'draft_m', 'weight_kg', 'mast_height_m'];

    protected $casts = [
        'loa_m'         => 'float',
        'beam_m'        => 'float',
        'draft_m'       => 'float',
        'mast_height_m' => 'float',
        'weight_kg'     => 'integer',
    ];

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}