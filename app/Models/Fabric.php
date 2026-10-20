<?php

namespace App\Models;

use Database\Factories\FabricFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fabric extends Model
{
    /** @use HasFactory<FabricFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];
}
