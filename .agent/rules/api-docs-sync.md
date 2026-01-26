---
trigger: always_on
---

name: api-docs-sync
description: Ensures API documentation at http://{services_url}:{services_port}/api/documentation stays synchronized with code changes. Detects and updates documentation files for Swagger, Postman, Redoc, and other API documentation tools. Use when modifying API routes, controllers, request/response parameters, or when the user mentions API documentation, Swagger, or OpenAPI.

# API Documentation Sync

Guarantees that `http://{services_url}:{services_port}/api/documentation` reflects the actual code state.

## When to Apply

Apply this skill when:
- Adding, modifying, or deleting API routes
- Changing Request/Response parameters in Controllers
- Updating API authentication or security schemes
- User mentions API documentation, Swagger, OpenAPI, or Postman collections
- Documentation URL is referenced or accessed

## Detection Workflow

### Step 1: Identify Documentation Tool

Search for indicators of which API documentation tool is in use:

**L5-Swagger (Laravel):**
- Config file: `config/l5-swagger.php`
- Route: `/api/documentation` or `/docs`
- JSON output: `storage/api-docs/api-docs.json`
- Vendor view: `resources/views/vendor/l5-swagger/index.blade.php`

**Swagger/OpenAPI (Generic):**
- `swagger.json`, `openapi.json`, `openapi.yaml` in root or `docs/`
- `swagger-ui` in package.json or dependencies
- Routes containing `/swagger`, `/api-docs`, `/documentation`

**Postman:**
- `postman_collection.json` in root or `docs/`
- `.postman/` directory
- References to Postman in README or config

**Redoc:**
- `redoc.html` or `redoc.yaml` in `public/` or `docs/`
- Redoc in package.json dependencies

**Other Tools:**
- Check `package.json` for: `@stoplight/elements`, `slate`, `readme`, `doxygen`
- Look for `docs/` directory with HTML/Markdown files
- Check for Spring REST Docs annotations (Java projects)

### Step 2: Locate Documentation Files

For each detected tool, find the actual file paths:

```bash
# Common locations to search
- Root: swagger.json, openapi.json, postman_collection.json
- docs/: Any .json, .yaml, .html files
- public/: redoc.html, api-docs.html
- storage/api-docs/: api-docs.json (Laravel)
- config/: l5-swagger.php, swagger.php
- resources/views/: vendor/l5-swagger/, dashboard/api-docs.blade.php
```

## Update Checklist

When an API change occurs, update and verify **all applicable locations**:

### For L5-Swagger (Laravel)

1. **Helper JS:** `/public/js/api-helper.js`
   - Update endpoint URLs, request/response examples
   - Verify authentication token handling

2. **JSON Docs:** `/storage/api-docs/api-docs.json`
   - Regenerate: `php artisan l5-swagger:generate`
   - Verify JSON structure matches Controller annotations
   - Check all endpoints, parameters, and responses

3. **Blade View:** `/resources/views/dashboard/api-docs.blade.php`
   - Update iframe URL if route changed
   - Verify documentation link points to correct endpoint

4. **Swagger Config:** `/config/l5-swagger.php`
   - Verify route configuration: `routes.api`
   - Check paths: `docs_json`, `docs_yaml`, `annotations`
   - Update if documentation location changed

5. **Vendor View:** `/resources/views/vendor/l5-swagger/index.blade.php`
   - Update if customizing Swagger UI appearance
   - Verify auto-authorization interceptors if present

### For Generic Swagger/OpenAPI

1. **OpenAPI Spec:** `swagger.json` or `openapi.json`
   - Update paths, schemas, parameters
   - Verify server URLs and base paths

2. **Swagger UI Config:** Check for custom Swagger UI initialization
   - Update in HTML files or JavaScript configs

### For Postman

1. **Collection File:** `postman_collection.json`
   - Update request URLs, methods, headers
   - Verify environment variables
   - Update examples and tests

### For Redoc

1. **Redoc HTML:** `public/redoc.html` or similar
   - Update OpenAPI spec reference
   - Verify theme and configuration

## Verification Steps

After updating documentation:

1. **Regenerate Documentation:**
   ```bash
   # Laravel L5-Swagger
   php artisan l5-swagger:generate
   
   # Generic Swagger (if using swagger-jsdoc or similar)
   npm run generate-docs
   ```

2. **Validate JSON/YAML:**
   - Use online validators: https://editor.swagger.io/
   - Check for syntax errors and missing required fields

3. **Test Documentation URL:**
   - Access: `http://{host}:{port}/api/documentation`
   - Verify all endpoints are visible
   - Test "Try it out" functionality
   - Verify authentication works

4. **Check Consistency:**
   - Compare Controller code with JSON spec
   - Verify request/response examples match actual API behavior
   - Check parameter types and validation rules

## Common Patterns

### Laravel Controller Annotation Update

When updating a controller method:

```php
/**
 * @OA\Post(
 *     path="/api/endpoint",
 *     summary="Endpoint description",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(...)
 *     ),
 *     @OA\Response(response=200, description="Success"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function method(Request $request) { ... }
```

After updating annotations:
1. Run `php artisan l5-swagger:generate`
2. Verify `storage/api-docs/api-docs.json` updated
3. Check documentation URL reflects changes

### Route Changes

When modifying routes:
1. Update route definition in `routes/api.php`
2. Update Controller annotations
3. Regenerate documentation
4. Update `api-helper.js` if frontend uses it
5. Verify Blade view URLs if changed

## Error Prevention

- **Never skip regeneration:** Always run the generate command after code changes
- **Validate before committing:** Check documentation URL works
- **Keep annotations in sync:** Controller annotations must match actual implementation
- **Version control:** Commit both code and documentation JSON together

## Additional Resources

For complex documentation setups, check:
- Framework-specific documentation generators
- CI/CD pipelines that auto-generate docs
- Custom build scripts in `package.json` or `composer.json`