<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cie10DentalCatalog extends Model
{
    protected $table = 'cie10_dental_catalog';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'description',
    ];

    public function clinicalEvolutionNotes(): HasMany
    {
        return $this->hasMany(ClinicalEvolutionNote::class, 'diagnosis_cie10', 'code');
    }
}
