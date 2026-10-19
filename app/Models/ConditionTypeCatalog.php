<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConditionTypeCatalog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'category',
        'applies_to',
        'default_color',
        'icon',
    ];

    public function toothStates(): HasMany
    {
        return $this->hasMany(OdontogramToothState::class, 'condition_type_id');
    }

    public function treatmentEvents(): HasMany
    {
        return $this->hasMany(ToothTreatmentEvent::class, 'condition_type_id');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}