<?php

namespace App\Models;

use Database\Factories\SareeTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SareeType extends Model
{
    /** @use HasFactory<SareeTypeFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];
}
