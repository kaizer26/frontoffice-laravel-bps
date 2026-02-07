<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'slug',
        'deskripsi',
        'satuan',
        'periode_tipe',
        'layout_type',
        'numeric_format',
        'decimal_places',
        'sumber_data',
        'link_spreadsheet',
        'template_json',
    ];

    protected $casts = [
        // Removed template_json - using accessor/mutator instead for SQLite compatibility
    ];

    // Relationships
    public function entries()
    {
        return $this->hasMany(DataEntry::class);
    }

    // Accessor to ensure template_json is always properly decoded
    public function getTemplateJsonAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?? [];
        }
        return $value ?? [];
    }

    // Mutator to ensure template_json is stored as JSON string
    public function setTemplateJsonAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['template_json'] = json_encode($value);
        } elseif (is_string($value)) {
            // Validate it's valid JSON
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['template_json'] = $value;
            } else {
                throw new \InvalidArgumentException('Invalid JSON provided for template_json');
            }
        }
    }

    // Auto-generate slug from judul
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registry) {
            if (empty($registry->slug)) {
                $registry->slug = Str::slug($registry->judul);
                
                // Ensure uniqueness
                $count = 1;
                $originalSlug = $registry->slug;
                while (static::where('slug', $registry->slug)->exists()) {
                    $registry->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }
}
