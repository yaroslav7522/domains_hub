<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain',
        'check_interval',
        'request_timeout',
        'check_method',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CheckHistory::class);
    }

    public function latestCheck(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CheckHistory::class)->latestOfMany();
    }
}
