<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentistProfile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'license_number',
        'specialty',
        'signature_image_url',
        'bio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
