<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class DomainFactory extends Factory
{
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => $this->faker->unique()->slug(1),
            'tenant_id' => (string) Str::uuid(), // overwritten by ->for(Tenant::factory())
        ];
    }

    /**
     * State: append .test to the subdomain.
     */
    public function withFullDomain(string $tld = 'test'): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => "{$attributes['domain']}.$tld",
        ]);
    }
}
