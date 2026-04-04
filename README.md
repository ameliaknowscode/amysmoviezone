# Amy's Movie Zone

A personal movie logging app built with Laravel. Track films you've watched, log diary entries, rate and review movies, maintain a watchlist, and follow friends to see their activity.

Built as a hands-on learning project for both Laravel and [Claude Code](https://claude.ai/code).

## Features

- Movie catalog with cast and crew credits
- Personal diary — log watches with dates and written reviews
- Star ratings and liked/disliked tracking
- Watchlist (want to watch / watched)
- Social feed — follow other users and see their activity
- Custom movie lists (ranked or unranked)
- Director Connections — explore shared cast members for multiple directors
- Admin panel for managing movies, people, and credits

## Tech Stack

- **PHP 8.2** / **Laravel 12**
- **MySQL**
- **Tailwind CSS** / **Alpine.js** / **Vite**
- **Laravel Queues** for background notifications

## Local Setup

**Requirements:** PHP 8.2+, Composer, Node.js, MySQL

```bash
git clone https://github.com/ameliaknowscode/amysmoviezone.git
cd amysmoviezone

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_CONNECTION=mysql
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

```bash
php artisan migrate
npm run build
php artisan serve
```

The app will be available at `http://localhost:8000`.

**Optional:** Seed fake users and activity data (requires movies to already exist in the database):

```bash
php artisan db:seed
```

## Queue Worker

Email notifications are dispatched via Laravel Queues. To process them locally:

```bash
php artisan queue:work
```

## License

MIT
