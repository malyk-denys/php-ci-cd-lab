<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'code',
        'seat_number',
        'price',
        'customer_email',
        'status',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'sold_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class)->withTimestamps();
    }
}
