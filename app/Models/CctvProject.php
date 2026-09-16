<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CctvProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_number',
        'client_id',
        'quotation_id',
        'sale_id',
        'work_order_id',
        'installation_id',
        'responsible_employee_id',
        'created_by',
        'source',
        'system_type',
        'status',
        'site_name',
        'address',
        'city',
        'contact_name',
        'contact_phone',
        'internet_provider',
        'network_notes',
        'site_survey_scheduled_at',
        'site_survey_completed_at',
        'site_survey_notes',
        'notes',
        'installed_at',
        'maintenance_due_at',
    ];

    protected function casts(): array
    {
        return [
            'site_survey_scheduled_at' =>
                'datetime',

            'site_survey_completed_at' =>
                'datetime',

            'installed_at' =>
                'datetime',

            'maintenance_due_at' =>
                'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class
        );
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(
            Quotation::class
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class
        );
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(
            Installation::class
        );
    }

    public function responsibleEmployee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'responsible_employee_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function devices(): HasMany
    {
        return $this->hasMany(
            CctvDevice::class
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