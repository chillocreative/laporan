#!/bin/bash
# =============================================================================
# Sistem Pelaporan - Deployment Script
# Malaysian Government Reporting System
# Usage: bash deploy.sh [production|staging]
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
ENVIRONMENT="${1:-}"
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
ARTISAN="${PHP_BIN} ${APP_DIR}/artisan"

# ANSI colours
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Colour

# ---------------------------------------------------------------------------
# Helper functions
# ---------------------------------------------------------------------------
log_info()  { echo -e "${BLUE}[INFO]${NC}  $(date '+%Y-%m-%d %H:%M:%S') $*"; }
log_ok()    { echo -e "${GREEN}[OK]${NC}    $(date '+%Y-%m-%d %H:%M:%S') $*"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC}  $(date '+%Y-%m-%d %H:%M:%S') $*"; }
log_error() { echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') $*"; }

abort() {
    log_error "$*"
    exit 1
}

# ---------------------------------------------------------------------------
# 1. Validate environment argument
# ---------------------------------------------------------------------------
if [[ -z "${ENVIRONMENT}" ]]; then
    abort "Environment argument required. Usage: bash deploy.sh [production|staging]"
fi

if [[ "${ENVIRONMENT}" != "production" && "${ENVIRONMENT}" != "staging" ]]; then
    abort "Invalid environment '${ENVIRONMENT}'. Must be 'production' or 'staging'."
fi

log_info "Starting deployment for [${ENVIRONMENT}] environment..."
log_info "Application directory: ${APP_DIR}"

# ---------------------------------------------------------------------------
# 2. Enter maintenance mode
# ---------------------------------------------------------------------------
log_info "Entering maintenance mode..."
${ARTISAN} down --retry=60 --refresh=15 || true
log_ok "Maintenance mode enabled."

# ---------------------------------------------------------------------------
# 3. Pull latest code
# ---------------------------------------------------------------------------
log_info "Pulling latest code from remote..."
cd "${APP_DIR}"
git pull origin "$(git rev-parse --abbrev-ref HEAD)"
log_ok "Code updated."

# ---------------------------------------------------------------------------
# 4. Install Composer dependencies (production-optimised)
# ---------------------------------------------------------------------------
log_info "Installing Composer dependencies..."
${COMPOSER_BIN} install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --working-dir="${APP_DIR}"
log_ok "Composer dependencies installed."

# ---------------------------------------------------------------------------
# 5. Install NPM dependencies and build front-end assets
# ---------------------------------------------------------------------------
log_info "Installing NPM dependencies..."
${NPM_BIN} ci --prefix="${APP_DIR}"
log_ok "NPM dependencies installed."

log_info "Building front-end assets (Vite)..."
${NPM_BIN} run build --prefix="${APP_DIR}"
log_ok "Front-end assets built."

# ---------------------------------------------------------------------------
# 6. Run database migrations
# ---------------------------------------------------------------------------
log_info "Running database migrations..."
${ARTISAN} migrate --force
log_ok "Migrations completed."

# ---------------------------------------------------------------------------
# 7. Clear and re-cache configuration, routes, views, events
# ---------------------------------------------------------------------------
log_info "Clearing caches..."
${ARTISAN} config:clear
${ARTISAN} route:clear
${ARTISAN} view:clear
${ARTISAN} event:clear
log_ok "Caches cleared."

log_info "Re-caching configuration, routes, views, events..."
${ARTISAN} config:cache
${ARTISAN} route:cache
${ARTISAN} view:cache
${ARTISAN} event:cache
log_ok "Caches rebuilt."

# ---------------------------------------------------------------------------
# 8. Restart queue workers
# ---------------------------------------------------------------------------
log_info "Restarting queue workers..."
${ARTISAN} queue:restart
log_ok "Queue restart signal sent."

# ---------------------------------------------------------------------------
# 9. Set proper file permissions
# ---------------------------------------------------------------------------
log_info "Setting file permissions..."
chmod -R 775 "${APP_DIR}/storage"
chmod -R 775 "${APP_DIR}/bootstrap/cache"

# Ensure the web server user owns writable directories
if id -u www-data >/dev/null 2>&1; then
    chown -R www-data:www-data "${APP_DIR}/storage"
    chown -R www-data:www-data "${APP_DIR}/bootstrap/cache"
    log_ok "Permissions set (owner: www-data)."
else
    log_warn "User www-data not found. Skipping chown. Set ownership manually if needed."
fi

# ---------------------------------------------------------------------------
# 10. Optimise class autoloader
# ---------------------------------------------------------------------------
log_info "Optimising autoloader..."
${COMPOSER_BIN} dump-autoload --optimize --working-dir="${APP_DIR}"
log_ok "Autoloader optimised."

# ---------------------------------------------------------------------------
# 11. Exit maintenance mode
# ---------------------------------------------------------------------------
log_info "Exiting maintenance mode..."
${ARTISAN} up
log_ok "Application is live."

# ---------------------------------------------------------------------------
# Done
# ---------------------------------------------------------------------------
echo ""
echo -e "${GREEN}=============================================================================${NC}"
echo -e "${GREEN} Sistem Pelaporan - Deployment Complete${NC}"
echo -e "${GREEN} Environment : ${ENVIRONMENT}${NC}"
echo -e "${GREEN} Timestamp   : $(date '+%Y-%m-%d %H:%M:%S %Z')${NC}"
echo -e "${GREEN}=============================================================================${NC}"
echo ""
