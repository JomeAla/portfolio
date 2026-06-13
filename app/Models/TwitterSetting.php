<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwitterSetting extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'client_id',
        'client_secret',
        'oauth_token',
        'oauth_token_secret',
    ];

    protected $casts = [
        'expires_at' => 'integer',
    ];
}