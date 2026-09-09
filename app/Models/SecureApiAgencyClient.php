<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecureApiAgencyClient extends Model
{
    protected $table = 'secure_api_agency_clients';

    protected $fillable = [
        'license_id', 'parent_license_id', 'email', 'status',
    ];

    public function license()
    {
        return $this->belongsTo(SecureApiLicense::class, 'license_id');
    }

    public function parent()
    {
        return $this->belongsTo(SecureApiLicense::class, 'parent_license_id');
    }
}
