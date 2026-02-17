#!/bin/bash
# Quick Update Script - Pull latest changes from GitHub
# Run this whenever you want to update your application

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "================================================"
echo "  Update Laravel Application from GitHub"
echo "================================================"
echo ""

# Verify we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Not a Laravel project directory${NC}"
    exit 1
fi

# Check if it's a git repository
if [ ! -d ".git" ]; then
    echo -e "${RED}Error: Not a Git repository${NC}"
    exit 1
fi

echo -e "${BLUE}Current directory: $(pwd)${NC}"
echo ""

# Show current status
echo "Current Status:"
echo "---------------"
git status
echo ""

# Check for uncommitted changes
if [[ -n $(git status -s) ]]; then
    echo -e "${YELLOW}⚠ Warning: You have uncommitted changes${NC}"
    echo ""
    read -p "Do you want to stash these changes? (yes/no): " STASH_CHANGES
    if [ "$STASH_CHANGES" = "yes" ]; then
        git stash
        echo -e "${GREEN}✓ Changes stashed${NC}"
    else
        echo -e "${RED}Cannot pull with uncommitted changes${NC}"
        exit 1
    fi
fi
echo ""

# Pull latest changes
echo "Step 1: Pulling latest changes from GitHub..."
git pull origin main || git pull origin master
echo -e "${GREEN}✓ Code updated${NC}"
echo ""

# Update dependencies
echo "Step 2: Updating Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
    echo -e "${GREEN}✓ Dependencies updated${NC}"
else
    echo -e "${YELLOW}⚠ Composer not found, skipping${NC}"
fi
echo ""

# Run migrations
echo "Step 3: Running database migrations..."
read -p "Run migrations? (yes/no): " RUN_MIGRATIONS
if [ "$RUN_MIGRATIONS" = "yes" ]; then
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠ Skipping migrations${NC}"
fi
echo ""

# Clear and rebuild cache
echo "Step 4: Clearing and rebuilding cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Cache rebuilt${NC}"
echo ""

# Restore stashed changes if any
if [ "$STASH_CHANGES" = "yes" ]; then
    echo "Restoring stashed changes..."
    git stash pop
    echo -e "${GREEN}✓ Changes restored${NC}"
    echo ""
fi

# Summary
echo "================================================"
echo -e "${GREEN}  ✓ Update Complete!${NC}"
echo "================================================"
echo ""
echo "Latest changes from GitHub have been deployed"
echo ""
echo "If you have frontend assets, run:"
echo "  npm install && npm run build"
echo ""
echo "Check your application to verify everything works"
echo ""
