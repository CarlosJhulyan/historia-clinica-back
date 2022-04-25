<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class EspecialidadIgnorada extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ESP_ING';
    protected $fillable = [
        'cod',
        'des'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'cod';
}
