<?php

namespace Iksaku\Laravel\MassUpdate\Tests\App\Models;

use Carbon\Carbon;
use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $username
 * @property string|null $name
 * @property int $rank
 * @property bool $can_code
 * @property Carbon $updated_at
 */
class User extends Model
{
    use HasFactory;
    use MassUpdatable;

    protected $guarded = [];

    protected $casts = [
        'rank' => 'int',
        'can_code' => 'bool',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
