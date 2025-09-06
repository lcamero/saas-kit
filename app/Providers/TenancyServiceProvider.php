<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Features\SupportFileUploads\FilePreviewController;
use Livewire\Livewire;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    // Jobs\SeedDatabase::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                    // Per the docs, since we use Livewire file uploads which uses real-time facades
                    // https://tenancyforlaravel.com/docs/v3/integrations/livewire
                    // https://tenancyforlaravel.com/docs/v3/realtime-facades
                    \App\Jobs\CreateFrameworkDirectoriesForTenant::class,

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(true), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(true), // `false` by default, but you probably want to make this `true` for production.
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,

                function (Events\TenancyInitialized $event) {
                    // Include port if using non-standard. Helps with custom ports or localhost systems
                    $host = request()->getHttpHost();

                    // Build proper scheme + host
                    $scheme = request()->isSecure() ? 'https://' : 'http://';

                    config(['auth.defaults.guard' => 'tenant']);
                    config(['app.url' => $scheme.$host]);
                    config(['app.name' => app(\App\Settings\Tenant\GeneralSettings::class)->application_name]);

                    config(['scout.prefix' => 'tenant_'.tenant('id')]);
                    config(['settings.cache.prefix' => 'tenant_'.tenant('id')]);
                },
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();

        $this->makeTenancyMiddlewareHighestPriority();

        DomainTenantResolver::$shouldCache = true;

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(
                    'web',
                    'universal',
                    \App\Http\Middleware\InitializeTenancy::class,
                );
        });

        FilePreviewController::$middleware = ['web', 'universal', \App\Http\Middleware\InitializeTenancy::class];

        // Add tags for Scout jobs
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $scoutJobs = [
                \Laravel\Scout\Jobs\MakeSearchable::class,
                \Laravel\Scout\Jobs\RemoveFromSearch::class,
                \Laravel\Scout\Jobs\RemoveableScoutCollection::class,
                \Laravel\Scout\Jobs\MakeRangeSearchable::class,
            ];

            // $payload['data']['command'] holds the job
            if (collect($scoutJobs)->contains(fn ($class) => $payload['data']['command'] instanceof $class)) {
                return [
                    'tags' => [
                        config('tenancy.job_tags_prefix').tenant('id'),
                    ],
                ];
            }

            return [];
        });
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant/web.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant/web.php'));
            }

            if (file_exists(base_path('routes/tenant/api.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant/api.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
            \App\Http\Middleware\InitializeTenancy::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
