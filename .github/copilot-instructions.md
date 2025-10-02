# Copilot Instructions for event-booking (Laravel)

## Project Overview
- This is a Laravel-based web application for event booking.
- Follows standard Laravel MVC structure: `app/` (logic), `resources/views/` (UI), `routes/` (routing), `database/` (migrations, seeders, factories).
- Uses Eloquent ORM for database access and migrations for schema changes.

## Key Directories & Files
- `app/Models/` — Eloquent models (e.g., `User.php`).
- `app/Http/Controllers/` — Controllers for handling requests.
- `routes/web.php` — Web routes (browser-based UI).
- `routes/api.php` — API routes (stateless endpoints).
- `resources/views/` — Blade templates for UI.
- `database/migrations/` — Schema migrations.
- `database/seeders/` — Database seeders.
- `tests/` — Feature and unit tests.

## Developer Workflows
- **Start local server:** `php artisan serve`
- **Run tests:** `php artisan test` or `vendor\bin\phpunit`
- **Run migrations:** `php artisan migrate`
- **Seed database:** `php artisan db:seed`
- **Install dependencies:** `composer install` (PHP), `npm install` (JS/CSS)
- **Build frontend assets:** `npm run build` (uses Vite)

## Project Conventions
- Controllers are grouped by domain in `app/Http/Controllers/`.
- Models are in `app/Models/` and use Eloquent relationships.
- Blade templates are in `resources/views/`.
- Use Laravel's middleware for authentication/authorization in `app/Http/Middleware/`.
- Environment config in `.env` (not committed).
- Use `php artisan` for most dev tasks (see `artisan` file).

## Integration & Patterns
- Uses Laravel's service provider pattern (`app/Providers/`).
- API endpoints defined in `routes/api.php`.
- Web UI routes in `routes/web.php`.
- Uses Laravel's event system for background jobs and broadcasting.
- External packages managed via Composer (`composer.json`).
- Frontend assets managed with Vite (`vite.config.js`).

## Examples
- To add a new API endpoint: define route in `routes/api.php`, create controller in `app/Http/Controllers/`, add logic, and (optionally) write tests in `tests/Feature/`.
- To add a migration: create file in `database/migrations/`, then run `php artisan migrate`.

## References
- See `README.md` for general Laravel info and links to official docs.
- See `phpunit.xml` for test configuration.

---
For more, see [Laravel Docs](https://laravel.com/docs).