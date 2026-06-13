<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SlugGenerator;

class Sequence extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    
    protected $fillable = [
        'name',
        'description', 
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function steps(): HasMany
    {
        return $this->hasMany(SequenceStep::class, 'sequence_id')->orderBy('step_order');
    }
    
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'sequence_id');
    }
}