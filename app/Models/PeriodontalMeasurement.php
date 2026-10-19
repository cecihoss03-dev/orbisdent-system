<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodontalMeasurement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'periodontal_chart_id',
        'tooth_fdi_code',
        'site', // mesial_buccal, mid_buccal, distal_buccal, mesial_lingual, mid_lingual, distal_lingual
        'probing_depth_mm',
        'recession_mm',
        'bleeding_on_probing',
        'plaque_present',
        'mobility_grade',
        'furcation_grade',
    ];

    protected $casts = [
        'bleeding_on_probing' => 'boolean',
        'plaque_present'      => 'boolean',
    ];

    public function periodontalChart(): BelongsTo
    {
        return $this->belongsTo(PeriodontalChart::class);
    }

    public function tooth(): BelongsTo
    {
        return $this->belongsTo(ToothDefinition::class, 'tooth_fdi_code', 'fdi_code');
    }
}
