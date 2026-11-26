<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Discount extends Model
{
    /** @use HasFactory<DiscountFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'active_from',
        'active_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'active_from' => 'datetime',
            'active_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class)->withTimestamps();
    }
}
