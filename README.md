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

Lastly, a quick way to fire up the configured services and start building is to run the following command:

```bash
composer dev
```

This will run vite to serve your asset and listen for changes (`npm run dev`), run Laravel Pail to tail logs (`php artisan pail`) and launch Laravel Horizon to manage your queues (`php artisan horizon`).

---

## Setup

For the time being you may reference the base setup in base kit documentation [Setup](https://github.com/lcamero/laravel-starter-kit?tab=readme-ov-file#setup). Any additional steps will be listed below.