<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quantity',
        'is_seated',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'is_seated' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function soldCount(): int
    {
        return $this->tickets()->where('status', '!=', 'cancelled')->count();
    }

    public function remainingSeats(): int
    {
        return max(0, $this->quantity - $this->soldCount());
    }

    public function isSoldOut(): bool
    {
        return $this->remainingSeats() <= 0;
    }

    /**
     * Attribue le prochain libellé de siège pour ce type (ex. Rangée B / Siège 14).
     */
    public function nextSeatLabel(): string
    {
        $index = $this->soldCount() + 1; // 1-based
        $seatsPerRow = 20;
        $rowLetter = chr(65 + intdiv($index - 1, $seatsPerRow)); // A, B, C...
        $seatInRow = (($index - 1) % $seatsPerRow) + 1;

        return 'Rangée '.$rowLetter.' / Siège '.$seatInRow;
    }
}
