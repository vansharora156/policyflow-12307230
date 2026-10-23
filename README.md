# PolicyFlow Assignment Submission

Campus Placement Software Engineering Practical Assessment

### Student Submission
- **Project**: PolicyFlow
- **Framework**: Laravel 11 (PHP 8.3)
- **Database**: SQLite

### Setup Commands
```bash
composer install --no-audit
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test
```

### Endpoints
- `POST /api/policy-requests` : Create policy request
- `GET /api/policy-requests/{id}` : View policy request
- `POST /api/policy-requests/{id}/submit` : Submit policy to mock insurer
- `GET /api/policy-requests` : List requests

### Notes
Basic implementation covering core intake and pricing calculations.
