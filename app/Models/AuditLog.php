<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'module',
        'action',
        'description',
        'route_name',
        'method',
        'url',
        'status_code',
        'entity_type',
        'entity_id',
        'request_payload',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' =>
                'array',

            'status_code' =>
                'integer',

            'entity_id' =>
                'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function scopeModule(
        Builder $query,
        ?string $module
    ): Builder {
        if (!$module) {
            return $query;
        }

        return $query->where(
            'module',
            $module
        );
    }

    public function scopeAction(
        Builder $query,
        ?string $action
    ): Builder {
        if (!$action) {
            return $query;
        }

        return $query->where(
            'action',
            $action
        );
    }
}