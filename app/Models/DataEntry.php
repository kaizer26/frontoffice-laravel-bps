<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_registry_id',
        'periode',
        'data_json',
    ];

    protected $casts = [
        // Removed data_json - using accessor/mutator instead for SQLite compatibility
    ];

    // Relationships
    public function registry()
    {
        return $this->belongsTo(DataRegistry::class, 'data_registry_id');
    }

    // Accessor to ensure data_json is always properly decoded
    public function getDataJsonAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?? [];
        }
        return $value ?? [];
    }

    // Mutator to ensure data_json is stored as JSON string
    public function setDataJsonAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['data_json'] = json_encode($value);
        } elseif (is_string($value)) {
            json_decode($value);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['data_json'] = $value;
            } else {
                throw new \InvalidArgumentException('Invalid JSON provided for data_json');
            }
        }
    }
}
