<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OdontogramaHistorial extends Model
{
    protected $guarded = [];
    protected $table = 'HCW_ODONTOGRAMA_HISTORIAL';
    protected $fillable = [
        'ID_HISTORIAL',
        'COD_PACIENTE',
        'COD_GRUPO_CIA',
        'COD_MEDICO',
        'DETALLES',
        'FECHA',
        'OBSERVACIONES',
        'ESPECIFICACIONES'
    ];
    protected $casts = [];

    public $timestamps = false;
    protected $primaryKey = 'ID_HISTORIAL';

    public function datos(): HasMany
    {
        return $this->hasMany(DatosOdontograma::class, 'ID_HISTORIAL', 'ID_HISTORIAL');
    }
}
