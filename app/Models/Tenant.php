<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, Searchable;

    protected $with = [
        'domains',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: function () {
                $domain = $this->domains()->first()?->domain;

                if (! $domain) {
                    return null;
                }

                $baseUrl = config('app.url');
                $parsed = parse_url($baseUrl);

                $scheme = $parsed['scheme'] ?? 'https';
                $host = $parsed['host'] ?? '';
                $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

                // Build new url with tenant domain as subdomain
                $subdomainHost = $domain.'.'.$host;

                return "{$scheme}://{$subdomainHost}{$port}";
            }
        );
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
