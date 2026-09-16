<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CctvCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'cctv_project_id',
        'cctv_device_id',
        'credential_type',
        'label',
        'username',
        'secret_value',
        'host',
        'port',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Laravel cifra automáticamente este campo usando APP_KEY.
            |--------------------------------------------------------------------------
            */

            'secret_value' =>
                'encrypted',

            'port' =>
                'integer',
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
}