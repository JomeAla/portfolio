<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'tags';
    
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];
    
    public $timestamps = false;

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_tags', 'tag_id', 'lead_id');
    }
}
