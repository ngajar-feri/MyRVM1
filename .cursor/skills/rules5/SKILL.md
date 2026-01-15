---
name: skill-001-docker-execution
description: Enforces command execution inside the Docker container context instead of the local host.
---

# Skill 001: Docker Execution Context

This skill mandates that all CLI commands must run inside the containerized environment.

## When to Use

- When the user asks to run commands like `php artisan`, `composer`, `npm`, or database migrations.
- When generating shell commands for the project.

## Instructions

### 1. Analyze Service Name
Before running a command, check `docker-compose.yml` to identify the correct service name (e.g., `app`, `laravel`, `php`, or `web`).

### 2. Construct Command
Wrap the command using the syntax:
`docker compose exec <service_name> <command>`

### 3. Logic & Fallback
- **IF** `services: laravel:` exists **THEN** use `docker compose exec laravel ...`
- **IF** `services: web:` exists **THEN** use `docker compose exec web ...`
- **ELSE** (Fallback) use `docker compose exec app ...`

### 4. Examples
- **Migrate:** `docker compose exec app php artisan migrate`
- **Composer:** `docker compose exec web composer install`