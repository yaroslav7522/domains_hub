# Domains Hub

A domain uptime monitoring API built with Laravel 12. Users register domains, which are checked on a configurable interval. When a domain goes down, the owner is notified via email or Telegram.

## Public URL for test
https://domains-hub.laravel.cloud/

## Tech Stack

- **PHP 8.2+** / **Laravel 12**
- **Laravel Sanctum** — token-based API authentication
- **Inertia.js + Vue 3** — frontend SPA
- **MySQL** — primary database
- **Laravel Queue** — async domain checks via jobs
- **Laravel Scheduler** — dispatches check jobs every minute

---

## Architecture

```
app/
├── Console/Commands/
│   └── CheckDomainsCommand.php     # Scheduler entry point — dispatches due domains
├── Contracts/
│   └── NotificationServiceInterface.php
├── Http/Controllers/
│   ├── AuthController.php
│   ├── DomainController.php
│   └── CheckHistoryController.php
├── Jobs/
│   └── CheckDomainJob.php          # Queued job — runs one domain check
├── Models/
│   ├── User.php
│   ├── Domain.php
│   └── CheckHistory.php
└── Services/
    ├── DomainCheckService.php       # HTTP check logic + notification trigger
    ├── EmailNotificationService.php
    ├── TelegramNotificationService.php
    └── NotificationServiceFactory.php
```

**Check flow:**

```
Scheduler (every minute)
  → CheckDomainsCommand   filters domains due by check_interval
    → CheckDomainJob       queued, retries up to 3×
      → DomainCheckService  performs HTTP request, saves CheckHistory
        → NotificationServiceFactory  sends Telegram alert if domain is down
```

---

## Installation

```bash
git clone <repo-url>
cd domains_hub

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate
```

Start all services in development:

```bash
composer run dev
```

This runs the Laravel server, queue worker, log watcher, and Vite dev server concurrently.

---

## Environment Variables

| Variable | Description |
|---|---|
| `DB_*` | Database connection |
| `MAIL_*` | Mail driver config (SMTP, Mailgun, etc.) |
| `MAIL_FROM_ADDRESS` | Sender address for email notifications |
| `TELEGRAM_BOT_TOKEN` | Bot token from [@BotFather](https://t.me/BotFather) |
| `QUEUE_CONNECTION` | `database` recommended for production |

---

## Running the Scheduler

In production, add a single cron entry:

```
* * * * * php /path/to/domains_hub/artisan schedule:run >> /dev/null 2>&1
```

The scheduler runs `domains:check` every minute. The command uses `withoutOverlapping()` so concurrent runs are safe.

---

## API Reference

All authenticated endpoints require the header:

```
Authorization: Bearer <token>
```

### Auth

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/register` | Register a new user |
| `POST` | `/api/login` | Login and receive a token |
| `POST` | `/api/logout` | Revoke current token |
| `GET` | `/api/user` | Get authenticated user |
| `PUT` | `/api/user` | Update name / email |

#### Register

```json
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```

Response `201`:
```json
{ "token": "...", "user": { ... } }
```

#### Login

```json
POST /api/login
{ "email": "john@example.com", "password": "secret123" }
```

Response `200`:
```json
{ "token": "...", "user": { ... } }
```

---

### Domains

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/domains` | List all domains for the authenticated user |
| `POST` | `/api/domains` | Add a new domain |
| `GET` | `/api/domains/{id}` | Get a single domain |
| `PUT` | `/api/domains/{id}` | Update a domain |
| `DELETE` | `/api/domains/{id}` | Delete a domain |

#### Domain fields

| Field | Type | Default | Description |
|---|---|---|---|
| `domain` | string | required | Hostname without protocol, e.g. `example.com` |
| `check_interval` | integer | `300` | Seconds between checks |
| `request_timeout` | integer | `30` | HTTP request timeout in seconds |
| `check_method` | string | `GET` | `GET` or `HEAD` |

---

### Check History

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/domains/{id}/history` | Paginated check history for a domain (50 per page) |

#### History record fields

| Field | Description |
|---|---|
| `status` | `up` or `down` |
| `http_code` | HTTP response code, or `null` on connection failure |
| `response_time_ms` | Round-trip time in milliseconds |
| `error` | Error message when status is `down`, otherwise `null` |
| `created_at` | Timestamp of the check |

---

## Notifications

When a domain check returns `down`, the owner is notified if the relevant channel is configured on their account.

### Telegram

1. Create a bot via [@BotFather](https://t.me/BotFather) and copy the token to `TELEGRAM_BOT_TOKEN`.
2. Set the user's `telegram_chat_id` (their personal or group chat ID).
3. Notifications are sent automatically — no additional configuration needed.

To update a user's Telegram chat ID via the API, send a `PUT /api/user` request with `telegram_chat_id` included.

### Email

Uses Laravel's mail system. Configure `MAIL_*` variables in `.env`. Notifications go to the user's registered email address.

---

## Artisan Commands

```bash
# Check all due domains immediately (bypasses queue, useful for testing)
php artisan domains:check

# Check a single domain by ID
php artisan domains:check --id=1
```