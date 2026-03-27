<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'is_admin'];
    protected $hidden = ['password'];
    protected $casts = ['is_admin' => 'boolean', 'password' => 'hashed'];
}
