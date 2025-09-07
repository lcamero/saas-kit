<?php

namespace App\Models\Tenant;

use App\Notifications\Tenant\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\TenantUserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, Searchable;

    protected $connection = 'tenant'; // tenancy will swap this automatically

    // To force Spatie's laravel permission guard usage
    protected string $guard_name = 'tenant';

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

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
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Define a special searchable index to make it tenant aware
     */
    public function searchableAs(): string
    {
        // scout.prefix should be getting applied in the TenancyServiceProvider already
        // so this will append to it
        // e.g. tenant_ab294298-be61-4e62-84d0-be9ea085cd55_users
        $index = [config('scout.prefix'), $this->getTable()];

        return implode('_', $index);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            // 'id' => (int) $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
