<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    public const SALE_ACTIVE = 'active';

    public const SALE_INACTIVE = 'inactive';

    public const SALE_UPCOMING = 'upcoming';

    public const SALE_ENDED = 'ended';

    public const SALE_SOLD_OUT = 'sold_out';

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quantity',
        'is_seated',
        'sale_starts_at',
        'sale_ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'is_seated' => 'boolean',
            'is_active' => 'boolean',
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
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

    /**
     * @param  Builder<TicketType>  $query
     * @return Builder<TicketType>
     */
    public function scopePurchasable(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $inner) use ($now) {
                $inner->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now);
            })
            ->where(function (Builder $inner) use ($now) {
                $inner->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now);
            });
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

    public function isWithinSaleWindow(): bool
    {
        $now = now();

        if ($this->sale_starts_at && $now->lt($this->sale_starts_at)) {
            return false;
        }

        if ($this->sale_ends_at && $now->gt($this->sale_ends_at)) {
            return false;
        }

        return true;
    }

    public function isPurchasable(): bool
    {
        return $this->is_active
            && $this->isWithinSaleWindow()
            && ! $this->isSoldOut();
    }

    public function saleStatus(): string
    {
        if (! $this->is_active) {
            return self::SALE_INACTIVE;
        }

        if ($this->isSoldOut()) {
            return self::SALE_SOLD_OUT;
        }

        if ($this->sale_starts_at && now()->lt($this->sale_starts_at)) {
            return self::SALE_UPCOMING;
        }

        if ($this->sale_ends_at && now()->gt($this->sale_ends_at)) {
            return self::SALE_ENDED;
        }

        return self::SALE_ACTIVE;
    }

    public function saleStatusLabel(): string
    {
        return match ($this->saleStatus()) {
            self::SALE_INACTIVE => __('Ticket sale inactive'),
            self::SALE_UPCOMING => __('Ticket sale upcoming'),
            self::SALE_ENDED => __('Ticket sale ended'),
            self::SALE_SOLD_OUT => __('Ticket sale sold out'),
            default => __('Ticket sale active'),
        };
    }

    /**
     * Attribue le prochain libellé de siège pour ce type (ex. Rangée B / Siège 14).
     */
    public function nextSeatLabel(): string
    {
        $index = $this->soldCount() + 1;
        $seatsPerRow = 20;
        $rowLetter = chr(65 + intdiv($index - 1, $seatsPerRow));
        $seatInRow = (($index - 1) % $seatsPerRow) + 1;

        return 'Rangée '.$rowLetter.' / Siège '.$seatInRow;
    }
}
