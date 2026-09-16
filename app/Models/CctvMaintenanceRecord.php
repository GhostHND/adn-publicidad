<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CctvMaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'cctv_project_id',
        'cctv_device_id',
        'performed_by_employee_id',
        'created_by',
        'maintenance_type',
        'maintenance_date',
        'description',
        'findings',
        'actions_taken',
        'cost',
        'next_due_date',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'next_due_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            CctvProject::class,
            'cctv_project_id'
        );
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(
            CctvDevice::class,
            'cctv_device_id'
        );
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'performed_by_employee_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}