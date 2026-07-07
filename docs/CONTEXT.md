# Papohapo — Agent Handoff Context

Last inspected: 2026-07-07. Read this before touching code — it's the source
of truth for what's real vs. stubbed, and the order to build in.

## What actually works today

- `POST backend/auth.php` — email+password login, session cookie, redirect
  to `frontend/dashboard.php`. Only `active=1` users can log in.
- `backend/rbac.php` — `require_role([...])` guard used by `vote.php` and
  `import_csv.php`.
- `backend/vote.php` — accepts a vote for a given `position_id`, but only if
  the parent election is `ACTIVE` and inside `[start_at, end_at]`. Upserts
  via `ON DUPLICATE KEY`.
- `backend/import_csv.php` — admin-only bulk user import from CSV
  (`name,email,role`), all seeded with password `changeme`.
- `frontend/{login,dashboard,index,logout}.php` — minimal role-branching
  views, no styling beyond `assets/style.css`.
- Docker Compose: `db` (MariaDB 11, schema auto-loaded), `backend`,
  `frontend`, optional `tailscale` profile.

## Known gaps, in priority order

Election legitimacy depends on Phase 0 more than any feature — build in
this order, don't skip ahead to nicer UI first.

### Phase 0 — Election integrity (do this first)
1. **Ballot secrecy is broken.** `votes.voter_id` is a direct FK to
   `users.id`, so any DB access (or the ADMIN role) can see exactly who
   voted for whom. Real elections need voter identity separated from vote
   content. Approach: split into `ballots_cast(voter_id, position_id,
   cast_at)` (proves *that* someone voted, for turnout/audit) and
   `votes(anonymous_token, position_id, candidate_id)` where
   `anonymous_token` is a one-time random token issued at vote time with no
   stored link back to `voter_id`. This is the single most important
   architectural fix.
2. **`audit_logs` table is never written to.** Every state-changing action
   (login, vote cast, election status change, CSV import) should insert a
   row. Needed for post-election disputes/appeals.
3. **CSV-imported users all get password `changeme`.** This is a live
   credential vulnerability the moment the CSV is imported before students
   log in. Replace with: random per-user temp password + forced
   reset-on-first-login flow, or a magic-link invite instead of a password
   at all.
4. **No CSRF protection** on `vote.php` or `auth.php` forms.
5. **No login rate-limiting/lockout** — `auth.php` has no brute-force
   protection.

### Phase 1 — Core election management (admin can't do their job yet)
- No UI/endpoints to create/edit an `election`, its `positions`, or attach
  `candidates` to a position. Right now this only exists as raw SQL against
  `schema.sql`. Needs an admin CRUD flow: create election → set scope
  (GENERAL/FACULTY) → add positions (with `max_votes`, `allows_blank`) →
  attach candidates (with manifesto + photo) → transition DRAFT → ACTIVE →
  CLOSED.
- `frontend/index.php` hardcodes `position_id=1` — needs to dynamically
  list the active election's positions and candidates instead.

### Phase 2 — Voting UX
- Candidate cards (photo, manifesto) per position instead of a single
  "Blank Vote" button.
- Multi-position ballot in one flow (loop over all positions in the active
  election a voter is eligible for, respecting `FACULTY`-scoped elections
  matching the voter's `faculty_id`).
- Clear "you already voted for X" state after a vote is cast.

### Phase 3 — Results & audit
- `dashboard.php` links to `import.php` and `reports.php` — **neither
  file exists yet in `frontend/`.** `reports.php` should show live/closed
  tallies per position (count `votes` grouped by `candidate_id`), gated to
  ADMIN and OBSERVER roles. Observers should be read-only.
- Export results (CSV/PDF) for official record-keeping.
- Tie-break rule needs a documented policy (not yet decided).

### Phase 4 — Access & identity
- Enforce `FACULTY`-scoped election eligibility server-side (a FACULTY
  election should reject voters outside that faculty even if they guess
  the `position_id`).
- Password reset flow (currently none — `changeme` is permanent until an
  admin manually updates it).

### Phase 5 — Infra hardening
- Prefer the `tailscale` profile over publishing `8080`/`8081` on the host
  for anything beyond local dev — reduces attack surface for a system
  handling real votes.
- Docker healthchecks on `db`/`backend`/`frontend`.
- DB backup strategy for `db_data` volume (election data must be
  recoverable — there is currently none).
- HTTPS termination if ever exposed off-tailnet.

### Phase 6 — Stretch
- Email/SMS notifications on election open/close.
- i18n (English/Kiswahili) for frontend copy.
- Accessibility pass on frontend views.

## Working agreements

- `.env` is gitignored and **must stay that way** — never commit real
  `DB_PASSWORD`/`TS_AUTHKEY` values. `.env.example` is the template.
- `docker-compose.yml` env vars all have safe fallbacks except
  `DB_PASSWORD`/`DB_ROOT_PASSWORD`/`TS_AUTHKEY`, which must come from `.env`.
- Schema changes go in `db/schema.sql` (no migration tool yet — consider
  adding one, e.g. `phinx` or plain numbered `.sql` files, once Phase 0/1
  land and the schema starts changing more often).
