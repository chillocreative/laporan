#!/bin/bash
# =============================================================================
# CI/CD Deployment Script (non-interactive)
# Called by GitHub Actions over SSH after CI passes on main.
# =============================================================================

set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "$0")/.." && pwd)}"
BRANCH="${DEPLOY_BRANCH:-main}"

log() { echo "[deploy-ci] $(date '+%Y-%m-%d %H:%M:%S') $*"; }

log "Deploying $BRANCH into $APP_DIR"

cd "$APP_DIR"

log "Enter maintenance mode"
php artisan down --retry=60 --refresh=15 || true

# Trap: make sure we always come back up, even on failure
trap 'log "Bringing app back up (trap)"; php artisan up || true' EXIT

log "Fetching latest code"
git fetch --prune origin "$BRANCH"
git reset --hard "origin/$BRANCH"

log "Installing composer dependencies (prod)"
composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

log "Installing npm dependencies"
npm ci

log "Building frontend assets"
npm run build

log "Running database migrations"
php artisan migrate --force

log "Rebuilding caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

log "Restarting queue workers"
php artisan queue:restart || true

log "Optimising autoloader"
composer dump-autoload --optimize

log "Exit maintenance mode"
php artisan up
trap - EXIT

log "Deployment complete"
