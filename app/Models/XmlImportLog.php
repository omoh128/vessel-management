<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlImportLog extends Model
{
    protected $fillable = [
        'filename', 'source', 'total_records', 'inserted',
        'updated', 'skipped', 'failed', 'errors', 'status',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'errors'      => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];
}