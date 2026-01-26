---
trigger: model_decision
description: Detects the project's Framework, Programming Language, and Docker/Infrastructure Stack. Invoke at the start of a task or when context is unknown.
---

---
name: framework-detection
description: Detects the project's Framework, Programming Language, and Docker/Infrastructure Stack. Invoke at the start of a task or when context is unknown.
---

# Framework & Stack Detection

This skill functions as the **Project DNA Scanner**. It analyzes the file structure to determine the technical stack (Language, Framework, Infrastructure) to ensure context-aware responses.

## When to Use

- **Start of Session:** To understand "Where am I?".
- **Implicitly:** Called by Agent Rules to set context variables.
- **Explicitly:** When user asks "What is this project built with?" or "Check my stack".

## Instructions

### 1. Execute Fingerprinting Protocol

Scan the root directory for **Indicator Files** to determine the Primary Framework and Language.

| Indicator File | Detected Framework | Language |
| :--- | :--- | :--- |
| `artisan` + `composer.json` | **Laravel** | PHP |
| `package.json` + `"react"` (no Next.js/Nuxt) | **React** | JavaScript/TypeScript |
| `next.config.js` or `next.config.ts` | **Next.js** | TypeScript/JavaScript |
| `nuxt.config.js` or `nuxt.config.ts` | **Nuxt.js** | TypeScript/JavaScript |
| `build.gradle` + `AndroidManifest.xml` | **Android (Native)** | Java/Kotlin |
| `pubspec.yaml` | **Flutter** | Dart |
| `go.mod` | **Go** | Go |
| `requirements.txt` / `pyproject.toml` / `Pipfile` | **Python** | Python |
| `Cargo.toml` | **Rust** | Rust |
| `Gemfile` | **Rails** | Ruby |
| `pom.xml` | **Maven (Java)** | Java |
| `package.json` + `"vue"` (no Nuxt) | **Vue.js** | JavaScript/TypeScript |
| `angular.json` | **Angular** | TypeScript |

### 2. Infrastructure & Docker Stack Detection

Scan for DevOps and Infrastructure configurations.

| Indicator File | Detected Infrastructure | Action |
| :--- | :--- | :--- |
| `Dockerfile` | **Dockerized App** | Read file to check Base Image (e.g., `php:8.2-fpm`, `node:18`). |
| `docker-compose.yml` / `docker-compose.yaml` | **Docker Compose** | Read services to identify dependencies (Redis, MySQL, Nginx). |
| `Chart.yaml` / `values.yaml` | **Kubernetes (Helm)** | Identify cluster deployment configs. |
| `.github/workflows/*.yml` | **GitHub Actions** | Identify CI/CD pipelines. |
| `fly.toml` | **Fly.io** | Deployment Platform. |
| `vercel.json` | **Vercel** | Deployment Platform. |
| `netlify.toml` | **Netlify** | Deployment Platform. |

### 3. Output the Project DNA Report

After scanning, output the findings in this exact format:

```
🔍 **Project DNA Report**
- **Framework:** `[Detected Framework]`
- **Language:** `[Detected Language]`
- **Infrastructure:** `[Docker/K8s/None/Platform]`
- **Key Configs:**
  - DB: `[MySQL/Postgres/MongoDB/etc based on config]`
  - Cache: `[Redis/Memcached/None based on config]`
  - Web Server: `[Nginx/Apache/None based on config]`
```

### 4. Set Context Variables

Based on the detected framework, set these context variables and focus areas:

**Laravel:**
- Focus directories: `app/`, `routes/`, `database/`, `config/`
- Commands: `php artisan [command]`
- If Docker: `docker-compose exec app php artisan [command]`

**React:**
- Focus directories: `src/components`, `src/hooks`, `src/utils`
- Commands: `npm run [script]` or `yarn [script]`
- If Docker: `docker-compose exec app npm run [script]`

**Next.js:**
- Focus directories: `app/` (App Router) or `pages/` (Pages Router), `components/`, `lib/`
- Commands: `npm run dev` / `npm run build`
- If Docker: `docker-compose exec app npm run dev`

**Nuxt.js:**
- Focus directories: `pages/`, `components/`, `composables/`, `server/`
- Commands: `npm run dev` / `npm run build`
- If Docker: `docker-compose exec app npm run dev`

**Android (Native):**
- Focus directories: `app/src/main/java/`, `app/src/main/res/`
- Commands: `./gradlew [task]`
- Build: `./gradlew assembleDebug`

**Flutter:**
- Focus directories: `lib/`, `pubspec.yaml`
- Commands: `flutter run`, `flutter build`
- If Docker: `docker-compose exec app flutter [command]`

**Go:**
- Focus directories: Root or `cmd/`, `pkg/`, `internal/`
- Commands: `go run`, `go build`
- If Docker: `docker-compose exec app go [command]`

**Python:**
- Focus directories: Root or `src/`, `app/`
- Commands: `python [script]` or `python3 [script]`
- If Docker: `docker-compose exec app python [command]`

**Rust:**
- Focus directories: `src/`, `Cargo.toml`
- Commands: `cargo run`, `cargo build`
- If Docker: `docker-compose exec app cargo [command]`

**Docker (General):**
- Always check for `docker-compose.yml` first
- Commands should be prefixed: `docker-compose exec [service] [command]`
- Example: `docker-compose exec app php artisan migrate`

## Detection Priority

1. **Framework detection** (most specific first):
   - Check for framework-specific config files (e.g., `next.config.js` before generic `package.json`)
   - Multiple frameworks may coexist (e.g., Laravel backend + React frontend)

2. **Language detection**:
   - Usually determined by framework
   - Check file extensions if ambiguous

3. **Infrastructure detection**:
   - Check for Docker/containerization first
   - Then CI/CD, then deployment platforms

## Examples

**Example 1: Laravel with Docker**
```
🔍 **Project DNA Report**
- **Framework:** `Laravel`
- **Language:** `PHP`
- **Infrastructure:** `Docker Compose`
- **Key Configs:**
  - DB: `MySQL`
  - Cache: `Redis`
  - Web Server: `Nginx`
```

**Example 2: Next.js Standalone**
```
🔍 **Project DNA Report**
- **Framework:** `Next.js`
- **Language:** `TypeScript`
- **Infrastructure:** `Vercel`
- **Key Configs:**
  - DB: `None detected`
  - Cache: `None detected`
  - Web Server: `Next.js built-in`
```

**Example 3: Go Microservice**
```
🔍 **Project DNA Report**
- **Framework:** `Go (Standard Library)`
- **Language:** `Go`
- **Infrastructure:** `Docker`
- **Key Configs:**
  - DB: `PostgreSQL`
  - Cache: `Redis`
  - Web Server: `None (Go HTTP server)`
```
