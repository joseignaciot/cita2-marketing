# SearchReports

Multi-user Google Search Console property manager with customizable reports.

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8.0
- Redis 7.x

## Installation

### 1. Clone and install dependencies

```bash
git clone <repo-url> searchreports
cd searchreports
composer install
npm install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and fill in:
- Database credentials (`DB_*`)
- Google OAuth credentials (`GOOGLE_*`)
- Redis connection (`REDIS_*`)

### 3. Start services with Docker

```bash
docker compose up -d
```

This starts MySQL (3306), Redis (6379), and Mailhog (8025 web UI).

### 4. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build frontend assets

```bash
npm run build
# or for development:
npm run dev
```

### 6. Start queues

```bash
php artisan horizon
```

### 7. Start the application

```bash
php artisan serve
```

Visit: http://localhost:8000

## Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project or select existing
3. Enable **Google Search Console API**
4. Create OAuth 2.0 credentials (Web application)
5. Add authorized redirect URI: `http://localhost:8000/auth/google/callback`
6. Copy Client ID and Secret to `.env`

## Architecture

- **Backend**: Laravel 11
- **Frontend**: Vue 3 + Inertia.js
- **CSS**: Tailwind CSS v4
- **Database**: MySQL 8 + Redis
- **Queues**: Laravel Horizon
- **Auth**: Laravel Socialite (Google OAuth)

## Roles

- `admin`: Full access, Horizon dashboard, all users' data
- `user`: Own properties and reports only

## Scheduled Tasks

| Schedule | Command | Description |
|---|---|---|
| Daily 3am | `gsc:sync-all` | Sync all active GSC properties |
| Hourly | `reports:clean-expired-cache` | Remove expired cache entries |
| Weekly | `reports:clean` | Remove reports older than 30 days |
