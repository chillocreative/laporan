#!/bin/bash
# Laravel Deployment Script for cPanel
# Run this after cloning the repository

set -e  # Exit on error

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "================================================"
echo "  Laravel Application Deployment"
echo "  Repository: laporan"
echo "================================================"
echo ""

# Get the directory where the script is run from
REPO_DIR=$(pwd)

# Verify we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Not a Laravel project directory${NC}"
    echo "Please run this script from your Laravel project root"
    echo "Example: cd /home/chilloc1/public_html/laporan && bash deploy-laravel.sh"
    exit 1
fi

echo -e "${GREEN}✓ Laravel project detected${NC}"
echo -e "${BLUE}Working directory: $REPO_DIR${NC}"
echo ""

# Step 1: Environment file
echo "Step 1: Setting up environment file..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Created .env from .env.example${NC}"
        echo -e "${YELLOW}⚠ Please edit .env file with your database credentials${NC}"
    else
        echo -e "${RED}✗ No .env.example file found${NC}"
    fi
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi
echo ""

# Step 2: Install dependencies
echo "Step 2: Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${YELLOW}⚠ Composer not found, skipping...${NC}"
    echo "  Please install dependencies manually or install Composer"
fi
echo ""

# Step 3: Generate application key
echo "Step 3: Generating application key..."
if grep -q "APP_KEY=$" .env 2>/dev/null || ! grep -q "APP_KEY=" .env 2>/dev/null; then
    php artisan key:generate
    echo -e "${GREEN}✓ Application key generated${NC}"
else
    echo -e "${GREEN}✓ Application key already exists${NC}"
fi
echo ""

# Step 4: Set permissions
echo "Step 4: Setting correct permissions..."
chmod -R 755 storage bootstrap/cache 2>/dev/null || {
    echo -e "${YELLOW}⚠ Could not set permissions automatically${NC}"
    echo "  Please set manually:"
    echo "  chmod -R 755 storage bootstrap/cache"
}
echo -e "${GREEN}✓ Permissions updated${NC}"
echo ""

# Step 5: Storage link
echo "Step 5: Creating storage symlink..."
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Storage symlink created${NC}"
else
    echo -e "${GREEN}✓ Storage symlink already exists${NC}"
fi
echo ""

# Step 6: Database check
echo "Step 6: Database configuration..."
echo -e "${YELLOW}Do you want to run database migrations now?${NC}"
echo "Make sure your .env file has correct database settings"
read -p "Run migrations? (yes/no): " RUN_MIGRATIONS

if [ "$RUN_MIGRATIONS" = "yes" ]; then
    echo "Running migrations..."
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"

    echo ""
    read -p "Run seeders? (yes/no): " RUN_SEEDERS
    if [ "$RUN_SEEDERS" = "yes" ]; then
        php artisan db:seed --force
        echo -e "${GREEN}✓ Seeders completed${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Skipping database migrations${NC}"
    echo "  Run manually: php artisan migrate"
fi
echo ""

# Step 7: Cache optimization
echo "Step 7: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"
echo ""

# Step 8: Asset compilation (if needed)
echo "Step 8: Frontend assets..."
if [ -f "package.json" ]; then
    echo -e "${YELLOW}Frontend dependencies detected${NC}"
    echo "To build assets, run:"
    echo "  npm install"
    echo "  npm run build"
else
    echo -e "${GREEN}✓ No frontend build needed${NC}"
fi
echo ""

# Summary
echo "================================================"
echo -e "${GREEN}  ✓ Deployment Complete!${NC}"
echo "================================================"
echo ""
echo "Next Steps:"
echo ""
echo "1. Configure your .env file:"
echo "   nano .env"
echo ""
echo "2. Set your document root to:"
echo "   $REPO_DIR/public"
echo ""
echo "3. Test your application:"
echo "   Open your domain in a browser"
echo ""
echo "4. Check logs if needed:"
echo "   tail -f storage/logs/laravel.log"
echo ""

# Show document root info
echo "================================================"
echo "  Document Root Configuration"
echo "================================================"
echo ""
echo "In cPanel, set your domain's document root to:"
echo -e "${BLUE}$REPO_DIR/public${NC}"
echo ""
echo "Or create a subdomain pointing to the public folder"
echo ""

echo "Deployment script completed!"
echo ""
