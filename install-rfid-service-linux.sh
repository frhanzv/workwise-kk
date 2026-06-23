#!/bin/bash

# RFID Listener Service Installation Script for Linux
# Run as root or with sudo

set -e

echo "================================================"
echo "   Install RFID Listener as Linux Service"
echo "================================================"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "ERROR: This script must be run as root or with sudo"
    echo "Usage: sudo bash install-rfid-service-linux.sh"
    exit 1
fi

# Detect web server user
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
else
    WEB_USER="$SUDO_USER"
fi

echo "Detected web user: $WEB_USER"
echo ""

# Get the current directory (where workwise is located)
WORKWISE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "WorkWise directory: $WORKWISE_DIR"
echo ""

# Create systemd service file
echo "Creating systemd service..."

cat > /etc/systemd/system/rfid-listener.service << EOF
[Unit]
Description=WorkWise RFID Listener Service
After=network.target

[Service]
Type=simple
User=$WEB_USER
Group=$WEB_USER
WorkingDirectory=$WORKWISE_DIR
ExecStart=/usr/bin/php $WORKWISE_DIR/spark rfid:listen
Restart=always
RestartSec=5
StandardOutput=append:$WORKWISE_DIR/writable/logs/rfid-listener.log
StandardError=append:$WORKWISE_DIR/writable/logs/rfid-listener-error.log

# Security
NoNewPrivileges=true
PrivateTmp=true

[Install]
WantedBy=multi-user.target
EOF

echo "Service file created at: /etc/systemd/system/rfid-listener.service"
echo ""

# Create log directory if it doesn't exist
mkdir -p "$WORKWISE_DIR/writable/logs"
chown -R $WEB_USER:$WEB_USER "$WORKWISE_DIR/writable/logs"

# Reload systemd
echo "Reloading systemd..."
systemctl daemon-reload

# Enable service (auto-start on boot)
echo "Enabling service to start on boot..."
systemctl enable rfid-listener.service

# Start service
echo "Starting service..."
systemctl start rfid-listener.service

echo ""
echo "================================================"
echo "   Service installed successfully!"
echo "================================================"
echo ""
echo "Service Name: rfid-listener"
echo "Status: $(systemctl is-active rfid-listener)"
echo "Startup Type: $(systemctl is-enabled rfid-listener)"
echo ""
echo "The RFID listener will now start automatically when the system boots."
echo ""
echo "Useful commands:"
echo "  - Check status:    sudo systemctl status rfid-listener"
echo "  - View logs:       sudo journalctl -u rfid-listener -f"
echo "  - Stop service:    sudo systemctl stop rfid-listener"
echo "  - Start service:   sudo systemctl start rfid-listener"
echo "  - Restart service: sudo systemctl restart rfid-listener"
echo "  - Disable service: sudo systemctl disable rfid-listener"
echo "  - Remove service:  sudo systemctl stop rfid-listener && sudo systemctl disable rfid-listener && sudo rm /etc/systemd/system/rfid-listener.service && sudo systemctl daemon-reload"
echo ""
echo "Logs are saved to: $WORKWISE_DIR/writable/logs/rfid-listener.log"
echo ""

# Show current status
echo "Current service status:"
systemctl status rfid-listener --no-pager

echo ""
echo "Installation complete!"
