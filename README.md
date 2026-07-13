# Shopping Cart

A minimal, production-style shopping cart in plain PHP + MySQL. It shows a
product list, a cart summary with per-line totals, subtotal, GST (5%),
QST (9.975%) and grand total. Quantities can be adjusted (stepper, direct
input, or remove) and every change is persisted to the database immediately.

No framework — the only "dependencies" are the official `php:8.3-apache` and
`mysql:8.0` Docker images, which stay fully separate from the source code.

## Requirements

- Docker (with Compose v2)

## Installation

```bash
git clone <this repo>
cd fortnine-test
bin/start          # builds + starts the containers
```

Open http://localhost:8090. MySQL is seeded automatically from `db/init.sql`
on first start.

Helper scripts (markshust-style):

| Script      | Purpose                                  |
|-------------|------------------------------------------|
| `bin/start` | Build and start the app + database        |
| `bin/stop`  | Stop the containers                       |
| `bin/mysql` | Open a MySQL shell on the `cart` database |
| `bin/cli`   | Run a command in the app container, e.g. `bin/cli php -v` |

To reset the database: `docker compose down -v && bin/start`.

## How it works

- **State** — each browser gets a `cart_token` cookie (random 256-bit,
  HttpOnly). The token maps to a row in `carts`; items live in `cart_items`
  with a unique key on `(cart_id, product_id)`. The cart survives browser
  restarts, server restarts, and works without any items being kept in the
  session.
- **Money** — prices are stored and computed as integer cents to avoid
  floating-point drift; taxes are rounded per Canadian practice.
- **API** — `src/public/api.php` is a small JSON endpoint
  (`GET` = cart summary, `POST` = `add` / `set_qty`, where qty `0` removes the
  line). Writes are CSRF-protected with a session token.
- **Frontend** — the page is server-rendered, then `assets/cart.js`
  (vanilla JS, no build step) re-renders the cart section from the JSON the
  API returns after each change.

```
├── docker-compose.yml   # app (php:8.3-apache) + db (mysql:8.0)
├── Dockerfile           # adds pdo_mysql, points Apache at src/public
├── db/init.sql          # schema + seed products
├── bin/                 # helper scripts
└── src/
    ├── app/             # Database.php, Cart.php, bootstrap.php
    └── public/          # index.php, api.php, assets/ (docroot)
```

## Tools used for development

- PHP 8.3 (PDO / prepared statements), MySQL 8.0
- Vanilla JavaScript, HTML, CSS (no build tooling)
- Docker Compose for the local environment
- Git / GitHub

## Online demo

See the repository description for the demo URL.
