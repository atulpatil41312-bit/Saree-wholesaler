<?php

namespace App\Models;

use Database\Factories\PlatformSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    /** @use HasFactory<PlatformSettingFactory> */
    use HasFactory;

    protected $fillable = ['platform_name', 'logo_path'];
}
