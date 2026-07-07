# Papohapo — University Student Virtual Elections System

A dockerized virtual elections platform for university student body elections
(SRC/faculty/class-rep style). PHP + MariaDB backend/frontend split, optional
Tailscale/Headscale-only exposure instead of public ports.

> Agent handoff: see `docs/CONTEXT.md` for current state, known gaps, and the
> phased roadmap. Read it before making changes.

## Architecture

```
                 ┌─────────────┐        ┌─────────────┐
   :8080 ──────▶ │  frontend   │ ─────▶ │   backend   │ ─────▶ MariaDB (db)
  (or tailnet)   │ php:8.3-apache      │ php:8.3-apache      internal network
                 └─────────────┘        └─────────────┘
                        │  session_data volume shared between the two
                 ┌─────────────┐
                 │ tailscale   │  optional profile, network_mode: service:frontend
                 │ (Headscale) │  exposes frontend over tailnet instead of host ports
                 └─────────────┘
```

- **db** — MariaDB 11, schema auto-loaded from `db/schema.sql` on first boot.
- **backend** — PHP endpoints (`auth.php`, `vote.php`, `import_csv.php`), no
  public routes rendered — pure API/action layer.
- **frontend** — PHP views (`login.php`, `dashboard.php`, `index.php`,
  `logout.php`) that POST to the backend via `BACKEND_ORIGIN`.
- **tailscale** — disabled by default (`--profile tailscale`); serves the
  frontend over your tailnet/Headscale instead of raw host ports 8080/8081.

## Domain model (`db/schema.sql`)

`faculties → users(role: ADMIN/CANDIDATE/VOTER/OBSERVER) → elections(scope:
GENERAL/FACULTY, status: DRAFT/ACTIVE/CLOSED) → positions → candidates →
votes`, plus an `audit_logs` table (currently unused — see gaps below).

Key constraints already enforced at the DB layer:
- One vote per `(voter_id, position_id)` — re-votes overwrite, not duplicate.
- Candidates are unique per `(user_id, position_id)`.
- Elections carry a `start_at`/`end_at` window and a `status`, both checked
  in `vote.php` before a vote is accepted.

## Local setup

```bash
cd student-election-system
cp .env.example .env        # fill in DB_PASSWORD, DB_ROOT_PASSWORD, etc.
docker compose up -d --build
# frontend: http://localhost:8080  backend: http://localhost:8081
```

To expose over Tailscale/Headscale instead of host ports:
```bash
docker compose --profile tailscale up -d
docker compose exec tailscale tailscale serve --bg 80
```

## Status

Functional skeleton: login, role-gated dashboard, single hardcoded vote
action. **Not yet a complete elections system** — admin can't create
elections/positions/candidates through the UI, there's no results/tally
view, and ballot secrecy isn't yet enforced at the data layer. Full gap
list and roadmap: `docs/CONTEXT.md`.
