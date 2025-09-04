<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    public static function getFakultas()
    {
        return self::select('id', 'name')->where('is_fakultas', true)->get();
    }

    public static function getTypes()
    {
        return self::select('type')->groupBy('type')->get();
    }
}
