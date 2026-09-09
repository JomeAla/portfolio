<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecureApiLicense extends Model
{
    protected $table = 'secure_api_licenses';

    protected $fillable = [
        'license_hash', 'key_suffix', 'type', 'plan', 'status', 'domain',
        'expires_at', 'max_activations', 'activations_count', 'meta',
    ];

    protected $casts = [
        'meta'        => 'array',
        'expires_at'  => 'datetime',
    ];

    public function agencyClients()
    {
        return $this->hasMany(SecureApiAgencyClient::class, 'parent_license_id');
    }
}
