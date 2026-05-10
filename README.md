
3# ICMS Backend Integration

A CodeIgniter 4 based backend designed for integration with frontend clients and API consumers. This repository implements a comprehensive **Role-Based Access Control (RBAC)** system as its core feature, with complementary modules for payroll management, employee time tracking, and administrative functions to demonstrate and test RBAC functionality in a real-world context.

## Key Features

### Core Feature: Role-Based Access Control (RBAC)
- JWT-based authentication with dynamic session tokens
- Role switching with step-up MFA for critical roles (payroll_admin, owner, accounting)
- Granular permission-based access control
- Protected API endpoints with RBAC filters

### Complementary Features (for RBAC Testing & Demonstration)
- Payroll disbursement and summary endpoints (requires `payroll|execute` permissions)
- Employee hours submission API (requires `timesheet|submit` permissions)
- Admin user, role, and permission management endpoints
- Designed to run behind a web server with `public/` as the web root

## Requirements

- PHP 8.2 or higher
- `ext-intl`
- `ext-mbstring`
- `ext-json` (built into PHP)
- `ext-mysqli` or another supported DB driver for database access
- Optional: `ext-curl` for CodeIgniter HTTP client usage

## Installation

1. Clone the repository:

   ```bash
   git clone <repository-url> icms
   cd icms
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Copy the environment template and configure your settings:

   ```bash
   copy env .env
   ```

4. Edit `.env` and configure the following values:

   - `CI_ENVIRONMENT = development` or `production`
   - `JWT_SECRET_KEY = your-secret-key`
   - Database connection settings under `database.default.*`

   Example:

   ```ini
   CI_ENVIRONMENT = development
   JWT_SECRET_KEY = your-generated-secret
   database.default.hostname = localhost
   database.default.database = icms_db
   database.default.username = root
   database.default.password = secret
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

5. Ensure `public/` is the document root for your web server.

## Running Locally

Use CodeIgniter's built-in server for development:

```bash
php spark serve
```

Then open:

```text
http://localhost:8080
```

## Project Structure

- `app/` - Application code
  - `Controllers/` - Controller classes and API endpoints
  - `Config/` - Route, filter, and app configuration
  - `Models/` - Data models for users, roles, permissions, payroll, and more
- `public/` - Web root, includes `index.php`
- `system/` - CodeIgniter framework source
- `writable/` - Cache, logs, session, and upload storage

## API Setup and Integration

This repository is optimized for backend API integration. The main API routes are defined in `app/Config/Routes.php`.

### Public Endpoints

- `POST /login`
  - Body: `username`, `password`
  - Response: JWT token and user session details

### Protected API Endpoints

#### Auth

- `POST /api/auth/switch-role`
  - Body: `target_role`, optional `mfa_token`
  - Header: `Authorization: Bearer <token>`

#### Payroll

- `POST /api/v1/payroll/disburse`
  - Protected by RBAC `payroll|execute`
  - Header: `Authorization: Bearer <token>`

- `GET /api/v1/payroll/summary`
  - Protected by RBAC `payroll|execute`
  - Header: `Authorization: Bearer <token>`

#### Employee

- `POST /api/v1/employee/submit-hours`
  - Protected by RBAC `timesheet|submit`
  - Header: `Authorization: Bearer <token>`

#### Admin

- `GET /api/v1/admin/users`
- `POST /api/v1/admin/users/create`
- `DELETE /api/v1/admin/users/{id}`
- `GET /api/v1/admin/users/{id}/roles`
- `POST /api/v1/admin/roles/create`
- `GET /api/v1/admin/roles`
- `POST /api/v1/admin/roles/assign`
- `POST /api/v1/admin/roles/revoke`
- `GET /api/v1/admin/roles/{id}/permissions`
- `POST /api/v1/admin/permissions/create`
- `GET /api/v1/admin/permissions`
- `POST /api/v1/admin/permissions/assign`
- `POST /api/v1/admin/permissions/revoke`
- `DELETE /api/v1/admin/permissions/{id}`

> Note: Most admin routes are protected by application filters and should be called with a valid JWT token.

## Authentication Flow

1. User logs in via `POST /login`.
2. The backend validates credentials and returns a JWT token.
3. The frontend stores the token and sends it with `Authorization: Bearer <token>` for protected APIs.
4. Role switching is performed using `POST /api/auth/switch-role` and may require `mfa_token` for critical roles.

## Example Login Request

```bash
curl -X POST http://localhost:8080/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=secret"
```

Response example:

```json
{
  "status": 200,
  "message": "Login successful",
  "token": "<jwt-token>",
  "user": {
    "user_id": "1",
    "username": "admin",
    "active_role": "employee",
    "permissions": []
  }
}
```

## Example Protected API Request

```bash
curl -X GET http://localhost:8080/api/v1/payroll/summary \
  -H "Authorization: Bearer <jwt-token>"
```

## Deployment Notes

- Set `CI_ENVIRONMENT = production` in `.env`.
- Use a real JWT secret key and keep it private.
- Ensure `writable/` has write permissions for cache, logs, and session storage.
- Configure your web server to serve the `public/` directory as the application root.

## Troubleshooting

- If your app cannot connect to the database, verify `.env` database settings and extension availability.
- If JWT authentication fails, verify `JWT_SECRET_KEY` in `.env`.
- Make sure `public/index.php` is the web server entry point, not the repository root.

## License

This project uses the MIT license.
