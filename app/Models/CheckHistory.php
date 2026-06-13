<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckHistory extends Model
{
    protected $fillable = [
        'domain_id',
        'status',
        'http_code',
        'error',
        'response_time_ms',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
