<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## MemberFlow accounts

For a fresh clone on another computer, install PHP 8.2 or later (with MySQL
and SQLite PDO extensions), Composer, MySQL, and Mailpit. In the project folder:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Copy the template only on a fresh setup; keep an existing configured `.env`.
Create the `tw31_tasks` database (or choose another name), then set the matching
`DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in your local `.env`.
Run `php artisan config:clear` after changing configuration.

After pulling these changes, run `php artisan migrate`. On a fresh database,
also run `php artisan db:seed` to populate membership categories and levels.

Start MySQL and Mailpit, then run `php artisan serve` and
`php artisan queue:work` in separate terminals. The current pages use the
committed public CSS and Leaflet CDN assets. If you use the Vite assets, run
`npm install` followed by `npm run build` on the new computer.

The real `.env`, database contents, private keys, logs, sessions, uploads,
and generated caches stay outside Git. `.env.example`, dependency lockfiles,
migrations, seeders, views, and public styles stay in Git so the application
can be set up again. Existing accounts and uploaded files require a separate
private transfer if you want to retain them on a different database.
For a public deployment, set `APP_ENV=production` and `APP_DEBUG=false` in
the server's private environment configuration.

Open `/login` to choose between **Admin login** and **Member login**. New members
use `/signup`, which includes the membership application, category and level
selection, location map, and weather. `/membership/apply` redirects to signup.

Create your first administrator locally:

```powershell
php artisan app:create-admin
```

The command asks for a name, email, and password with a hidden password prompt.
There is no public administrator signup or default administrator password.

To give an existing member a login while retaining their membership and
transaction history, use their member ID from the admin member page:

```powershell
php artisan app:create-member-login 1
```

Replace `1` with the correct member ID. Set the credentials privately on the
local computer; do not put passwords in Git or shell command arguments.

Administrators manage the overview, members, categories, levels, tasks,
transactions, and payments. Members can view shared tasks and only their own
transactions and payments. Admin routes enforce the role on the server.
Public signup always creates a member account with a pending membership and
application transaction, using the selected membership level's fee. It does
not collect a payment or automatically activate membership. Existing members
cannot have their records claimed through public signup by email.

Signup queues a branded verification email with the member's name, application
reference, a **Verify email address** button, and a copyable fallback URL.
Links expire after 24 hours and can be opened while logged out. They confirm
email ownership without logging the visitor in, approving membership, or
recording a payment. Members can resend the email from their membership page
(limited to three requests per ten minutes).

The example environment uses Mailpit for local testing. For delivery to real
inboxes through Gmail, set the following in the ignored local `.env` file,
using your actual sender address and a Google App Password:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-sender@gmail.com
MAIL_PASSWORD="your-google-app-password"
MAIL_FROM_ADDRESS=your-sender@gmail.com
MAIL_FROM_NAME="MemberFlow"
QUEUE_CONNECTION=database
APP_URL=http://127.0.0.1:8000
```

Create an App Password using Google's instructions:
https://support.google.com/accounts/answer/185833
Never commit real credentials or paste them into chat. For links opened on
another device, `APP_URL` must be the reachable address of your running app;
`127.0.0.1` works only on the computer running the app. Use your HTTPS domain
when deployed.

After changing `.env`, run `php artisan config:clear`, restart any existing
queue worker, and run `php artisan queue:work`. Keep the worker running to
deliver signup and resend emails. With Gmail SMTP, Mailpit is not needed.
The application cannot deliver to Gmail while SMTP credentials are blank.

Run `php artisan test` to check authentication, access rules, and the membership
and weather features using SQLite in memory.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
