<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installation_number',
        'work_order_id',
        'client_id',
        'responsible_employee_id',
        'created_by',
        'source',
        'status',
        'scheduled_at',
        'departed_at',
        'started_at',
        'completed_at',
        'contact_name',
        'contact_phone',
        'address',
        'city',
        'reference',
        'estimated_duration_minutes',
        'notes',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'departed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class
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
}