<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class VerificationRequest
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $channel
 * @property string $method
 * @property string $target
 * @property string|null $code_hash
 * @property string|null $token_hash
 * @property int $send_attempts
 * @property Carbon|null $last_sent_at
 * @property Carbon $expires_at
 * @property Carbon|null $verified_at
 * @property Carbon|null $revoked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 *
 * @package App\Models
 */
class VerificationRequest extends Model
{
	protected $table = 'verification_requests';

	protected $casts = [
		'user_id' => 'int',
		'send_attempts' => 'int',
		'last_sent_at' => 'datetime',
		'expires_at' => 'datetime',
		'verified_at' => 'datetime',
		'revoked_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'type',
		'channel',
		'method',
		'target',
		'code_hash',
		'token_hash',
		'send_attempts',
		'last_sent_at',
		'expires_at',
		'verified_at',
		'revoked_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
