# Event Booking API (Laravel)

A small REST API for events, tickets, bookings, payments, RBAC, notifications (queued), and caching.

## Features
- Auth: register, login, logout (`Sanctum` tokens)
- Roles: `admin`, `organizer`, `customer` (middleware protected)
- CRUD: Events, Tickets
- Bookings: create/list/delete (duplicate and stock checks)
- Payments: mock `PaymentService` + idempotent pay endpoint
- Notifications: `PaymentReceived` stored in DB and **queued**
- Caching: events list cached (auto-bust on writes)
- Seeded data per spec

## Tech
Laravel + Sanctum + SQLite + Database queue driver

---

## 1) Install

```bash
git clone <your-repo>
cd event-booking
composer install
cp .env.example .env
```

Edit `.env` (Windows absolute path shown as example):

```
APP_KEY=                # leave empty; generated in next step

DB_CONNECTION=sqlite
DB_DATABASE=C:\Users\user\OneDrive\Bureau\AutomatedPros\event-booking\database\database.sqlite
DB_FOREIGN_KEYS=true

QUEUE_CONNECTION=database
```

Create the SQLite file if missing:

```bash
cd database && type NUL > database.sqlite && cd ..
```

---

## 2) Key, Migrate, Seed

```bash
php artisan key:generate
php artisan migrate --seed
```

**Seeded data (as required):** 2 admins, 3 organizers, 10 customers, 5 events, 15 tickets, 20 bookings.

---

## 3) Serve & Queue

Start the API:

```bash
php artisan serve    # http://127.0.0.1:8000
```

Prepare queue tables (first time only) then run worker:

```bash
php artisan queue:table
php artisan migrate   # this adds jobs tables
php artisan queue:work
```

> Keep `queue:work` running to process queued notifications.

---

## 4) API Summary

All routes are under `/api`.

Auth:
- `POST /register` — name, email, password, phone
- `POST /login` — returns token
- `GET /me` — current user (Bearer token)
- `POST /logout` — revoke token

Events (auth required):
- `GET /events` — list (filters: `q`, `from`, `to`, `sort`, `dir`, `per_page`, `page`)
- `GET /events/{event}`
- `POST /events` — organizer/admin
- `PUT /events/{event}` — event owner (organizer) or admin
- `DELETE /events/{event}` — admin

Tickets:
- `GET /events/{event}/tickets` — list for event
- `POST /events/{event}/tickets` — owner/admin
- `GET /tickets/{ticket}`
- `PUT /tickets/{ticket}` — owner/admin
- `DELETE /tickets/{ticket}` — owner/admin

Bookings:
- `POST /bookings` — any authed user (prevents duplicate for same ticket/user)
- `GET /bookings` — mine; `?all=1` for admin lists all
- `DELETE /bookings/{booking}` — mine (or admin)

Payments:
- `POST /bookings/{booking}/pay` — creates `payments` row, sets booking `confirmed`, enqueues `PaymentReceived` notification

Caching:
- `GET /events` is cached for 60s
- Creating/updating/deleting events or tickets **flushes** the events cache

---

## 5) Curl Cheatsheet

### Register
```bash
curl -X POST "http://127.0.0.1:8000/api/register" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password\",\"phone\":\"+971500000000\"}"
```

### Login (copy `token`)
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"email\":\"test@example.com\",\"password\":\"password\"}"
```

### Me
```bash
curl -X GET "http://127.0.0.1:8000/api/me" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

### Events
List with filters:
```bash
curl -X GET "http://127.0.0.1:8000/api/events?q=Dubai&from=2025-12-01&to=2025-12-31&sort=title&dir=asc&per_page=5" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

Create (organizer/admin):
```bash
curl -X POST "http://127.0.0.1:8000/api/events" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"title\":\"AI Meetup Dubai\",\"description\":\"Evening talks\",\"date\":\"2025-12-10 19:00:00\",\"location\":\"Internet City\"}"
```

Update (owner/admin):
```bash
curl -X PUT "http://127.0.0.1:8000/api/events/EVENT_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"location\":\"DWTC Hall 2\"}"
```

Delete (admin):
```bash
curl -X DELETE "http://127.0.0.1:8000/api/events/EVENT_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

### Tickets
List by event:
```bash
curl -X GET "http://127.0.0.1:8000/api/events/EVENT_ID/tickets" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

Create (owner/admin):
```bash
curl -X POST "http://127.0.0.1:8000/api/events/EVENT_ID/tickets" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"type\":\"VIP\",\"price\":199.99,\"quantity\":100}"
```

Update / Delete (owner/admin):
```bash
curl -X PUT "http://127.0.0.1:8000/api/tickets/TICKET_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"price\":149.99}\"

curl -X DELETE "http://127.0.0.1:8000/api/tickets/TICKET_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

### Bookings
Create:
```bash
curl -X POST "http://127.0.0.1:8000/api/bookings" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d "{\"ticket_id\":TICKET_ID,\"quantity\":2}"
```

Mine:
```bash
curl -X GET "http://127.0.0.1:8000/api/bookings" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

All (admin):
```bash
curl -X GET "http://127.0.0.1:8000/api/bookings?all=1" \
  -H "Authorization: Bearer ADMIN_TOKEN" -H "Accept: application/json"
```

Delete:
```bash
curl -X DELETE "http://127.0.0.1:8000/api/bookings/BOOKING_ID" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

### Payment + Queued Notification
```bash
curl -X POST "http://127.0.0.1:8000/api/bookings/BOOKING_ID/pay" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```
> Run `php artisan queue:work` to process the queued `PaymentReceived` notification.  
> Check latest notification in Tinker:
> ```php
> php artisan tinker
> \App\Models\User::where('email','test@example.com')->first()->notifications()->latest()->first();
> ```

### Logout
```bash
curl -X POST "http://127.0.0.1:8000/api/logout" \
  -H "Authorization: Bearer YOUR_TOKEN" -H "Accept: application/json"
```

---

## 6) Postman Collection (import JSON)

Open Postman → **Import** → **Raw text** → paste:

```json
{
  "info": { "name": "Event Booking API", "_postman_id": "e4f1f1b8-aaaa-bbbb-cccc-collection", "description": "Auth, Events, Tickets, Bookings, Payments", "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json" },
  "variable": [
    { "key": "baseUrl", "value": "http://127.0.0.1:8000" },
    { "key": "token",  "value": "" },
    { "key": "eventId","value": "1" },
    { "key": "ticketId","value": "" },
    { "key": "bookingId","value": "" }
  ],
  "item": [
    { "name": "Auth - Register", "request": { "method": "POST", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" } ], "url": { "raw": "{{baseUrl}}/api/register", "host": ["{{baseUrl}}"], "path": ["api","register"] }, "body": { "mode": "raw", "raw": "{\\"name\\":\\"Test User\\",\\"email\\":\\"test@example.com\\",\\"password\\":\\"password\\",\\"phone\\":\\"+971500000000\\"}" } } },
    { "name": "Auth - Login", "request": { "method": "POST", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" } ], "url": { "raw": "{{baseUrl}}/api/login", "host": ["{{baseUrl}}"], "path": ["api","login"] }, "body": { "mode": "raw", "raw": "{\\"email\\":\\"test@example.com\\",\\"password\\":\\"password\\"}" } } },
    { "name": "Auth - Me", "request": { "method": "GET", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/me", "host": ["{{baseUrl}}"], "path": ["api","me"] } } },
    { "name": "Events - List (filters)", "request": { "method": "GET", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events?q=Dubai&from=2025-12-01&to=2025-12-31&sort=title&dir=asc&per_page=5", "host": ["{{baseUrl}}"], "path": ["api","events"], "query":[ {"key":"q","value":"Dubai"},{"key":"from","value":"2025-12-01"},{"key":"to","value":"2025-12-31"},{"key":"sort","value":"title"},{"key":"dir","value":"asc"},{"key":"per_page","value":"5"} ] } } },
    { "name": "Events - Create", "request": { "method": "POST", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events", "host": ["{{baseUrl}}"], "path": ["api","events"] }, "body": { "mode": "raw", "raw": "{\\"title\\":\\"AI Meetup Dubai\\",\\"description\\":\\"Evening talks\\",\\"date\\":\\"2025-12-10 19:00:00\\",\\"location\\":\\"Internet City\\"}" } } },
    { "name": "Events - Update", "request": { "method": "PUT", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events/{{eventId}}", "host": ["{{baseUrl}}"], "path": ["api","events","{{eventId}}"] }, "body": { "mode": "raw", "raw": "{\\"location\\":\\"DWTC Hall 2\\"}" } } },
    { "name": "Events - Delete (admin)", "request": { "method": "DELETE", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events/{{eventId}}", "host": ["{{baseUrl}}"], "path": ["api","events","{{eventId}}"] } } },
    { "name": "Tickets - List by Event", "request": { "method": "GET", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events/{{eventId}}/tickets", "host": ["{{baseUrl}}"], "path": ["api","events","{{eventId}}","tickets"] } } },
    { "name": "Tickets - Create", "request": { "method": "POST", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/events/{{eventId}}/tickets", "host": ["{{baseUrl}}"], "path": ["api","events","{{eventId}}","tickets"] }, "body": { "mode": "raw", "raw": "{\\"type\\":\\"VIP\\",\\"price\\":199.99,\\"quantity\\":50}" } } },
    { "name": "Bookings - Create", "request": { "method": "POST", "header": [ { "key": "Content-Type", "value": "application/json" }, { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/bookings", "host": ["{{baseUrl}}"], "path": ["api","bookings"] }, "body": { "mode": "raw", "raw": "{\\"ticket_id\\":{{ticketId}},\\"quantity\\":1}" } } },
    { "name": "Bookings - Mine", "request": { "method": "GET", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/bookings", "host": ["{{baseUrl}}"], "path": ["api","bookings"] } } },
    { "name": "Payments - Pay Booking", "request": { "method": "POST", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/bookings/{{bookingId}}/pay", "host": ["{{baseUrl}}"], "path": ["api","bookings","{{bookingId}}","pay"] } } },
    { "name": "Auth - Logout", "request": { "method": "POST", "header": [ { "key": "Accept", "value": "application/json" }, { "key": "Authorization", "value": "Bearer {{token}}" } ], "url": { "raw": "{{baseUrl}}/api/logout", "host": ["{{baseUrl}}"], "path": ["api","logout"] } } }
  ]
}
```

After importing, set environment variables:
- `baseUrl = http://127.0.0.1:8000`
- `token = <paste from Login>`
- Set `eventId`, `ticketId`, `bookingId` as you create them.

---

## 7) Testing (per spec)

Recommended coverage:
- **Feature tests:** Registration, Login, Event create, Ticket booking (no duplicates, stock check), Payment flow (booking→pay→confirmed).
- **Unit test:** `PaymentService::charge()` returns `{ ok: true, txn: "TXN-..." }` for positive amounts.

Run all:
```bash
php artisan test
```

---

## Troubleshooting

- **`ECONNREFUSED`** in Postman: start server `php artisan serve`.
- **`Could not open input file: artisan`**: run commands from the **project root** (where `artisan` exists).
- **`Unauthenticated.`**: missing/expired token → login again and pass `Authorization: Bearer <token>`.
- **Notifications not created**: make sure `php artisan queue:work` is running and `QUEUE_CONNECTION=database` + jobs tables migrated.
