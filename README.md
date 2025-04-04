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

#### Airports

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/airports` | `AirportController@index` | List all airports |
| GET | `/api/airports/{airport}` | `AirportController@show` | Get details of a specific airport |
| POST | `/api/airports` | `AirportController@store` | Create a new airport |
| PUT | `/api/airports/{airport}` | `AirportController@update` | Update an existing airport |
| DELETE | `/api/airports/{airport}` | `AirportController@destroy` | Delete an airport |

#### Airplanes

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/airplanes` | `AirplaneController@index` | List all airplanes |
| GET | `/api/airplanes/{airplane}` | `AirplaneController@show` | Get details of a specific airplane |
| POST | `/api/airplanes` | `AirplaneController@store` | Create a new airplane |
| PUT | `/api/airplanes/{airplane}` | `AirplaneController@update` | Update an existing airplane |
| DELETE | `/api/airplanes/{airplane}` | `AirplaneController@destroy` | Delete an airplane |

#### Flights

| Method | Route | Controller | Description |
|--------|------|------------|-------------|
| GET | `/api/flights` | `FlightController@index` | List all flights |
| GET | `/api/flights/{flight}` | `FlightController@show` | Get details of a specific flight |
| POST | `/api/flights` | `FlightController@store` | Create a new flight |
| PUT | `/api/flights/{flight}` | `FlightController@update` | Update an existing flight |
| DELETE | `/api/flights/{flight}` | `FlightController@destroy` | Delete a flight |

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

# Frontend Application

The frontend is built with Next.js and provides a modern user interface for interacting with the API.

## Getting Started

1. Navigate to the frontend directory:

```bash
cd frontend
```

2. Install dependencies:

```bash
npm install
```

3. Start the development server:

```bash
npm run dev
```

The application will be available at `http://localhost:3000`.

## Pages

### Public Pages
- `/` - Home page with features overview and authentication options
- `/login` - User login page
- `/signup` - User registration page with account type selection

### Protected Pages
- `/dashboard` - Enterprise management dashboard (enterprise users only)
- `/profile` - User profile management
- `/flights` - Flight booking interface (regular users)

## Components

### Layout Components
- `Navbar` - Main navigation component with dynamic user menu
- `DashboardTabs` - Tab system for enterprise dashboard

### Form Components
- `Dropdown` - Reusable dropdown with search functionality
- `EditAirlineModal` - Modal for editing airline details

### Dashboard Components
- `AirlineTab` - Airline management interface
- `FlightTab` - Flight management interface with CRUD operations

## Contexts

### Authentication
- `AuthContext` - Manages user authentication state and operations
- Methods:
  - `login()`
  - `signup()`
  - `logout()`
  - `updateUser()`

### Data Management
- `AirlineContext` - Airline data management
- `FlightContext` - Flight operations and state
- `AirportContext` - Airport data and operations

## Styles

### Global Styles
- Color scheme with light/dark mode support
- Responsive layout system
- Typography using Geist font family

### Module Styles
- `Auth.module.css` - Authentication forms styling
- `Dashboard.module.css` - Dashboard layout and components
- `Navbar.module.css` - Navigation styling
- `Modal.module.css` - Modal components
- `Tabs.module.css` - Tab system styling
- `Dropdown.module.css` - Custom dropdown component

## Theme Variables

```css
:root {
  --background: #f8fafc;
  --foreground: #0f172a;
  --primary: #0284c7;
  --primary-dark: #0369a1;
  --secondary: #64748b;
  --accent: #0ea5e9;
  --border: #e2e8f0;
  --card-bg: #ffffff;
  --error: #ef4444;
  --navbar-height: 60px;
}
```

## API Integration

### Services
- `auth.service.ts` - Authentication API calls
- `airline.service.ts` - Airline management endpoints
- `flight.service.ts` - Flight operations
- `airport.service.ts` - Airport data fetching

### Axios Configuration
- Base URL configuration
- Token management
- Request/Response interceptors
- Error handling

## Type Definitions

### User Types
```typescript
interface User {
  id: string;
  name: string;
  email: string;
  phone: string;
  user_type: 'client' | 'enterprise';
}
```

### Data Models
```typescript
interface Flight {
  id: number;
  airline_id: number;
  origin: string;
  destination: string;
  duration: number;
  flight_date: string;
  status: string;
  passenger_capacity: number;
}

interface Airline {
  id: string;
  name: string;
}

interface Airport {
  id: string;
  name: string;
  country: string;
}
```

## Development Tools

- TypeScript for type safety
- ESLint for code quality
- CSS Modules for scoped styling
- Next.js App Router
- Lucide React for icons

## Best Practices

1. Component Structure:
   - Functional components with TypeScript
   - Props interface definitions
   - Custom hooks for logic separation

2. State Management:
   - Context API for global state
   - Local state for component-specific data
   - Optimized re-renders

3. Error Handling:
   - Form validation
   - API error management
   - User feedback

4. Styling:
   - CSS Modules for component isolation
   - CSS Variables for theme consistency
   - Mobile-first responsive design

## Features

- User authentication (login/signup)
- Enterprise dashboard for managing airlines and flights
- Responsive design
- Real-time form validation
- Dark mode support

## Project Structure

```
frontend/
├── src/
│   ├── api/          # API configuration and services
│   ├── app/          # Next.js app router pages
│   ├── components/   # Reusable React components
│   ├── contexts/     # React context providers
│   ├── hooks/        # Custom React hooks
│   ├── interfaces/   # TypeScript interfaces
│   ├── styles/       # CSS modules and global styles
│   └── utils/        # Utility functions and helpers
├── public/           # Static files
└── package.json      # Project dependencies and scripts
```

## Available Scripts

- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run start` - Start production server
- `npm run lint` - Run ESLint for code quality
