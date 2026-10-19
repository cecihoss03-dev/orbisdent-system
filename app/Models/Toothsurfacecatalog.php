<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToothSurfaceCatalog extends Model
{
    protected $table = 'tooth_surfaces_catalog';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    public function toothStates(): HasMany
    {
        return $this->hasMany(OdontogramToothState::class, 'surface_code', 'code');
    }

    public function treatmentEvents(): HasMany
    {
        return $this->hasMany(ToothTreatmentEvent::class, 'surface_code', 'code');
    }
}