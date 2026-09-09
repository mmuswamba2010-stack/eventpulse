<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'google_id',
        'facebook_id',
        'mobile_money_provider',
        'bank_account_holder',
        'bank_name',
        'bank_account_number',
        'organizer_status',
        'organizer_reviewed_at',
        'organizer_moderation_note',
        'organizer_terms_accepted_at',
        'organizer_terms_version',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'organizer_reviewed_at' => 'datetime',
            'organizer_terms_accepted_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isOrganizer(): bool
    {
        return $this->role === 'organizer';
    }

    public function isParticipant(): bool
    {
        return $this->role === 'participant';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrganizerApproved(): bool
    {
        return $this->organizer_status === 'approved' || $this->organizer_status === null;
    }

    public function needsOrganizerApproval(): bool
    {
        return $this->isOrganizer()
            && config('eventpulse.organizer_moderation', true)
            && $this->organizer_status === 'pending';
    }

    public function canAccessOrganizerSpace(): bool
    {
        return $this->isOrganizer()
            && ! $this->isSuspended()
            && $this->organizer_status !== 'rejected'
            && ($this->isOrganizerApproved() || ! config('eventpulse.organizer_moderation', true));
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function mobileMoneyProviderLabel(): ?string
    {
        if (! $this->mobile_money_provider) {
            return null;
        }

        return Ticket::MOBILE_PROVIDERS[$this->mobile_money_provider] ?? null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
