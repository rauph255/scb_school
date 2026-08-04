# MySQL Admin Credentials Needed

Date: 2026-08-03

## Context

The updated project contract requires MySQL 8.0+ as the only relational database for local development, testing, staging, and production. The local machine has `mysql`, `mysqld`, and PHP `pdo_mysql` available, and `mysql.service` is running.

## Finding

The MySQL root account rejects passwordless access:

```text
ERROR 1045 (28000): Access denied for user 'root'@'localhost'
```

`sudo mysql` also requires an interactive sudo password, which is not available to Codex.

## Decision

Proceed with MySQL-first application configuration and Phase 0B database documentation, but do not claim migrations or MySQL tests are complete until database credentials are provided.

## Needed External Input

Provide one of the following:

- MySQL admin credentials that can create `scb_school`, `scb_school_test`, and `scb_app`.
- A pre-created `scb_app` user with privileges on `scb_school` and `scb_school_test`.
- Permission and password handling outside Codex for local `sudo mysql` setup.
