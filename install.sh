#!/bin/bash
# c-helper installation script

set -e

echo "╔════════════════════════════════════════╗"
echo "║   c-helper - Universal AI Dev Proxy   ║"
echo "║   by Steve Wallis & Claude            ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP not found. Please install PHP 7.4 or higher."
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✅ Found PHP $PHP_VERSION"

# Check SQLite
if ! php -m | grep -q sqlite3; then
    echo "❌ PHP SQLite3 extension not found."
    echo "   Install with: sudo apt-get install php-sqlite3"
    exit 1
fi

echo "✅ SQLite3 extension available"

# Generate API key
API_KEY="dev-$(date +%s)"
echo ""
echo "🔑 Generated API key: $API_KEY"
echo ""

# Create .env file
cat > .env << EOF
# c-helper configuration
C_HELPER_KEY=$API_KEY
C_HELPER_DB=./c-helper.db
C_HELPER_BASE=$(pwd)
EOF

echo "✅ Created .env file"

# Create systemd service (optional)
read -p "📦 Create systemd service? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    sudo tee /etc/systemd/system/c-helper.service > /dev/null << EOF
[Unit]
Description=c-helper API Server
After=network.target

[Service]
Type=simple
User=$USER
WorkingDirectory=$(pwd)
EnvironmentFile=$(pwd)/.env
ExecStart=/usr/bin/php -S 0.0.0.0:8888 $(pwd)/c-helper-server.php
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
    
    sudo systemctl daemon-reload
    echo "✅ Systemd service created"
    echo "   Start with: sudo systemctl start c-helper"
    echo "   Enable on boot: sudo systemctl enable c-helper"
fi

echo ""
echo "🚀 Installation complete!"
echo ""
echo "Next steps:"
echo "  1. Start server: php -S 0.0.0.0:8888 c-helper-server.php"
echo "  2. Register project: curl -X POST http://localhost:8888/api/projects \\"
echo "       -H \"X-API-Key: $API_KEY\" \\"
echo "       -H \"Content-Type: application/json\" \\"
echo "       -d '{\"name\": \"my-project\", \"path\": \"$(pwd)\"}'"
echo ""
echo "API Key saved to .env - keep it secret!"
