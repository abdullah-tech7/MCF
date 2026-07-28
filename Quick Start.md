composer dump-autoload
php artisan optimize:clear
php artisan mcf:install
php artisan serve

# Quick Start

## Modules

Create a new module:

```bash
php artisan mcf:make:module Users
```

---

## Workflows

Create a workflow inside a module:

```bash
php artisan mcf:make:workflow Users Profile
```

Create a CRUD workflow:

```bash
php artisan mcf:make:workflow:crud Users UserManagement
```

---

# Database

## Models

Create a model:

```bash
php artisan mcf:make:model User
```

Create a model with a migration:

```bash
php artisan mcf:make:model User -m
```

Create a model with a factory:

```bash
php artisan mcf:make:model User -f
```

Create a model with a seeder:

```bash
php artisan mcf:make:model User -s
```

Create a model with migration, factory, and seeder:

```bash
php artisan mcf:make:model User -mfs
```

---

## Migrations

Create a migration:

```bash
php artisan mcf:make:migration create_users_table
```

Create a migration for a new table:

```bash
php artisan mcf:make:migration create_users_table --create=users
```

Create a migration for an existing table:

```bash
php artisan mcf:make:migration add_email_to_users_table --table=users
```

---

## Factories

Create a factory:

```bash
php artisan mcf:make:factory UserFactory
```

Create a factory for a specific model:

```bash
php artisan mcf:make:factory UserFactory --model=User
```

---

## Seeders

Create a seeder:

```bash
php artisan mcf:make:seeder UserSeeder
```

---

# HTTP

## Middleware

Create a middleware:

```bash
php artisan mcf:make:middleware Auth
```

---

## Validation Rules

Create a validation rule:

```bash
php artisan mcf:make:rule StrongPassword
```

---

## Notifications

Create a notification:

```bash
php artisan mcf:make:notification OrderCreated
```

---

## Mail

Create a mailable:

```bash
php artisan mcf:make:mail WelcomeMail
```

---

# Routes

Register all application routes inside:

```text
app/MCF/mcf_routes.php
```

---

# Available Commands

| Command | Description |
|----------|-------------|
| `mcf:make:module` | Create a new module |
| `mcf:make:workflow` | Create a workflow |
| `mcf:make:workflow:crud` | Create a CRUD workflow |
| `mcf:make:model` | Create a model |
| `mcf:make:migration` | Create a migration |
| `mcf:make:factory` | Create a factory |
| `mcf:make:seeder` | Create a seeder |
| `mcf:make:middleware` | Create a middleware |
| `mcf:make:rule` | Create a validation rule |
| `mcf:make:notification` | Create a notification |
| `mcf:make:mail` | Create a mailable |
````
