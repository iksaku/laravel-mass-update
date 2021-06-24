<?php

namespace Iksaku\Laravel\MassUpdate\Tests\App\Models;

use Carbon\Carbon;
use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property Carbon $updated_at
 */
class User extends Model
{
    use HasFactory;
    use MassUpdatable;

    protected $guarded = [];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
