#!/bin/bash
# SSH Key Setup Script for cPanel → GitHub
# Run this script in your cPanel Terminal

set -e  # Exit on error

echo "================================================"
echo "  GitHub SSH Key Setup for cPanel"
echo "  Repository: chillocreative/laporan"
echo "================================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Check if SSH directory exists
echo "Step 1: Checking SSH directory..."
if [ ! -d ~/.ssh ]; then
    echo -e "${YELLOW}Creating ~/.ssh directory...${NC}"
    mkdir -p ~/.ssh
    chmod 700 ~/.ssh
fi
echo -e "${GREEN}✓ SSH directory ready${NC}"
echo ""

# Step 2: Generate SSH key
KEY_FILE="$HOME/.ssh/id_ed25519_github"
echo "Step 2: Generating SSH key..."

if [ -f "$KEY_FILE" ]; then
    echo -e "${YELLOW}⚠ SSH key already exists at $KEY_FILE${NC}"
    read -p "Do you want to overwrite it? (yes/no): " OVERWRITE
    if [ "$OVERWRITE" != "yes" ]; then
        echo "Using existing key..."
    else
        echo "Generating new key..."
        ssh-keygen -t ed25519 -C "cpanel-laporan-deploy" -f "$KEY_FILE" -N ""
    fi
else
    echo "Generating new SSH key..."
    ssh-keygen -t ed25519 -C "cpanel-laporan-deploy" -f "$KEY_FILE" -N ""
fi

echo -e "${GREEN}✓ SSH key generated${NC}"
echo ""

# Step 3: Set correct permissions
echo "Step 3: Setting correct permissions..."
chmod 600 "$KEY_FILE"
chmod 644 "$KEY_FILE.pub"
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Step 4: Configure SSH config
CONFIG_FILE="$HOME/.ssh/config"
echo "Step 4: Configuring SSH..."

# Check if config exists and has GitHub entry
if [ -f "$CONFIG_FILE" ]; then
    if grep -q "Host github.com" "$CONFIG_FILE"; then
        echo -e "${YELLOW}⚠ GitHub SSH config already exists${NC}"
    else
        echo "Adding GitHub config..."
        cat >> "$CONFIG_FILE" << EOF

# GitHub Configuration for laporan deployment
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_github
    IdentitiesOnly yes
EOF
    fi
else
    echo "Creating SSH config..."
    cat > "$CONFIG_FILE" << EOF
# GitHub Configuration for laporan deployment
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_github
    IdentitiesOnly yes
EOF
fi

chmod 600 "$CONFIG_FILE"
echo -e "${GREEN}✓ SSH config updated${NC}"
echo ""

# Step 5: Add GitHub to known hosts
echo "Step 5: Adding GitHub to known hosts..."
if ! grep -q "github.com" ~/.ssh/known_hosts 2>/dev/null; then
    ssh-keyscan github.com >> ~/.ssh/known_hosts 2>/dev/null
    echo -e "${GREEN}✓ GitHub added to known hosts${NC}"
else
    echo -e "${GREEN}✓ GitHub already in known hosts${NC}"
fi
echo ""

# Step 6: Display public key
echo "================================================"
echo "  🔑 YOUR PUBLIC SSH KEY"
echo "================================================"
echo ""
echo -e "${YELLOW}Copy the key below and add it to GitHub:${NC}"
echo ""
cat "$KEY_FILE.pub"
echo ""
echo "================================================"
echo ""

# Step 7: Instructions
echo -e "${GREEN}Next Steps:${NC}"
echo ""
echo "1. Copy the SSH key shown above"
echo "2. Go to: https://github.com/settings/ssh/new"
echo "3. Add the key with title: 'cPanel Laporan Server'"
echo "4. Come back here and press Enter to test the connection"
echo ""
read -p "Press Enter when you've added the key to GitHub..."

# Step 8: Test connection
echo ""
echo "Step 6: Testing GitHub connection..."
echo ""

if ssh -T git@github.com 2>&1 | grep -q "successfully authenticated"; then
    echo -e "${GREEN}================================================${NC}"
    echo -e "${GREEN}  ✓ SUCCESS! SSH authentication is working!${NC}"
    echo -e "${GREEN}================================================${NC}"
    echo ""
    echo "You can now use SSH URLs for Git operations:"
    echo "  git@github.com:chillocreative/laporan.git"
    echo ""
else
    echo -e "${RED}================================================${NC}"
    echo -e "${RED}  ✗ Connection failed${NC}"
    echo -e "${RED}================================================${NC}"
    echo ""
    echo "Troubleshooting:"
    echo "1. Make sure you added the public key to GitHub"
    echo "2. Check: https://github.com/settings/keys"
    echo "3. Try manually: ssh -T git@github.com"
    echo ""
fi

echo ""
echo "Script completed!"
echo ""
