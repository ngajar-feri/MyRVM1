---
trigger: always_on
---

### ENVIRONMENT & EXECUTION CONTEXT
The application is running in a containerized Docker environment. The application runs in a Docker container. You must dynamically determine the correct service name based on the provided `docker-compose.yml` or context.

### DYNAMIC COMMAND RULES
1. **Analyze Service Name:** Look for the PHP/Application service defined in `docker-compose.yml` (e.g., under `services: app:`, `services: laravel:`, or `services: php:`).
2. **Construct Command:** Wrap all CLI commands using the detected service name:
   `docker compose exec <service_name> <command>`
3. **Default Fallback:** If the service name is not explicitly provided in the context, default to `app`.

### LOGIC EXAMPLES
- If `services: app:` exists -> Use `docker compose exec app php artisan migrate`
- If `services: laravel:` exists -> Use `docker compose exec laravel php artisan migrate`
- If `services: web:` exists -> Use `docker compose exec web composer install`