<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'name',
        'contact',
        'phone',
        'email',
    ];

    public function supplyBatches(): HasMany
    {
        return $this->hasMany(SupplyBatch::class);
    }
}
