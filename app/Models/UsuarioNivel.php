<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsuarioNivel extends Model
{

    protected $guarded = [];
    protected $table = 'usuario_nivel';
    protected $fillable = [
        'ID_USU_NVL',
        'LOGIN_USU',
        'SEC_USU_LOCAL',
        'ID_NIVEL',
        'ESTADO'

    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_USU_NVL';

    public function nivel(): HasOne
    {
        return $this->hasOne(Nivel::class, 'id_nivel', 'id_nivel');
    }
}