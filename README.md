# Auction Platform

## Description
An online auction platform built with modern web technologies, allowing users to create, bid on, and manage auctions.

## Features
- User authentication and authorization
- Create and manage auction listings
- Real-time bidding system
- User roles (Admin and Bidder)
    - Admin: Manage users and auctions
    - Bidder: Place bids

## Prerequisites
- PHP >= 8.1
- MySQL/MariaDB
- Composer
- Node.js & NPM

## Installation
1. Clone the repository:
```bash
git clone https://github.com/shefiMon/auction-platform.git
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Configure environment variables:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run database migrations:
```bash
php artisan migrate
```

7. Start the development server:
```bash
php artisan serve
npm run dev
```

8. Configure Pusher for real-time features in your `.env` file:
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_secret_key
PUSHER_APP_CLUSTER=your_cluster
```

9. Install WebSocket dependencies:
```bash
npm install --save-dev laravel-echo pusher-js
```

10. Enable Broadcasting in `config/app.php`:
```php
'providers' => [
    // ...
    App\Providers\BroadcastServiceProvider::class,
],
```

11. Seed the database:
```bash
php artisan db:seed
```

## Test Accounts
```
Bidder: bidder@auction.com / password
Admin: admin@auction.com / password
```

## Scheduled Tasks
Add to `App\Console\Kernel.php`:
```php
php artisan schedule:work       
protected function schedule(Schedule $schedule)
{
    $schedule->command('app:activate-auction')->everyMinute();
    $schedule->command('app:mark-as-completed')->everyMinute();
    
}
```

Run the scheduler:
```bash
php artisan schedule:work
```

## Usage
Visit `http://localhost:8000` in your web browser.
