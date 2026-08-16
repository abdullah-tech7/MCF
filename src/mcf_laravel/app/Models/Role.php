<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Role
 *
 * @property int $id
 * @property string $name_ar
 * @property string $name_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read string $name
 *
 * @property Collection<int, User> $users
 *
 * @package App\Models
 */
class Role extends Model
{

    protected $table = 'roles';

    protected $fillable = [
        'name',
    ];



    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
