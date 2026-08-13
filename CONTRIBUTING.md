# Contributing Guide

## Branches

Use short-lived feature branches.

Examples:

```text
feat/user-registration
feat/email-verification
feat/authentication
feat/profile
feat/admin-users
test/security
fix/token-expiration
docs/api
```

Do not commit directly to `main` for feature development.

---

# Conventional Commits

Format:

```text
type(scope): description
```

## Types

### feat

New functionality.

```text
feat(auth): add registration endpoint
```

### fix

Bug correction.

```text
fix(auth): reject expired refresh token
```

### test

Tests.

```text
test(auth): add invalid credential tests
```

### refactor

Code restructuring without behavior change.

```text
refactor(auth): extract refresh token service
```

### docs

Documentation changes.

```text
docs(readme): document docker setup
```

### chore

Maintenance.

```text
chore(deps): update symfony dependencies
```

### ci

CI/CD changes.

```text
ci(github): add test workflow
```

---

# Commit Rules

A commit should:

* represent one logical change;
* be understandable without opening the diff;
* avoid unrelated modifications.

Good:

```text
feat(auth): implement email verification
```

Bad:

```text
update everything
```

Bad:

```text
final version
```

Bad:

```text
fix
```

---

# Pull Requests

A pull request should explain:

## What changed?

Short description.

## Why?

Explain the engineering reason.

## Security impact

Mention authentication, authorization, validation, or data concerns.

## Tests

Explain what was tested.

## Known limitations

Mention unfinished work.

---

# Quality Checklist

Before opening a PR:

```text
[ ] Tests pass
[ ] No secrets committed
[ ] No unrelated changes
[ ] Validation implemented
[ ] Authorization verified
[ ] API behavior documented
[ ] Migration included when database changes exist
[ ] README updated when necessary
[ ] Commit messages follow Conventional Commits
```
