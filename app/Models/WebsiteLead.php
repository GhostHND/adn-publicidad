<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_number',
        'external_reference',
        'name',
        'business_name',
        'phone',
        'email',
        'service_interest',
        'message',
        'source',
        'status',
        'assigned_employee_id',
        'client_id',
        'quotation_id',
        'contacted_at',
        'reviewed_at',
        'attended_at',
        'closed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' =>
                'datetime',

            'reviewed_at' =>
                'datetime',

            'attended_at' =>
                'datetime',

            'closed_at' =>
                'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSABLE
    |--------------------------------------------------------------------------
    */

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'assigned_employee_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENTE
    |--------------------------------------------------------------------------
    */

    public function client(): BelongsTo
    {
        return $this->belongsTo(
            Client::class,
            'client_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COTIZACIÓN
    |--------------------------------------------------------------------------
    */

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(
            Quotation::class,
            'quotation_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO POR ESTADO
    |--------------------------------------------------------------------------
    */

    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        if (
            !$status
        ) {
            return $query;
        }

        return $query->where(
            'status',
            $status
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES ABIERTAS
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(
        Builder $query
    ): Builder {
        return $query->whereNotIn(
            'status',
            [
                'won',
                'lost',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES NUEVAS SIN REVISAR
    |--------------------------------------------------------------------------
    */

    public function scopeUnreviewed(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                'new'
            )
            ->whereNull(
                'reviewed_at'
            );
    }
}