<?php

namespace Iksaku\Laravel\MassUpdate\Tests\App\Models;

use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $year
 * @property string $quarter
 * @property float $total
 */
class Expense extends Model
{
    use HasFactory;
    use MassUpdatable;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'year' => 'int',
        'total' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
