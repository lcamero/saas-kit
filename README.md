# SaaS Kit

This [SaaS Kit](https://github.com/lcamero/saas-kit) comes pre-configured with a modern Laravel 12 stack, including [Livewire v3](https://livewire.laravel.com/) and [Livewire Flux Pro](https://fluxui.dev), along with a curated set of Laravel packages and developer tooling.

It is created on top of the [Laravel Starter Kit](https://github.com/lcamero/laravel-starter-kit) and has been extended and modified to suit custom requirements.

---

## Steps to install

You may get started by running the following command

```bash
laravel new my-app --using=lcamero/saas-kit
```

Or, if you prefer, use the composer create-project command instead

```bash
composer create-project lcamero/saas-kit
```

You will be asked if you wish to install a Flux UI pro license after the project is created so it configures it right away. Otherwise, you may activate it later with the following command

```bash
php artisan flux:activate
```

Run migrations with

```bash
php artisan migrate
```

Lastly, a quick way to fire up the configured services and start building is to run the following command:

```bash
composer dev
```

This will run vite to serve your asset and listen for changes (`npm run dev`), run Laravel Pail to tail logs (`php artisan pail`) and launch Laravel Horizon to manage your queues (`php artisan horizon`).

---

## Setup

For the time being you may reference the base setup in base kit documentation [Setup](https://github.com/lcamero/laravel-starter-kit?tab=readme-ov-file#setup). Any additional steps will be listed below.

### Current Differences from base Starter Kit

- Removed [Laravel Pulse][https://laravel.com/docs/12.x/pulse]. This package is removed from the installation as it provides less value out of the box in the multi-tenant model this kit uses. Some configuration changes can be applied to resolve users per tenant and show the information in the cards, but it is probably more work to get everything setup properly than the use it'll get at the moment. Also, other tools can be used to track performance and usage
- In relation to [Spatie Laravel Permission](https://github.com/spatie/laravel-permission), the main difference is that on tenant creation, the administrator account is created with a random password instead of a static one. This is meant to be used along with user impersonation, so for the most part the password does not need to be known. The central app does get an administrator account with a known password. The tenant administrator account receives the email defined in `config/tenancy.php` under 'provision_admin_email`. It uses the value from the `.env` variable `TENANCY_PROVISION_ADMIN_EMAIL` or a default.

## Packages

### Production

#### Tenancy for Laravel

[Tenancy for Laravel v3](https://tenancyforlaravel.com/) is used to manage tenancy for the application.

By default this kit comes configured to work in a multi-database model, with each tenant identified by subdomains. However, you can easily use a different identification approach by configuring one of the available identifiers provided by the tenancy package.

You just need to modify the configuration in `config/tenancy.php` by changing the `identification_handler` key.

```php
...
'identification_handler' => Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
// or
'identification_handler' => Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
// etc.
...
```

> To run tenancy in single-database mode instead you will need to apply the necessary changes to the codebase as described in the package documentation since those have not been made as easy to swap in this kit yet. You will need to take care of making modifications to the migrations, tenancy bootstrappers, and relevant code.

##### Tenancy Configuration out of the box

As stated above, this kit is configured with a multi-database tenancy model, identifying tenants by subdomains. Some other pieces of the kit have been configured in order to work properly with some of the included packages.

Tenant models are configured to be soft deleted in case you just need to manage enabling and disabling them. This make databases remain after deletion so keep that in mind if you would like a different behavior.

Some specific configurations you will find in the kit includes:

###### Routes

Specific tenant routes have been configured under `route/tenant` for `web` and `api` routes. This allows keeping the logic split between the central/landlord app and the tenant-specific code.

###### Views

Views and components were also split into their own directories so they are independent of changes on the central app. This avoids having to be constantly checking if tenancy is initialized when building views. For the most part they're split into the `resources/views/tenant`, `resources/views/components/tenant` and `resources/views/livewire/tenant` directories

###### User Model

An independent `\App\Models\Tenant\User` class is configured to manage the users created within tenants, different from your central users that will use the standard `App\Models\User` class.

They are almost identical other than some tweaks around the database connection, Laravel Scout specific configuration, etc.

A separate model will allow you to specify logic relevant to the tenant users and keep it separate from the central implementation. Think of scopes, attributes, relationships, etc., that could be different in both contexts.

###### User Impersonation

The user impersonation feature is enabled and configured to allow users in the central app to log into the tenants.

This works along with a preconfigured CentralAdministrator account that gets provisioned by default on all tenants. It gets created with the base permissions by using the [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) package to configure and verify roles and permissions.

###### Integration with Laravel Scout

To work well with Laravel Scout, the kit makes a modification to the `scout.prefix` configuration in the tenancy initialization process within the `TenancyServiceProvider` class so indexes are kept separate from central.

A tagging mechanism is also included in the provider to make the Laravel Scout jobs get a tenant tag so they're easier to track.

###### Integration with Laravel Socialite

To allow Laravel Socialite to work in the tenant's context, the flow had to be modified so they could be configured with providers like Google that require a full url to be used as redirect when configuring the OAuth client on their platform (this means that you cannot use wildcards for subdomains to authenticate users).

This results in having the login request initiated in the tenant login screen, having the provider redirect back to central and then central redirecting to the tenant's context and authenticating them.

To do this, the kit includes state information in the redirect request that works in tandem with a "magic link" route that exists in the tenant, allowing a single use, temporary, signed URL to log the user into the tenant system after a successful redirect from the OAuth provider.