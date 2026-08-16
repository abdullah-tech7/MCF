<?php

declare(strict_types=1);

namespace App\Models;

use App\MCF\Audit\Data\AuditDefinition;
use App\MCF\Audit\McfAuditable;
use App\MCF\Notification\Internal\NotificationRequest;
use App\MCF\Notification\NotificationData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property string $password
 * @property bool $is_active
 * @property Carbon|null $last_login_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Role $role

 * @property Collection|VerificationRequest[] $verification_requests
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use McfAuditable, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $casts = [
        'role_id' => 'int',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'is_active'         => 'bool',
        'last_login_at'     => 'datetime',
        'deletion_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
    ];

    public function verification_requests()
    {
        return $this->hasMany(VerificationRequest::class);
    }

        public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }


    /**
     * @return AuditDefinition[]
     */
    public static function auditDefinitions(): array
    {
        return [
            /*
             * Password updated.
             *
             * Audit + Mail notification.
             */
            new AuditDefinition(
                action: 'update',
                columns: ['password'],
                condition: null,
                message: 'The user updated the password.',
                notification: function (
                    Model $model,
                ): NotificationRequest {
                    return new NotificationRequest(
                        data: NotificationData::passwordUpdated(),
                        target: 'users',
                        users: [$model->getKey()],
                        channels: [
                            'mail',
                        ],
                    );
                },
            ),

            /*
             * Email updated.
             *
             * Audit + Mail notification.
             */
            new AuditDefinition(
                action: 'update',
                columns: ['email'],
                condition: null,
                message: 'The user updated the email.',
                notification: function (
                    Model $model,
                ): NotificationRequest {
                    return new NotificationRequest(
                        data: NotificationData::emailUpdated(
                            email: $model->email,
                        ),
                        target: 'users',
                        users: [$model->getKey()],
                        channels: [
                            'mail',
                        ],
                    );
                },
            ),

            /*
             * Phone number updated.
             *
             * Audit + Mail notification.
             */
            new AuditDefinition(
                action: 'update',
                columns: ['phone'],
                condition: null,
                message: 'The user updated the phone number.',
                notification: function (
                    Model $model,
                ): NotificationRequest {
                    return new NotificationRequest(
                        data: NotificationData::phoneUpdated(
                            phone: $model->phone,
                        ),
                        target: 'users',
                        users: [$model->getKey()],
                        channels: [
                            'mail',
                        ],
                    );
                },
            ),

            /*
             * Email verified.
             *
             * Audit + Mail notification.
             */
            new AuditDefinition(
                action: 'update',
                columns: ['email_verified_at'],
                condition: null,
                message: 'The user verified their email address.',
                notification: function (
                    Model $model,
                ): NotificationRequest {
                    return new NotificationRequest(
                        data: NotificationData::emailVerified(),
                        target: 'users',
                        users: [$model->getKey()],
                        channels: [
                            'mail',
                        ],
                    );
                },
            ),

            /*
             * Phone verified.
             *
             * Audit + Mail + SMS notifications.
             */
            new AuditDefinition(
                action: 'update',
                columns: ['phone_verified_at'],
                condition: null,
                message: 'The user verified their phone number.',
                notification: function (
                    Model $model,
                ): NotificationRequest {
                    return new NotificationRequest(
                        data: NotificationData::phoneVerified(),
                        target: 'users',
                        users: [$model->getKey()],
                        channels: [
                            'sms',
                            'mail',
                        ],
                    );
                },
            ),

            /*
             * User deleted.
             *
             * Audit only.
             */
            new AuditDefinition(
                action: 'delete',
                columns: [],
                condition: null,
                message: 'The user deleted an user.',
            ),
        ];
    }
}
