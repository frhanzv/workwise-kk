# Auto-Start RFID Listener Service

Choose one of these methods to run the RFID listener automatically:

---

## ✅ Method 1: Windows Service (RECOMMENDED)

Runs as a background service that starts automatically with Windows.

### Installation:

1. **Right-click** `install-rfid-service.bat`
2. Select **"Run as administrator"**
3. Wait for installation to complete
4. Done! Service is now running and will auto-start on boot

### Managing the Service:

**Check if service is running:**
```cmd
nssm status RFIDListener
```

**Stop the service:**
```cmd
nssm stop RFIDListener
```

**Start the service:**
```cmd
nssm start RFIDListener
```

**View logs:**
- Open: `writable\logs\rfid-listener.log`

**Uninstall service:**
```cmd
nssm remove RFIDListener confirm
```

---

## Method 2: Windows Startup (Silent Background)

Runs when you log in to Windows (no window shown).

### Setup:

1. **Press** `Win + R`
2. **Type**: `shell:startup` and press Enter
3. **Right-click** in the startup folder → New → Shortcut
4. **Browse to** `start-rfid-listener-hidden.vbs` in this project folder
5. **Name it**: "RFID Listener"
6. Click **Finish**

Now the listener will start automatically when you log in!

### Managing:

**Check if running:**
- Open Task Manager (Ctrl+Shift+Esc)
- Look for `php.exe` with command line containing `rfid:listen-all`

**Stop it:**
- End the `php.exe` process in Task Manager

**View logs:**
- Open: `writable\logs\log-[today].php`

---

## Method 3: Manual Start (Current Method)

Run manually when needed.

**Start:**
- Double-click `start-rfid-listener.bat`

**Stop:**
- Close the command window or press Ctrl+C

---

## Comparison

| Feature | Windows Service | Windows Startup | Manual |
|---------|----------------|-----------------|--------|
| Auto-start on boot | ✅ Yes | ✅ Yes (after login) | ❌ No |
| Runs without login | ✅ Yes | ❌ No | ❌ No |
| No visible window | ✅ Yes | ✅ Yes | ❌ No |
| Easy to manage | ✅ Yes | ⚠️ Medium | ✅ Yes |
| Best for | Production | Personal use | Testing |

---

## Troubleshooting

### Service won't start

**Check PHP path:**
```cmd
where php
```

If the path is different, edit the service:
```cmd
nssm edit RFIDListener
```

### Can't connect to reader

1. Check reader IP in: `app\Config\RFIDReader.php`
2. Verify reader is powered on and connected
3. Test connection: `php spark rfid:test-connection`

### View service logs

Open: `writable\logs\rfid-listener.log`

Look for connection errors or tag detection issues.

---

## Recommended Setup

For **production use**: Use Method 1 (Windows Service)
- Runs automatically
- Survives reboots
- Easy to manage
- Proper logging

For **development**: Use Method 3 (Manual)
- See real-time output
- Easy to stop/restart
- Good for debugging
