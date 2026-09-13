#!/usr/bin/env bash

set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
release_name="scb-cpanel-php82-$(date +%Y%m%d-%H%M%S)"
release_output="${project_root}/releases/${release_name}.zip"
release_tmp="$(mktemp -d /tmp/scb-cpanel-release.XXXXXX)"
bundle_root="${release_tmp}/${release_name}"
application_root="${bundle_root}/scb_app"
public_root="${bundle_root}/public_html"

cleanup() {
    rm -rf -- "${release_tmp}"
}

trap cleanup EXIT

mkdir -p "${application_root}" "${public_root}"

rsync -a \
    --include='/.env.production.example' \
    --exclude='/.env*' \
    --exclude='/.git/' \
    --exclude='/.agents/' \
    --exclude='/.codex/' \
    --exclude='/.github/' \
    --exclude='/.idea/' \
    --exclude='/.vscode/' \
    --exclude='/.phpunit.cache/' \
    --exclude='/.phpunit.result.cache' \
    --exclude='/AGENTS.md' \
    --exclude='/CODEX_MASTER_PROMPT.md' \
    --exclude='/DATABASE_DESIGN.md' \
    --exclude='/PRD.md' \
    --exclude='/PRD_MYSQL_AMENDMENT.md' \
    --exclude='/SHA256SUMS.txt' \
    --exclude='/node_modules/' \
    --exclude='/tests/' \
    --exclude='/phpunit.xml' \
    --exclude='/database/factories/' \
    --exclude='/database/seeders/' \
    --exclude='/docs/' \
    --exclude='/deploy/' \
    --exclude='/releases/' \
    --exclude='/scripts/' \
    --exclude='/resources/reference/' \
    --exclude='/storage/app/private/***' \
    --exclude='/storage/app/public/***' \
    --exclude='/storage/framework/***' \
    --exclude='/storage/logs/***' \
    --exclude='/public/hot' \
    --exclude='/public/storage' \
    --exclude='/README.md' \
    "${project_root}/" "${application_root}/"

mkdir -p \
    "${application_root}/storage/app/private" \
    "${application_root}/storage/app/public" \
    "${application_root}/storage/framework/cache/data" \
    "${application_root}/storage/framework/sessions" \
    "${application_root}/storage/framework/testing" \
    "${application_root}/storage/framework/views" \
    "${application_root}/storage/logs" \
    "${application_root}/bootstrap/cache"

cp "${project_root}/storage/app/.gitignore" "${application_root}/storage/app/.gitignore"
cp "${project_root}/storage/app/private/.gitignore" "${application_root}/storage/app/private/.gitignore"
cp "${project_root}/storage/app/public/.gitignore" "${application_root}/storage/app/public/.gitignore"
cp "${project_root}/storage/framework/.gitignore" "${application_root}/storage/framework/.gitignore"
cp "${project_root}/storage/framework/cache/.gitignore" "${application_root}/storage/framework/cache/.gitignore"
cp "${project_root}/storage/framework/sessions/.gitignore" "${application_root}/storage/framework/sessions/.gitignore"
cp "${project_root}/storage/framework/testing/.gitignore" "${application_root}/storage/framework/testing/.gitignore"
cp "${project_root}/storage/framework/views/.gitignore" "${application_root}/storage/framework/views/.gitignore"
cp "${project_root}/storage/logs/.gitignore" "${application_root}/storage/logs/.gitignore"

(
    cd "${application_root}"
    APP_ENV=production composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
)

rsync -a "${application_root}/public/" "${public_root}/"
cp "${project_root}/deploy/cpanel/public-index.php" "${public_root}/index.php"
cp "${project_root}/docs/cpanel-deployment.md" "${bundle_root}/INSTALL-CPANEL.md"

(
    cd "${bundle_root}"
    find . -type f ! -name SHA256SUMS -print0 \
        | sort -z \
        | xargs -0 sha256sum > SHA256SUMS
)

(
    cd "${release_tmp}"
    zip -qr "${release_output}" "${release_name}"
)

printf '%s\n' "${release_output}"
