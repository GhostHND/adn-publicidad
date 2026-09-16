<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CctvDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cctv_project_id',
        'device_code',
        'device_type',
        'brand',
        'model',
        'serial_number',
        'mac_address',
        'ip_address',
        'channel',
        'location',
        'status',
        'installed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'channel' => 'integer',
            'installed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            CctvProject::class,
            'cctv_project_id'
        );
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(
            CctvCredential::class
        );
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(
            CctvMaintenanceRecord::class
        );
    }
}