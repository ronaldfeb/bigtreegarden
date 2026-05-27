## BigTree Garden Platform

Modern Laravel 13 + Inertia.js + Vue 3 starter, using Vite, Tailwind CSS, Pest, and Laravel Sail for local development. This project is intended as the base for the BigTree Garden Platform.

### Tech stack

- **Backend**: Laravel 13 (PHP 8.3), Fortify for authentication, Wayfinder for typed routes
- **Frontend**: Inertia.js v2, Vue 3, Vite, Tailwind CSS v4, shadcn-vue components
- **Tooling**: Laravel Sail, Pest, Laravel Pint, ESLint, Prettier, TypeScript

### Prerequisites

- **PHP** 8.3+ (or Docker, if using Sail)
- **Composer**
- **Node.js** 20+ and **npm**
- **Docker + Docker Compose** (recommended) for running via Laravel Sail

### Getting started

Clone the repository and install dependencies:

```bash
git clone <your-repo-url>.git
cd bigtreegarden-platform

composer install
npm install
```

Copy the environment file and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database connection in `.env`, then run migrations:

```bash
php artisan migrate
```

Build frontend assets (or start the dev server, see below):

```bash
npm run build
```

> **Note**: In this project, many commands are commonly run through Laravel Sail. If you prefer Docker-based development, use the Sail equivalents described in the next section instead of running PHP and Node directly on your machine.

### Using Laravel Sail (Docker-based)

If you want to use Sail for a fully containerized setup:

```bash
cp .env.example .env
composer install

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Then open the app in your browser (typically `http://localhost` or the port configured for Sail).

### Local development

There are two main ways to run the app locally.

- **Via Composer script (non-Sail)**:

  ```bash
  composer dev
  ```

  This runs:

  - Laravel development server
  - Queue listener
  - Pail log viewer
  - Vite dev server

- **Via Sail (Docker)**:

  ```bash
  ./vendor/bin/sail up -d
  ./vendor/bin/sail artisan queue:listen
  ./vendor/bin/sail npm run dev
  ```

### Frontend scripts

From the project root:

- **Start Vite dev server**:

  ```bash
  npm run dev
  ```

- **Build production assets**:

  ```bash
  npm run build
  ```

- **Type-check Vue/TS**:

  ```bash
  npm run types:check
  ```

- **Lint & format**:

  ```bash
  npm run lint        # ESLint with auto-fix
  npm run lint:check  # ESLint without fixes
  npm run format      # Prettier (resources/)
  npm run format:check
  ```

### PHP tooling

- **Run tests (Pest)**:

  ```bash
  ./vendor/bin/sail artisan test --compact
  ```

  Or via Composer:

  ```bash
  composer test
  ```

- **Code style (Pint)**:

  ```bash
  ./vendor/bin/sail pint --parallel
  ```

### Environment & configuration

- Main configuration lives in `.env`. At minimum you should configure:
  - **APP_NAME**, **APP_URL**
  - **DB_CONNECTION**, **DB_HOST**, **DB_PORT**, **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD**
- This project ships with Laravel Fortify for authentication and Inertia.js + Vue for the frontend SPA shell.

### Useful Composer scripts

From `composer.json`:

- **`composer setup`**: One-shot setup for a fresh project (installs Composer deps, creates `.env`, generates key, runs migrations, installs npm deps, builds assets).
- **`composer dev`**: Starts an all-in-one local dev environment (Laravel server, queue, logs, Vite).
- **`composer test`**: Clears config cache, runs Pint (lint:check), then runs the test suite.

### Contributing

- Follow existing code style and patterns.
- Run **tests and linters** before pushing:

  ```bash
  composer test
  npm run lint:check
  npm run types:check
  npm run format:check
  ```

### License

This project is open-sourced software licensed under the **MIT license**.

