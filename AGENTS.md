# AGENTS.md

## Project

This repository contains the Waves Resources take-home engineering challenge.

The project is a secure Symfony REST API for:

- user registration;
- email verification;
- authentication;
- authorization;
- profile management;
- profile image storage;
- administrator user management.

---

# Primary Objective

Build a coherent, secure, maintainable backend.

The objective is NOT to maximize the number of features.

Prioritize:

1. Correctness
2. Security
3. Maintainability
4. Testability
5. Documentation
6. Operational simplicity

---

# Technology Constraints

Use:

- PHP 8.4+
- Symfony
- PostgreSQL
- Doctrine ORM
- Docker Compose
- Argon2id
- JWT access tokens
- Refresh tokens
- MinIO/S3-compatible storage
- Mailpit
- PHPUnit
- OpenAPI

Do not replace the stack without a documented engineering reason.

---

# Architecture

Use a modular-monolith architecture.

Respect these boundaries:

```text
Controller
    ↓
DTO / Validation
    ↓
Application Service
    ↓
Repository / Infrastructure
    ↓
Database / External Service
```

Controllers must remain thin.

Business logic must not be placed directly inside controllers.

---

# Controllers

Controllers live under:

```text
src/Controller/Api/V1/
```

Controllers are responsible for:

* receiving HTTP requests;
* invoking application services;
* returning HTTP responses.

Controllers must NOT:

* contain complex business logic;
* directly construct repositories;
* directly construct external clients;
* contain password hashing logic;
* contain token generation logic;
* contain large validation algorithms.

Use dependency injection.

---

# API Versioning

All API endpoints MUST use the current API version.

Current version:

```text
v1
```

Therefore, endpoints must follow:

```text
/api/v1/...
```

Examples:

```text
/api/v1/auth/register
/api/v1/auth/login
/api/v1/auth/refresh
/api/v1/me
/api/v1/admin/users
```

Do not create unversioned public API endpoints such as:

```text
/api/auth/login
/api/users
```

unless explicitly required for infrastructure endpoints such as health checks.

Infrastructure health check:

```text
GET /health
```

Do not introduce `/v2` unless a breaking API contract change actually requires it.

API versioning must not be confused with:

* Symfony version;
* PHP version;
* database schema version;
* application release version.

---

# DTOs

Use DTOs for incoming request payloads where appropriate.

Validate:

* required fields;
* email format;
* password policy;
* string length;
* file constraints;
* query parameters.

Never trust client input.

---

# Entities

Entities represent persisted state.

Do not turn entities into giant service classes.

Avoid putting external API calls or email sending directly inside entities.

---

# Repositories

Repositories are responsible for persistence queries.

Do not put business workflows inside repositories.

Repositories should not send emails, create JWTs, or upload files.

---

# Services

Use focused services.

Examples:

```text
RegistrationService
EmailVerificationService
AuthenticationService
RefreshTokenService
ProfileService
ProfileImageService
```

Avoid giant classes such as:

```text
UserManager
ApplicationManager
EverythingService
```

unless there is a demonstrated need.

---

# Security Requirements

Security is mandatory.

## Passwords

Use Argon2id.

Never:

```text
MD5
SHA1
SHA256
plain text
```

for password storage.

---

## Authentication

Access tokens should be short-lived.

Target:

```text
Access token: 15 minutes
Refresh token: 30 days
```

Do not hard-code security-sensitive values when configuration is appropriate.

---

## Refresh Tokens

Refresh tokens must be:

* securely generated;
* stored securely;
* expirable;
* revocable.

Do not store raw refresh tokens if a hashed representation can be used.

---

# Authorization

There are two roles:

```text
ROLE_USER
ROLE_ADMIN
```

Admin endpoints must reject regular users with:

```text
403 Forbidden
```

---

# IDOR / BOLA

This is a critical security requirement.

A user must never be able to:

* read another user's profile;
* modify another user's profile;
* change another user's password;
* access another user's private resources.

Prefer:

```text
GET /api/v1/me
PATCH /api/v1/me
```

over exposing unnecessary user IDs.

Never trust a user ID supplied by the client as proof of authorization.

Authorization must be checked server-side.

---

# Email Verification

Verification tokens must:

* be cryptographically random;
* expire;
* be single-use;
* be invalidated after successful use;
* preferably be stored hashed.

Do not log verification tokens.

---

# File Uploads

Never trust:

```text
filename
extension
Content-Type
```

provided by the client.

Validate:

* size;
* actual MIME type;
* supported format.

Only allow intended image formats.

Do not allow arbitrary file uploads.

When replacing an image:

1. Store the new object.
2. Confirm successful storage.
3. Update database association.
4. Remove the old object.

Never delete the old object first.

---

# Error Handling

Use structured API errors.

Responses should have predictable formats.

Do not expose:

* stack traces;
* SQL errors;
* internal filesystem paths;
* secrets;
* tokens;
* passwords.

Do not leak unnecessary authentication information.

---

# Logging

Use structured logging.

Never log:

* passwords;
* JWTs;
* refresh tokens;
* verification tokens;
* secrets.

---

# Database

Use:

* migrations;
* foreign keys;
* indexes;
* unique constraints;
* appropriate data types.

Email uniqueness must be enforced at the database level.

Do not rely exclusively on application-level checks.

---

# Testing

Prioritize security boundaries.

Minimum important tests:

```text
registration
email verification
login
refresh
logout

USER → ADMIN endpoint = forbidden

USER A → USER B profile = forbidden

USER A → USER B profile update = forbidden

password change requires current password
```

Tests must be deterministic.

Do not disable security mechanisms simply to make tests pass.

---

# Docker

The project should work through Docker Compose.

Required infrastructure:

```text
Symfony API
PostgreSQL
MinIO
Mailpit
```

Avoid introducing additional infrastructure unless necessary.

---

# API Design

Use consistent:

* HTTP methods;
* status codes;
* error formats;
* resource naming.

Examples:

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/verify-email
POST /api/v1/auth/refresh
POST /api/v1/auth/logout

GET /api/v1/me
PATCH /api/v1/me
POST /api/v1/me/password
POST /api/v1/me/profile-image

GET /api/v1/admin/users
```

---

# Status Codes

Use semantically correct HTTP status codes.

Examples:

```text
201 Created
200 OK
204 No Content
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
429 Too Many Requests
```

Do not return `200 OK` for every situation.

---

# Git

Use Conventional Commits.

Examples:

```text
feat(auth): add registration endpoint
feat(auth): implement email verification
feat(profile): add password change
test(security): prevent cross-user profile access
fix(auth): reject expired refresh token
docs(readme): document architecture
```

Avoid vague commits.

---

# Before Every Change

Before implementing a feature:

1. Understand the requirement.
2. Check existing architecture.
3. Check whether a service already exists.
4. Avoid duplicating logic.
5. Consider security implications.
6. Consider testing requirements.
7. Make the smallest coherent change.

---

# Before Finishing a Task

Run appropriate checks:

```bash
php bin/console lint:container
php bin/console doctrine:schema:validate
php bin/phpunit
```

Also check:

* formatting;
* static analysis if configured;
* migration status;
* Docker health.

Do not claim a feature is complete if tests are failing.

---

# What the Agent MUST NOT Do

Do not:

* rewrite the architecture without approval;
* introduce microservices;
* add unnecessary dependencies;
* modify unrelated files;
* remove tests to make the suite pass;
* disable authentication;
* disable authorization;
* weaken password requirements;
* store passwords or tokens in plaintext;
* commit secrets;
* commit `.env.local`;
* bypass validation;
* use `md5` or `sha256` for passwords;
* expose internal exceptions to API clients;
* silently change API contracts;
* delete existing functionality without explanation;
* create giant "manager" classes;
* over-engineer simple features;
* add features outside the roadmap merely because they are interesting;
* implement the entire application in one step — work one scoped task at a time.

---

# What the Agent SHOULD Ignore

Unless explicitly requested, ignore:

* frontend development;
* mobile applications;
* Kubernetes;
* microservices;
* GraphQL;
* Redis;
* Kafka;
* RabbitMQ;
* Elasticsearch;
* cloud deployment;
* production infrastructure;
* advanced observability;
* unnecessary design patterns;
* unrelated refactoring;
* UI development.

These are outside the core challenge.

---

# Priority Order

When requirements conflict, prioritize:

```text
1. Security
2. Correctness
3. Core challenge requirements
4. Tests
5. Maintainability
6. Documentation
7. Optional stretch goals
8. Cosmetic improvements
```

---

# Definition of Done

A feature is considered complete only when:

* implementation exists;
* validation exists;
* authorization is correct;
* relevant tests exist;
* error handling is implemented;
* documentation is updated when necessary;
* no secrets are introduced;
* existing tests still pass.
