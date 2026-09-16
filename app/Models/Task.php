<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_number',
        'work_order_id',
        'work_order_item_id',
        'responsible_employee_id',
        'created_by',
        'automation_key',
        'title',
        'description',
        'status',
        'priority',
        'sort_order',
        'is_review_task',
        'started_at',
        'paused_at',
        'completed_at',
        'review_at',
        'issue_reported_at',
        'issue_description',
    ];

    protected function casts(): array
    {
        return [
            'is_review_task' => 'boolean',
            'started_at' => 'datetime',
            'paused_at' => 'datetime',
            'completed_at' => 'datetime',
            'review_at' => 'datetime',
            'issue_reported_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrder::class
        );
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(
            WorkOrderItem::class
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

    public function events(): HasMany
    {
        return $this->hasMany(
            TaskEvent::class
        );
    }
}