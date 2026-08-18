<?php

namespace App\Models;

use App\Support\Money;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Alphabet sans caractères ambigus (pas de 0/O ni 1/I).
     */
    public const HUMAN_CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'user_id',
        'ticket_code',
        'ticket_number',
        'seat_number',
        'payment_method',
        'mobile_provider',
        'status',
        'scanned_at',
    ];

    public const MOBILE_PROVIDERS = [
        'mpesa' => 'M-Pesa',
        'orange_money' => 'Orange Money',
        'airtel_money' => 'Airtel Money',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (blank($ticket->ticket_code)) {
                $ticket->ticket_code = static::generateUniqueCode();
            }

            if (blank($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateUniqueTicketNumber();
            }
        });
    }

    /**
     * Référence lisible pour l'UI (alias d'affichage).
     */
    protected function formattedNumber(): Attribute
    {
        return Attribute::get(fn () => $this->ticket_number);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    /**
     * Libellé d'accès affiché sur le billet numérique / PDF.
     */
    public function accessLabel(): string
    {
        $type = $this->ticketType;
        $zone = $type?->name ? strtoupper($type->name) : 'STANDARD';

        if ($type?->is_seated || $this->seat_number) {
            return 'ZONE '.$zone.' — '.($this->seat_number ?: 'Place assignée');
        }

        return 'ACCÈS DEBOUT / PLACEMENT LIBRE'.($type ? ' ('.$zone.')' : '');
    }

    public function displayPrice(): string
    {
        $price = $this->ticketType?->price ?? $this->event?->price ?? 0;

        return Money::format((float) $price);
    }

    public function paymentMethodLabel(): string
    {
        if (! $this->payment_method) {
            return 'Non renseigné';
        }

        if ($this->payment_method === 'mobile_money') {
            return self::MOBILE_PROVIDERS[$this->mobile_provider]
                ?? 'Mobile Money';
        }

        return Event::PARTICIPANT_PAYMENT_METHODS[$this->payment_method]
            ?? $this->payment_method;
    }

    /**
     * Code technique opaque pour le QR Code / scan.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8)).'-'.hash('sha256', Str::uuid().microtime());
            $code = substr($code, 0, 40);
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Référence humaine courte : EP-XXXX-XXXX (sans 0/O/1/I).
     */
    public static function generateUniqueTicketNumber(): string
    {
        do {
            $number = 'EP-'.static::randomHumanBlock(4).'-'.static::randomHumanBlock(4);
        } while (static::where('ticket_number', $number)->exists());

        return $number;
    }

    protected static function randomHumanBlock(int $length): string
    {
        $alphabet = self::HUMAN_CODE_ALPHABET;
        $max = strlen($alphabet) - 1;
        $block = '';

        for ($i = 0; $i < $length; $i++) {
            $block .= $alphabet[random_int(0, $max)];
        }

        return $block;
    }
}
