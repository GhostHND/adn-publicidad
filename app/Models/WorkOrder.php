<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_order_number',
        'client_id',
        'sale_id',
        'responsible_employee_id',
        'created_by',
        'title',
        'status',
        'priority',
        'order_date',
        'due_date',
        'started_at',
        'completed_at',
        'delivered_at',
        'description',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class
        );
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sale::class
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

    public function items(): HasMany
    {
        return $this->hasMany(
            WorkOrderItem::class
        );
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            WorkOrderMaterial::class
        );
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(
            Task::class
        )->orderBy('sort_order');
    }
}