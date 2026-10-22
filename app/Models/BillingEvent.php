<?php

namespace App\Models;

use Database\Factories\BillingEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingEvent extends Model
{
    /** @use HasFactory<BillingEventFactory> */
    use HasFactory;

    protected $fillable = ['provider', 'provider_event_id', 'event_type', 'status', 'payload', 'processed_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
