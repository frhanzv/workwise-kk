# Auto-Start RFID Listener on Linux

This guide shows how to run the RFID listener automatically on Linux systems.

---

## ✅ Method 1: Systemd Service (RECOMMENDED)

Runs as a system service that starts automatically on boot.

### Quick Installation:

```bash
cd /var/www/workwise  # or your project directory
sudo bash install-rfid-service-linux.sh
```

### Manual Installation:

1. **Copy the service file:**
```bash
sudo cp rfid-listener.service /etc/systemd/system/
```

2. **Edit paths if needed:**
```bash
sudo nano /etc/systemd/system/rfid-listener.service
```

Update these lines if your paths are different:
- `WorkingDirectory=/var/www/workwise`
- `ExecStart=/usr/bin/php /var/www/workwise/spark rfid:listen`
- `User=www-data` (might be `apache`, `nginx`, or your username)

3. **Reload systemd:**
```bash
sudo systemctl daemon-reload
```

4. **Enable and start service:**
```bash
sudo systemctl enable rfid-listener
sudo systemctl start rfid-listener
```

### Managing the Service:

**Check status:**
```bash
sudo systemctl status rfid-listener
```

**View live logs:**
```bash
sudo journalctl -u rfid-listener -f
```

**View recent logs:**
```bash
sudo journalctl -u rfid-listener -n 100
```

**Stop service:**
```bash
sudo systemctl stop rfid-listener
```

**Start service:**
```bash
sudo systemctl start rfid-listener
```

**Restart service:**
```bash
sudo systemctl restart rfid-listener
```

**Disable auto-start:**
```bash
sudo systemctl disable rfid-listener
```

**Uninstall service:**
```bash
sudo systemctl stop rfid-listener
sudo systemctl disable rfid-listener
sudo rm /etc/systemd/system/rfid-listener.service
sudo systemctl daemon-reload
```

---

## Method 2: Cron (Alternative)

Use cron to keep the listener running.

### Setup:

1. **Create a wrapper script:**
```bash
nano ~/rfid-keepalive.sh
```

2. **Add this content:**
```bash
#!/bin/bash
if ! pgrep -f "rfid:listen" > /dev/null; then
    cd /var/www/workwise
    nohup php spark rfid:listen >> writable/logs/rfid-listener.log 2>&1 &
fi
```

3. **Make it executable:**
```bash
chmod +x ~/rfid-keepalive.sh
```

4. **Add to crontab:**
```bash
crontab -e
```

Add this line:
```
*/5 * * * * /home/yourusername/rfid-keepalive.sh
```

This checks every 5 minutes and restarts if not running.

---

## Method 3: Screen/Tmux Session

Run in a persistent terminal session.

### Using Screen:

**Start:**
```bash
screen -dmS rfid-listener bash -c "cd /var/www/workwise && php spark rfid:listen"
```

**Attach to view:**
```bash
screen -r rfid-listener
```

**Detach:** Press `Ctrl+A` then `D`

**Stop:**
```bash
screen -X -S rfid-listener quit
```

### Using Tmux:

**Start:**
```bash
tmux new-session -d -s rfid-listener "cd /var/www/workwise && php spark rfid:listen"
```

**Attach to view:**
```bash
tmux attach -t rfid-listener
```

**Detach:** Press `Ctrl+B` then `D`

**Stop:**
```bash
tmux kill-session -t rfid-listener
```

---

## Method 4: Supervisor (Production Alternative)

Use Supervisor for process management.

### Installation:

**Ubuntu/Debian:**
```bash
sudo apt-get install supervisor
```

**CentOS/RHEL:**
```bash
sudo yum install supervisor
sudo systemctl enable supervisord
sudo systemctl start supervisord
```

### Configuration:

1. **Create config file:**
```bash
sudo nano /etc/supervisor/conf.d/rfid-listener.conf
```

2. **Add configuration:**
```ini
[program:rfid-listener]
command=/usr/bin/php /var/www/workwise/spark rfid:listen
directory=/var/www/workwise
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/www/workwise/writable/logs/rfid-listener.log
```

3. **Update supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start rfid-listener
```

### Managing with Supervisor:

**Check status:**
```bash
sudo supervisorctl status rfid-listener
```

**Start/Stop/Restart:**
```bash
sudo supervisorctl start rfid-listener
sudo supervisorctl stop rfid-listener
sudo supervisorctl restart rfid-listener
```

**View logs:**
```bash
sudo supervisorctl tail rfid-listener
```

---

## Comparison

| Feature | Systemd | Cron | Screen/Tmux | Supervisor |
|---------|---------|------|-------------|------------|
| Auto-start on boot | ✅ Yes | ⚠️ Manual | ❌ No | ✅ Yes |
| Auto-restart on crash | ✅ Yes | ⚠️ Delayed (5min) | ❌ No | ✅ Yes |
| View live output | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes |
| Easy to manage | ✅ Very easy | ⚠️ Medium | ⚠️ Manual | ✅ Easy |
| Built-in logging | ✅ Yes | ⚠️ Basic | ⚠️ Manual | ✅ Yes |
| Best for | Modern Linux | Simple setup | Development | Production apps |

---

## Troubleshooting

### Service won't start

**Check PHP path:**
```bash
which php
```

Update the service file if path is different.

**Check permissions:**
```bash
ls -la /var/www/workwise/spark
chmod +x /var/www/workwise/spark
```

**Check writable directory:**
```bash
sudo chown -R www-data:www-data /var/www/workwise/writable
sudo chmod -R 775 /var/www/workwise/writable
```

### View detailed logs

**Systemd logs:**
```bash
sudo journalctl -u rfid-listener -xe
```

**Application logs:**
```bash
tail -f /var/www/workwise/writable/logs/rfid-listener.log
tail -f /var/www/workwise/writable/logs/log-$(date +%Y-%m-%d).php
```

### Test manually first

Before setting up auto-start, test the command:
```bash
cd /var/www/workwise
php spark rfid:listen
```

If it works, then set up the service.

### Check if already running

```bash
ps aux | grep "rfid:listen"
```

Kill existing process if needed:
```bash
pkill -f "rfid:listen"
```

---

## Recommended Setup

**For Ubuntu/Debian/CentOS (systemd):** Use Method 1 (Systemd Service)
- Native to modern Linux
- Excellent logging
- Easy management
- Best integration

**For older systems:** Use Method 4 (Supervisor)
- Reliable process management
- Good for multiple services
- Proven in production

**For development:** Use Method 3 (Screen/Tmux)
- Quick to start/stop
- See real-time output
- No configuration needed
