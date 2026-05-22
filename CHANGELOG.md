# Changelog

All notable changes to Amy's Movie Zone are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] — 2026-05-22

### Fixed

- **Rewatch detection is now chronology-based.** Backdating a watch correctly flags it as the original (not the rewatch); deleting the first watch promotes the surviving entry to be the original; editing `watched_at` re-derives the flags across the (user, movie) group. Owned by a `ReviewObserver`; legacy data brought in line by a backfill migration.
- **Credit CSV import no longer creates junk Type rows** from column-shifted TMDb-shaped exports. Unknown types produce a row-level error instead of silently joining the canonical list. Existing junk rows reassigned to "Actor" and removed.

### Changed

- **Public registration disabled.** New accounts now happen via admin invitation only.

### Developer Experience

- `DemoUserSeeder` — fixed-data seed for a screenshot-ready watch diary (`php artisan db:seed --class=DemoUserSeeder --force`). Idempotent; not auto-wired into `DatabaseSeeder`.
- One-shot data-cleanup migrations: junk credit types removed; `reviews.is_rewatch` backfilled from chronology.

## [1.0.0] — 2026-04-29

The initial public release.

### Catalog & Movies

- Movie pages with cast/crew credits, synopsis, runtime, country, language, IMDb and Letterboxd links
- Genre taxonomy with browsable genre pages and bulk CSV import for admins
- Person pages with bio, photo, birth/death dates, nationality, and full filmography
- Half-star ratings (decimal) with a click-or-mousemove picker
- Filtered browse — title search + director, genre, and year-range filters
- "More from this director" section on every movie page (up to six films, best-rated first)
- Slug uniqueness via release year, preventing collisions across films sharing a title

### Diary & Logging

- Personal diary — log watches with dates, written reviews, and spoiler warnings
- Rewatch detection — second and subsequent logs auto-flagged, surfaced on the diary and movie pages
- Watchlist (want-to-watch / watched) with watch-date recording
- Star ratings and liked/disliked tracking per movie

### Discovery

- Recommendations engine — taste profile (top genres + directors) plus three independent buckets: genre-based, director-based, and collaborative filtering
- Director Connections — explore shared cast members across multiple directors
- Head-to-Head — side-by-side director comparison: film counts, average ratings, career span, decade activity, top genres, shared collaborators, full filmographies
- Collections — admin-curated themed groupings (e.g. "Criterion Collection", "A24 Films") with browsable discovery pages, poster strip previews, drag-and-drop ordering, and search-add on the edit page

### Social

- Following users with a directory and follower/following pages
- Activity feed of friends' ratings, reviews, and watchlist entries
- Custom movie lists (ranked or unranked) with list following
- Notifications — review-liked, user-followed, list-item-added, shared-log, review-commented; in-app and email
- Comment threads on any review
- Watch parties — co-watched detection surfaces an amber chip on diary entries when a followed user logged the same film on the same night; movie pages show a watch party banner when two or more friends share a watched date
- Privacy settings — public profile / private profile / privacy-aware activity surfacing

### Personal Stats

- Per-user `/stats` page — films by decade, by year watched, top genres, top directors, rating distribution
- Year in Review at `/year-review/{year}` — total films, hours watched, average rating, rewatch count, favourite film, month-by-month chart, top genres, top directors, rating distribution, decade breakdown, and per-year navigation
- **Directors Discovered** — first-ever watch of a director's work in a given year, with the introducing film, ordered chronologically

### Admin

- Admin dashboard with content management for movies, people, genres, types, collections, and credits
- AJAX autocomplete on movie credit forms (replacing the legacy person `<select>`)
- Bulk genre CSV import
- Movie / credit / person CSV import flows

### Authentication & Onboarding

- Email-verified registration with username
- Onboarding flow for new users
- Email notifications with per-user opt-in/out

### UI / UX / Accessibility

- Dark cinematic theme — zinc/amber palette across every view, blurred poster backdrops
- CSS component layer (`.card`, `.btn-amber`, `.input-dark`) and reusable Blade components (`<x-movie-poster-card>`, `<x-review-card>`, `<x-movie-star-rating>`, `<x-movie-watchlist-actions>`)
- Alpine.js components extracted to dedicated JS files
- Accessibility audit — `:focus-visible` outline, skip link + `<main>` landmark; ARIA on modals, search dialog, and flash component; star rating rebuilt as a radio-group with arrow-key support; spoiler reveal as an accessible button; per-page `<title>` mechanism; full WAI-ARIA tab pattern on welcome activity and movie show Cast/Crew sections; AA-passing contrast pass

### Performance

- Eager loading and `withCount` across hot paths to avoid N+1
- Query result caching (rating stats, watchlist counts, follower counts, profile stats) with explicit busting from mutating controllers
- Background queue dispatch for notifications
- Composite database indexes on hot query patterns, including `reviews(user_id, watched_at)` for diary, Year in Review, and Directors Discovered
- SortableJS bundled via npm rather than CDN

### Developer Experience

- Full test suite (~670 tests) covering controllers, models, actions, and notifications
- SQLite-portable controller queries (no MySQL-only `YEAR()` or `HAVING` without `GROUP BY`)
- Factories for every model
- `php artisan dc:export` command for generating Director Connections data exports

### Tech Stack

- PHP 8.3, Laravel 13, MySQL
- Tailwind CSS, Alpine.js, Vite
- Laravel Queues for background notifications
