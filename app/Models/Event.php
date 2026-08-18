<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    public static function publicationFee(): float
    {
        return (float) config('eventpulse.publication_fee', 25000);
    }

    public static function requiresPublicationPayment(): bool
    {
        return (bool) config('eventpulse.require_publication_payment', false);
    }

    public static function allowsSeatedPlacement(): bool
    {
        return (bool) config('eventpulse.enable_seated_placement', false);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Event>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    public function scopeVisibleInCatalog($query)
    {
        $query->where('status', 'published');

        if (static::requiresPublicationPayment()) {
            $query->where('is_paid', true);
        }

        return $query;
    }

    /** @deprecated Utiliser publicationFee() — montant en francs congolais */
    public const PUBLICATION_FEE = 25000;

    public const PLACEMENT_STANDING = 'standing';

    public const PLACEMENT_SEATED = 'seated';

    /** Catégories catalogue public (5 + autre). */
    public const CATEGORIES = [
        'music' => 'Musique',
        'tech' => 'Tech',
        'art' => 'Art',
        'sport' => 'Sport',
        'conference' => 'Conférence',
        'other' => 'Autre',
    ];

    /** Modes de paiement proposables aux participants. */
    public const PARTICIPANT_PAYMENT_METHODS = [
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte bancaire',
        'cash' => 'Espèces',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'location',
        'category',
        'event_date',
        'capacity',
        'price',
        'image_path',
        'status',
        'placement_mode',
        'accepted_payment_methods',
        'is_paid',
        'publication_fee',
        'payment_method',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'price' => 'decimal:2',
            'is_paid' => 'boolean',
            'publication_fee' => 'decimal:2',
            'paid_at' => 'datetime',
            'accepted_payment_methods' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function isSeatedPlacement(): bool
    {
        return $this->placement_mode === self::PLACEMENT_SEATED;
    }

    public function isStandingPlacement(): bool
    {
        return $this->placement_mode !== self::PLACEMENT_SEATED;
    }

    /**
     * @param  array<int, array<string, mixed>>  $ticketTypes
     */
    public static function ticketTypesAreFree(array $ticketTypes): bool
    {
        if ($ticketTypes === []) {
            return true;
        }

        return collect($ticketTypes)->every(
            fn (array $type) => (float) ($type['price'] ?? 0) <= 0
        );
    }

    public function isFreeEvent(): bool
    {
        $types = $this->relationLoaded('ticketTypes')
            ? $this->ticketTypes
            : $this->ticketTypes()->get();

        if ($types->isNotEmpty()) {
            return $types->every(fn (TicketType $type) => (float) $type->price <= 0);
        }

        return (float) $this->price <= 0;
    }

    /**
     * Modes de paiement proposés aux participants sur cet événement.
     *
     * @return list<string>
     */
    public function acceptedPaymentMethods(): array
    {
        $methods = $this->accepted_payment_methods;

        if (is_array($methods)) {
            if ($methods === []) {
                return [];
            }

            return array_values(array_filter(
                $methods,
                fn (string $method) => array_key_exists($method, self::PARTICIPANT_PAYMENT_METHODS)
            ));
        }

        return array_keys(self::PARTICIPANT_PAYMENT_METHODS);
    }

    public function acceptsPaymentMethod(string $method): bool
    {
        return in_array($method, $this->acceptedPaymentMethods(), true);
    }

    public function paymentMethodLabel(string $method): string
    {
        return self::PARTICIPANT_PAYMENT_METHODS[$method] ?? $method;
    }

    /**
     * Nombre de places restantes (somme des types, sinon capacité globale).
     */
    public function remainingSeats(): int
    {
        $types = $this->relationLoaded('ticketTypes')
            ? $this->ticketTypes
            : $this->ticketTypes()->get();

        if ($types->isNotEmpty()) {
            return (int) $types->sum(fn (TicketType $type) => $type->remainingSeats());
        }

        return max(0, $this->capacity - $this->tickets()->where('status', '!=', 'cancelled')->count());
    }

    public function isSoldOut(): bool
    {
        return $this->remainingSeats() <= 0;
    }

    public function isUpcoming(): bool
    {
        return $this->event_date->isFuture();
    }

    public function needsPayment(): bool
    {
        return static::requiresPublicationPayment() && ! $this->is_paid;
    }

    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if (! static::requiresPublicationPayment()) {
            return true;
        }

        return $this->is_paid;
    }

    /**
     * Prix affiché catalogue : plus bas type de billet.
     */
    public function startingPrice(): float
    {
        if ($this->relationLoaded('ticketTypes') && $this->ticketTypes->isNotEmpty()) {
            return (float) $this->ticketTypes->min('price');
        }

        return (float) $this->price;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    public static function categoryKeys(): array
    {
        return array_keys(self::CATEGORIES);
    }
}
