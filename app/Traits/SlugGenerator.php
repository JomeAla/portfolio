<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait SlugGenerator
{
    public static function generateUniqueSlug($model, ?string $field = null): string
    {
        $field = $field ?? static::getSlugSourceField();
        
        if (!$field || !isset($model->$field)) {
            return Str::random(10);
        }

        $slug = Str::slug($model->$field);
        $originalSlug = $slug;
        $counter = 1;

        $modelClass = get_called_class();
        $query = $modelClass::where('id', '!=', $model->id ?? 0);

        while ($query->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function getSlugSourceField(): ?string
    {
        if (isset(static::$slugSourceField)) {
            return static::$slugSourceField;
        }

        if (isset(static::$slugFrom)) {
            return static::$slugFrom;
        }

        if (property_exists(static::class, 'slugFrom')) {
            return static::$slugFrom;
        }

        return 'name';
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}