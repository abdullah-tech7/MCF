<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'audit_logs';

    /**
     * Disable the updated_at timestamp.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'user_role',
        'route_name',
        'action',
        'description',
        'data',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * The attribute casts.
     */
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];
}