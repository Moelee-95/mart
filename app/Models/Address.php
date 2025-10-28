<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'latitude',
        'longitude',
        'area',
        'type',
        'building_name',
        'floor',
        'apartment_number',
        'nearby_landmark',
        'phone',
        'additional_directions',
        'label',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFormattedAddressAttribute()
    {
        $parts = array_filter([
            $this->building_name,
            $this->floor ? "Floor {$this->floor}" : null,
            $this->apartment_number ? "Apt {$this->apartment_number}" : null,
            $this->area
        ]);
        
        return implode(', ', $parts);
    }
}