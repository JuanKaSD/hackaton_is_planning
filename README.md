# hackaton_is_planning



# hackaton_is_planning - Backend API

1. Clone the repository:

```bash
git clone <repository-url>
cd hackaton_is_planning/backend
```

2. Install dependencies:

```bash
composer install
```

3. Copy the `.env.example` file to `.env` and configure your environment variables:

```bash
cp .env.example .env
```

4. Generate the application key:

```bash
php artisan key:generate
```

5. Configure the database in the `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations to create the necessary tables:

```bash
php artisan migrate
```

7. (Optional) Run seeders to populate the database with initial data:

```bash
php artisan db:seed
```

8. Start the development server:

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## Authentication

The API uses Laravel Sanctum for token-based authentication. To authenticate:

1. Register a user or log in to get a token.
2. Include the token in your request headers:
    ```
    Authorization: Bearer {your-token}
    ```

## API Routes

### Public Routes

These routes don't require authentication:

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| POST | `/api/auth/register` | `UserController@store` | Register a new user |
| POST | `/api/auth/login` | `UserController@login` | Log in and return a token |
| POST | `/api/register` | `AuthController@register` | Register a new user (alternative) |
| POST | `/api/login` | `AuthController@login` | Log in (alternative) |

### Protected Routes

These routes require authentication (valid token):

#### Users

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/users` | `UserController@index` | List all users |
| GET | `/api/users/{id}` | `UserController@show` | Get details of a specific user |
| PUT | `/api/users/{user}/edit` | `UserController@update` | Update user data |
| DELETE | `/api/users/{id}` | `UserController@destroy` | Delete a user |
| POST | `/api/auth/logout` | `UserController@logout` | Log out (invalidate token) |
| POST | `/api/logout` | `AuthController@logout` | Log out (alternative) |
| GET | `/api/user` | `AuthController@user` | Get current user information |

#### Airlines

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/airlines` | `AirlineController@index` | List all airlines |
| GET | `/api/airlines/{airline}` | `AirlineController@show` | Get details of a specific airline |
| POST | `/api/airlines` | `AirlineController@store` | Create a new airline |
| PUT | `/api/airlines/{airline}` | `AirlineController@update` | Update an existing airline |
| DELETE | `/api/airlines/{airline}` | `AirlineController@destroy` | Delete an airline |

### Enterprise Routes

These routes require authentication and the `enterprise` middleware:

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/enterprise/airlines` | `AirlineController@index` | List airlines belonging to the authenticated user's company |

## Usage Examples

### User Registration

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Example User","email":"user@example.com","password":"password","password_confirmation":"password"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

Response:
```json
{
  "access_token": "1|example-token-string",
  "token_type": "Bearer"
}
```

### Get User Profile

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|example-token-string"
```

### List Airlines

```bash
curl -X GET http://localhost:8000/api/airlines \
  -H "Authorization: Bearer 1|example-token-string"
```

### Create a New Airline

```bash
curl -X POST http://localhost:8000/api/airlines \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|example-token-string" \
  -d '{"name":"New Airline","code":"NA","description":"Airline description"}'
```

## Database Structure

The API primarily uses the following models:

- `User`: Application users
- `Airline`: Airlines registered in the system

## Middlewares

The API uses the following middlewares:

- `auth:sanctum`: For token-based authentication
- `enterprise`: For enterprise-specific routes

## Additional Notes

- The API is configured with rate limiting (60 requests per minute) to prevent abuse.
- Responses are in JSON format.
- For validation errors, check the messages in the response.

## Development

To run tests:

```bash
php artisan test
```

For more information, see the [Laravel documentation](https://laravel.com/docs) and [Laravel Sanctum](https://laravel.com/docs/sanctum).
