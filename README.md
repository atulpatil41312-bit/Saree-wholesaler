# WholeMarket Saree Wholesaler

A Laravel marketplace for wholesale saree shops, seller sign-up, seller storefront themes, administrator activation, and public catalogues.

## Deploy on Render

The repository includes `render.yaml` and a Docker build for a full Laravel deployment with Render Postgres. The Blueprint provisions both the web service and the database, then deploys each push to `main`.

1. Open [Deploy to Render](https://render.com/deploy?repo=https://github.com/atulpatil41312-bit/Saree-wholesaler).
2. Sign in to Render and connect the GitHub repository.
3. Enter a private `INITIAL_ADMIN_EMAIL` and strong `INITIAL_ADMIN_PASSWORD` when prompted. They create the only initial administrator; demo credentials are never created in production.
4. Deploy the Blueprint. The health endpoint is `/up`; Render supplies the public `onrender.com` URL when the build completes.

The first request can take longer when running on Render's free plan. Every later push to `main` triggers a deployment automatically.

## Local development

```powershell
composer install
npm install --ignore-scripts
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

The local development database is SQLite; Render uses managed PostgreSQL.

## WholeMarket operational notes

### Local-only demo access

The development seeder creates local demonstration accounts only. They are never created in
production: production requires `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD`. Do not use
the local credentials outside a disposable development database.

### Queue and scheduler

The local default queue connection is `database`. In a separate terminal, start a worker before
testing queued notifications or jobs:

```powershell
php artisan queue:work
```

Run the scheduler continuously in development, or configure a one-minute scheduler trigger in
production. Sponsorship expiry checks remain enforced at request time even if a scheduled task is
delayed.

```powershell
php artisan schedule:work
```

### Backup and restore

Back up the production database and product-media disk together. Test restoration in an isolated
staging environment before relying on a backup. For the local SQLite demo, stop the app and copy
`database/database.sqlite`; restore by replacing it only while the application is stopped, then run
`php artisan migrate --force` to verify the schema.

### Deployment and rollback

Before deployment, run migrations, build frontend assets, restart queue workers, and verify the
health endpoint at `/up`. Keep the immediately preceding release and a database backup available
for rollback. A rollback must not run destructive reverse migrations against a live database;
restore a tested backup only when the migration plan explicitly calls for it.

### PWA boundary and current limitations

WholeMarket is one market-level installable PWA, not a separate installable app per shop. The
service worker caches only static public assets and a neutral offline page. It never caches
dashboard, authentication, payment, enquiry, API, or mutable storefront HTML responses.

Live payments, custom domains, carts, checkout, shipping, inventory synchronisation, and an
unrestricted page builder are intentionally out of scope for this release.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
