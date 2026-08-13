# Waves Resources — User Onboarding & Profile Management API

A production-oriented REST API developed as a take-home engineering challenge for Waves Resources.

The project implements secure user onboarding, authentication, authorization, profile management, email verification, profile image storage, and administrator user management.

The primary objective is not feature quantity, but demonstrating sound engineering decisions, security awareness, maintainable architecture, automated testing, and clear technical documentation.

---

## Table of Contents

- [Overview](#overview)
- [Goals](#goals)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [API Versioning](#api-versioning)
- [Core Features](#core-features)
- [Project Structure](#project-structure)
- [Security Model](#security-model)
- [Data Model](#data-model)
- [Authentication Flow](#authentication-flow)
- [Authorization Model](#authorization-model)
- [Infrastructure](#infrastructure)
- [Development Setup](#development-setup)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Environment Variables](#environment-variables)
- [Git Workflow](#git-workflow)
- [Commit Convention](#commit-convention)
- [Engineering Principles](#engineering-principles)
- [Trade-offs](#trade-offs)
- [Known Limitations](#known-limitations)
- [Future Improvements](#future-improvements)

---

# Overview

This project is a secure user onboarding and profile-management service.

The system allows users to:

- create an account;
- verify their email address;
- authenticate securely;
- refresh their authentication session;
- view their profile;
- update their full name;
- change their password;
- upload and replace a profile image.

Administrators can additionally:

- list registered users;
- filter users;
- sort users;
- paginate results.

The API is designed with security and maintainability as primary concerns.

---

# Goals

The main engineering goals are:

1. Secure authentication and authorization.
2. Explicit protection against IDOR/BOLA vulnerabilities.
3. Strong input validation.
4. Secure password storage.
5. Short-lived access tokens.
6. Refresh-token management.
7. Reliable email verification.
8. Safe object-storage integration.
9. Automated security-focused testing.
10. Reproducible local development using Docker.
11. Clear API documentation.
12. Maintainable and understandable code.

Feature quantity is intentionally secondary to correctness and engineering quality.

---

# Technology Stack

| Component | Technology |
|---|---|
| Backend | Symfony |
| Language | PHP 8.4+ |
| Database | PostgreSQL |
| ORM | Doctrine ORM |
| Authentication | JWT (HS256) + Refresh Tokens |
| Password Hashing | Argon2id |
| Object Storage | MinIO / S3-compatible storage |
| Email | Symfony Mailer |
| Email Catcher | Mailpit |
| API Documentation | OpenAPI / NelmioApiDocBundle |
| Testing | PHPUnit / Symfony Test Pack |
| Static analysis | PHPStan |
| Coding standards | PHP-CS-Fixer |
| Refactoring | Rector |
| Containers | Docker / Docker Compose |
| Logging | Monolog |
| CI | GitHub Actions |

---

# Architecture

The application follows a modular-monolith architecture.

Microservices are intentionally avoided because the problem domain is relatively small and does not justify the operational complexity of distributed services.

The architecture emphasizes clear boundaries between HTTP handling, application logic, persistence, security, and external integrations.

## High-Level Architecture

```text
                         ┌──────────────────────┐
                         │       Client         │
                         │ Postman / Swagger    │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │     Symfony API      │
                         │                      │
                         │ Controllers          │
                         │ DTOs / Validation    │
                         │ Application Services │
                         │ Security             │
                         └───────┬──────┬───────┘
                                 │      │
                    ┌────────────┘      └──────────────┐
                    ▼                                  ▼
             ┌───────────────┐                  ┌───────────────┐
             │  PostgreSQL   │                  │     MinIO     │
             │               │                  │               │
             │ Users         │                  │ Profile       │
             │ Tokens        │                  │ Images        │
             │ Verification  │                  │               │
             └───────────────┘                  └───────────────┘
                    │
                    ▼
             ┌───────────────┐
             │    Mailpit    │
             │               │
             │ Verification  │
             │ emails        │
             └───────────────┘
```

---

# Architectural Principles

The project follows these principles:

### Separation of concerns

Controllers should handle HTTP concerns.

Business logic belongs in application/domain services.

Persistence belongs in repositories and Doctrine.

External integrations should be isolated behind dedicated services or interfaces.

### Dependency Injection

Dependencies must be injected rather than instantiated manually inside business logic.

### Explicit security boundaries

Authentication and authorization must never depend only on client-provided identifiers.

### Fail securely

Unexpected or invalid input must result in safe failure rather than permissive behavior.

### Minimal complexity

Do not introduce abstractions, patterns, libraries, or infrastructure without a clear reason.

---

# API Versioning

The API is versioned through the URL path.

The current API version is:

```text
/api/v1
```

All public API endpoints must be exposed under the versioned prefix.

Example:

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
GET  /api/v1/me
GET  /api/v1/admin/users
```

The API version represents the public HTTP contract and is independent from the application, Symfony, or database version.

A future breaking API change may introduce:

```text
/api/v2
```

without breaking existing `/api/v1` clients.

Non-breaking changes should preferably be introduced within the existing API version.

Infrastructure endpoints are intentionally unversioned:

```text
GET /health
```

---

# Core Features

## Account Creation

```http
POST /api/v1/auth/register
```

Requirements:

* full name;
* unique email;
* password validation;
* Argon2id password hashing;
* structured validation errors;
* predictable response.

---

## Email Verification

```http
POST /api/v1/auth/verify-email
```

A newly registered user receives a verification email.

The verification token must:

* be cryptographically random;
* expire;
* be single-use;
* not be stored in plaintext.

Unverified users cannot access protected application functionality.

---

## Authentication

```http
POST /api/v1/auth/login
POST /api/v1/auth/refresh
POST /api/v1/auth/logout
```

Authentication uses:

* short-lived access tokens;
* refresh tokens;
* refresh-token revocation;
* rate limiting on login.

Target token lifetime:

```text
Access token: 15 minutes
Refresh token: 30 days
```

These values are configuration decisions and may be changed through environment configuration.

---

# Authorization

The application supports:

```text
ROLE_USER
ROLE_ADMIN
```

Authorization is enforced at two levels.

## Role-based authorization

Administrative endpoints require:

```text
ROLE_ADMIN
```

A regular user must receive:

```text
403 Forbidden
```

when attempting to access administrator functionality.

## Object-level authorization

Users can only access and modify their own profile.

The preferred profile API is:

```http
GET /api/v1/me
PATCH /api/v1/me
```

rather than exposing unnecessary user IDs.

This reduces the risk of IDOR/BOLA vulnerabilities.

---

# User Profile

Authenticated users can:

```http
GET    /api/v1/me
PATCH  /api/v1/me
POST   /api/v1/me/password
POST   /api/v1/me/profile-image
GET    /api/v1/me/profile-image
```

Users may:

* view their own profile;
* update their full name;
* view their email;
* change their password;
* upload a profile image;
* replace an existing profile image.

Email is read-only through profile management.

Changing a password requires the current password.

---

# Administrator

Administrators can access:

```http
GET /api/v1/admin/users
```

Supported functionality:

* pagination;
* filtering by role;
* filtering by verification status;
* sorting by creation date;
* sorting by name.

Example:

```text
/api/v1/admin/users?page=1&limit=20&role=ROLE_USER&verified=true&sort=createdAt&direction=desc
```

---

# Profile Image Storage

Profile images are stored using S3-compatible object storage.

Development environment:

```text
MinIO
```

The database stores the association between the user and the stored object.

The system validates:

* file size;
* MIME type;
* supported image format.

Supported formats may include:

* JPEG;
* PNG;
* WebP.

Replacing an image must not orphan the previous object.

The old object is deleted only after successful storage of the replacement.

---

# Project Structure

```text
src/
├── Controller/
│   └── Api/
│       └── V1/
│           ├── Auth/
│           ├── Profile/
│           └── Admin/
│
├── Entity/
│
├── Repository/
│
├── DTO/
│   ├── Auth/
│   ├── Profile/
│   └── Admin/
│
├── Service/
│   ├── Authentication/
│   ├── Email/
│   ├── Storage/
│   └── Profile/
│
├── Security/
│   └── Voter/
│
└── Enum/
```

---

# Data Model

## User

```text
User
├── id
├── fullName
├── email
├── password
├── roles
├── emailVerified
├── createdAt
└── updatedAt
```

## EmailVerificationToken

```text
EmailVerificationToken
├── id
├── user
├── tokenHash
├── expiresAt
├── usedAt
└── createdAt
```

## RefreshToken

```text
RefreshToken
├── id
├── user
├── tokenHash
├── expiresAt
├── revokedAt
└── createdAt
```

## ProfileImage

```text
ProfileImage
├── id
├── user
├── objectKey
├── mimeType
├── size
└── createdAt
```

---

# Authentication Flow

```text
Registration
     │
     ▼
Validate input
     │
     ▼
Hash password
     │
     ▼
Create user
     │
     ▼
Generate verification token
     │
     ▼
Send email
     │
     ▼
User verifies email
     │
     ▼
Login
     │
     ├── Access Token
     │
     └── Refresh Token
```

---

# Security Model

The following security requirements are considered mandatory.

## Password Security

Passwords must never be stored in plaintext.

Use:

```text
Argon2id
```

Do not use:

```text
MD5
SHA-1
SHA-256
```

as password hashing algorithms.

---

## Authentication Security

* short-lived access tokens;
* refresh-token expiration;
* refresh-token revocation;
* login rate limiting;
* generic authentication errors;
* no sensitive data in logs.

---

## Authorization Security

The application must explicitly protect against:

* IDOR;
* BOLA;
* privilege escalation;
* unauthorized profile modification.

---

## Input Validation

All inbound data must be validated.

This includes:

* JSON payloads;
* query parameters;
* path parameters;
* uploaded files.

---

## Secrets

Secrets must never be committed.

Use:

```text
.env.local
```

for local development.

Commit:

```text
.env.example
```

with placeholders only.

---

# Infrastructure

Development infrastructure:

```text
Docker Compose
│
├── nginx        → API HTTP (port 8000)
├── php          → Symfony PHP-FPM
├── frontend     → optional SPA (profile frontend, port 3000)
├── PostgreSQL
├── MinIO
├── Mailpit
└── Adminer
```

The system should be runnable by another engineer without requiring paid external services.

---

# Development Setup

## Clone

```bash
git clone https://github.com/rooneyi/Waves-Resources-onboarding.git
cd Waves-Resources-onboarding
```

## Development objectives

1. Secure authentication and authorization.
2. Explicit protection against IDOR/BOLA vulnerabilities.
3. Strong input validation.
4. Secure password storage (Argon2id).
5. Short-lived access tokens with refresh-token management.
6. Reliable email verification.
7. Safe object-storage integration for profile images.
8. Automated security-focused testing.
9. Reproducible local development using Docker Compose.
10. Clear API documentation (OpenAPI / Swagger).
11. Maintainable and understandable code.

## Prerequisites

- Docker Desktop (or Docker Engine + Compose)
- Git

Optional (host PHP workflow only): PHP 8.4+ with `pdo_pgsql`, Composer.

## 1. Environment

```bash
cp .env.example .env
```

Set at least:

- `APP_SECRET`
- `JWT_SECRET` (long random string)

`.env.example` already contains working local defaults for Postgres, Mailpit, and MinIO.

## 2. Start the stack

```bash
docker compose up -d --build
```

This starts nginx, PHP-FPM, PostgreSQL, MinIO, Mailpit, and Adminer.
Migrations run automatically on PHP container start.

Optional frontend placeholder:

```bash
docker compose --profile frontend up -d --build
```

## 3. Seed an administrator

```bash
docker compose exec php php bin/console app:seed-admin --email=admin@waves.local --password=AdminPass1
```

## 4. Local services

```text
API        → http://127.0.0.1:8000
Swagger    → http://127.0.0.1:8000/api/doc
Health     → http://127.0.0.1:8000/health
Frontend   → http://127.0.0.1:3000   (profile frontend)
PostgreSQL → 127.0.0.1:5433
Mailpit UI → http://127.0.0.1:8025
Adminer    → http://127.0.0.1:8080
MinIO API  → http://127.0.0.1:9000
MinIO UI   → http://127.0.0.1:9001
```

Inside Compose, PHP talks to services by hostname (`database`, `mailer`, `minio`).

## 5. Install dependencies (host)

If you run PHP on the host instead of using the PHP container:

```bash
composer install
```

Inside Docker, dependencies are installed by the PHP entrypoint when `vendor/` is missing:

```bash
docker compose exec php composer install
```

## 6. Run tests

```bash
docker compose exec php php bin/phpunit
```

Or on the host (with PHP 8.4 + Postgres reachable via `.env`):

```bash
php bin/phpunit
```

## 7. Run linters and static analysis

```bash
docker compose exec php composer lint
```

Individual commands:

```bash
docker compose exec php composer lint:cs       # PHP-CS-Fixer (dry-run)
docker compose exec php composer lint:cs-fix  # apply CS Fixer
docker compose exec php composer lint:phpstan  # PHPStan level 6
docker compose exec php composer lint:rector   # Rector (dry-run)
docker compose exec php composer rector        # apply Rector
```

On the host:

```bash
composer lint
```

## 8. Useful Symfony commands

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console lint:container
docker compose exec php php bin/console doctrine:schema:validate
```

## 9. Stop the stack

```bash
docker compose down
```

---

# Testing

Testing prioritizes security boundaries and critical workflows.

Important test scenarios include:

```text
Registration
├── valid registration
├── duplicate email
├── invalid email
└── weak password

Email Verification
├── valid token
├── expired token
└── reused token

Authentication
├── valid login
├── invalid credentials
├── unverified user
└── refresh token

Authorization
├── USER cannot access ADMIN endpoint
├── USER cannot access another user's profile
└── USER cannot modify another user's profile

Profile
├── update own profile
├── change password
└── upload image
```

---

# API Documentation

The API exposes OpenAPI documentation via NelmioApiDocBundle.

- Swagger UI: http://127.0.0.1:8000/api/doc
- OpenAPI JSON: http://127.0.0.1:8000/api/doc.json

Use the **Authorize** button in Swagger UI and provide:

```text
Bearer <access_token>
```

The documentation must allow a reviewer to understand and exercise the API without reading the source code.

---

# Environment Variables

Example configuration:

```dotenv
APP_ENV=dev
APP_SECRET=

DATABASE_URL=

JWT_SECRET=
JWT_ACCESS_TOKEN_TTL=900
JWT_REFRESH_TOKEN_TTL=2592000

MAILER_DSN=
MAILER_FROM=

S3_ENDPOINT=
S3_ACCESS_KEY=
S3_SECRET_KEY=
S3_BUCKET=
S3_REGION=
```

Actual secrets must never be committed.

## GitHub Actions secrets

CI (Tests / PHPStan) uses **safe ephemeral defaults** when secrets are not set
(`app` / `ChangeMe` for Postgres, CI placeholders for `APP_SECRET` / `JWT_SECRET`).

Optional overrides under **Settings → Secrets and variables → Actions**:

| Secret | Purpose |
|---|---|
| `APP_SECRET` | Override Symfony app secret in CI |
| `JWT_SECRET` | Override JWT signing secret in CI |
| `S3_ACCESS_KEY` / `S3_SECRET_KEY` / `S3_BUCKET` / `S3_REGION` | Override S3 test values |
| `MAILER_FROM` | Override mail From address in CI |
| `DEPLOY_HOST` | Production SSH host |
| `DEPLOY_USER` | Production SSH user |
| `DEPLOY_SSH_KEY` | Private SSH key for deploy |
| `DEPLOY_PORT` | SSH port |
| `DEPLOY_PATH` | Absolute path to the compose project on the server |
| `DEPLOY_GHCR_USER` | GitHub username/org used to pull from GHCR |
| `DEPLOY_GHCR_TOKEN` | PAT with `read:packages` for the server |

Also create a GitHub Environment named `production` (used by the deploy job).

> If Actions show *“account is locked due to a billing issue”*, fix billing under
> GitHub **Settings → Billing** — workflows cannot start until that is resolved.

### Deploy workflow

`.github/workflows/deploy.yml` (manual only):

1. Builds `docker/php/Dockerfile.prod`
2. Pushes the image to `ghcr.io/<owner>/<repo>`
3. SSHs to the server, pulls images, and runs `docker compose up -d`

Trigger: **Actions → Deploy → Run workflow**.

The server `compose` stack must reference the GHCR image for the PHP service.

---

# Git Workflow

The main branch is:

```text
main
```

Development work should use short-lived feature branches.

Examples:

```text
feat/user-registration
feat/email-verification
feat/jwt-authentication
feat/profile-management
feat/minio-storage
test/auth-security
docs/api-documentation
fix/refresh-token-validation
```

Avoid large branches containing unrelated changes.

---

# Commit Convention

Use Conventional Commits.

Format:

```text
<type>(<scope>): <description>
```

Examples:

```text
feat(auth): add user registration

feat(auth): implement email verification

feat(profile): add profile image upload

feat(admin): add paginated user listing

fix(auth): reject expired refresh tokens

test(auth): cover invalid login attempts

test(security): prevent cross-user profile access

refactor(auth): extract token service

docs(readme): document authentication flow

chore(docker): add postgres service
```

Allowed primary types:

```text
feat
fix
test
refactor
docs
chore
perf
ci
```

Commit messages should describe one coherent change.

Avoid:

```text
update stuff
fix
changes
final
final-final
test123
```

---

# Engineering Principles

The following principles guide implementation.

### 1. Correctness over feature count

A small, secure implementation is preferable to a large incomplete implementation.

### 2. Security boundaries first

Authentication and authorization must be designed before exposing protected resources.

### 3. Explicit over magical

Important security and business decisions should be easy to discover in the code.

### 4. No premature abstraction

Do not create interfaces, factories, managers, or generic services without a concrete reason.

### 5. No unnecessary dependencies

Every dependency should have a documented purpose.

### 6. Test behavior, not implementation details

Tests should verify externally observable behavior.

### 7. Documentation is part of the implementation

Important architectural and security decisions must be documented.

---

# Trade-offs

The project intentionally uses a modular monolith instead of microservices.

Reason:

The application has a relatively small domain and does not require independent deployment or scaling of individual services. A modular monolith reduces operational complexity while preserving clear internal boundaries.

MinIO is used locally instead of a paid cloud object-storage provider.

Reason:

MinIO provides an S3-compatible development environment and allows the complete challenge to run locally.

---

# Known Limitations

Known limitations will be documented as implementation progresses.

Examples may include:

* development-only local object storage;
* limited observability;
* no production deployment;
* limited metrics;
* no advanced fraud detection.

Limitations must never be hidden from reviewers.

---

# Future Improvements

Potential future improvements include:

* production S3 integration;
* refresh-token reuse detection;
* password reset;
* administrator audit logging;
* metrics and tracing;
* production secrets management;
* advanced observability.

---

# License

This repository was created as part of an engineering evaluation challenge.
