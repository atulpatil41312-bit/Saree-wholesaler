<?php

namespace App\Models;

use Database\Factories\GeneratedTemplateDraftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedTemplateDraft extends Model
{
    /** @use HasFactory<GeneratedTemplateDraftFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stitch_project_id',
        'screen_id',
        'title',
        'prompt',
        'device_type',
        'status',
        'html_path',
        'image_path',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
