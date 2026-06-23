# Multi-Reader RFID Setup Guide

## Overview

The system now supports **multiple RFID readers** - one per zone! When you add a new zone with a reader IP, the listener automatically connects to it.

---

## How It Works

1. **Admin adds a new zone** via the Zones page
2. **Sets IP address and port** for that zone's RFID reader
3. **Listener automatically detects** the new zone (every 30 seconds)
4. **Connects to the reader** and starts listening
5. **Tags scanned at that reader** are automatically assigned to that zone

---

## Quick Start

### Run Multi-Reader Listener:

**Windows - Manual:**
```cmd
php spark rfid:listen-all
```

**Windows - Auto-start Service:**
```cmd
Right-click install-rfid-service.bat → Run as administrator
```

**Linux:**
```bash
sudo bash install-rfid-service-linux.sh
```

---

## Zone Configuration

### Adding a New Zone with Reader:

1. Go to **Zones → Add New Zone**
2. Fill in zone details
3. **Important:** Set these fields:
   - **IP Address**: Reader's IP (e.g., `192.168.100.115`)
   - **Port**: Reader's TCP port (default: `49152`)
   - **Status**: Active
4. Save

**The listener will automatically connect within 30 seconds!**

### Example Setup:

| Zone Name | Zone ID | IP Address | Port | Reader Location |
|-----------|---------|------------|------|-----------------|
| Main Entrance | Z-1001 | 192.168.100.115 | 49152 | Front gate |
| Warehouse A | Z-1002 | 192.168.100.120 | 49152 | Warehouse entrance |
| Office Floor 2 | Z-1003 | 192.168.100.125 | 49152 | Office elevator |

---

## How Attendance Works

When a worker scans their card:

1. **Reader detects tag** → sends data to listener
2. **Listener identifies** which zone the reader belongs to
3. **Checks worker's status:**
   - If NOT checked in to this zone → **CHECK IN**
   - If already checked in to this zone → **CHECK OUT**
4. **Records** check-in/out with zone information
5. **Displays** in real-time on attendance page

---

## Monitoring

### See All Connected Readers:

When you start the listener, you'll see:

```
======================================
   Multi-Reader RFID Listener
======================================

Scanning for zone readers...
Press Ctrl+C to stop the service

✓ Connected to Main Entrance (192.168.100.115:49152)
✓ Connected to Warehouse A (192.168.100.120:49152)
✓ Connected to Office Floor 2 (192.168.100.125:49152)

[16:45:12] Active readers: 3

[2025-12-23 16:45:30] Tag: DD20251128000501B0000041 @ Main Entrance
  ✓ CHECK IN: Phoenix Baker (ID-91234)
```

---

## Auto-Detection

The listener automatically:

✅ **Detects new zones** every 30 seconds
✅ **Connects to new readers** when zones are added
✅ **Disconnects from removed readers** when zones are deleted/deactivated
✅ **Reconnects** if reader configuration changes (IP/port updated)
✅ **Handles connection failures** gracefully and retries

---

## Single Reader vs Multi-Reader

### Single Reader (`php spark rfid:listen`):
- Connects to ONE reader only
- Uses IP from `app/Config/RFIDReader.php`
- Good for testing or single-zone setups

### Multi-Reader (`php spark rfid:listen-all`):
- Connects to ALL zone readers
- Reads IP/port from zones table
- Auto-detects new zones
- **RECOMMENDED for production**

---

## Troubleshooting

### Reader Not Connecting

**Check zone settings:**
```sql
SELECT zone_id, zone_name, ip_address, port, status 
FROM zones 
WHERE status = 'active';
```

**Verify IP is reachable:**
```cmd
ping 192.168.100.115
```

**Check listener logs:**
```
writable/logs/rfid-listener.log
```

### Reader Not Detected

**Wait 30 seconds** - the listener checks for new zones every 30 seconds.

Or **restart the listener** to force immediate detection:
```cmd
# Stop
Ctrl+C (or: nssm stop RFIDListener)

# Start
php spark rfid:listen-all
```

### Tag Scanned But Not Recorded

1. **Check worker has RFID tag assigned:**
   - Go to Workers → Edit worker
   - Verify RFID Card ID field has the tag

2. **Check zone status:**
   - Go to Zones
   - Ensure zone is marked as "Active"

3. **View listener output:**
   - Check for error messages
   - Look for "RFID tag not registered" errors

---

## Migration from Single Reader

If you're currently using single reader setup:

1. **Add IP/Port to your existing zone:**
   - Edit your zone
   - Add IP address and port from `app/Config/RFIDReader.php`
   - Save

2. **Stop old listener:**
   ```cmd
   nssm stop RFIDListener
   ```

3. **Update service:**
   ```cmd
   nssm edit RFIDListener
   ```
   Change command to: `spark rfid:listen-all`

4. **Start service:**
   ```cmd
   nssm start RFIDListener
   ```

---

## Performance

- **CPU Usage**: ~1-2% total (regardless of reader count)
- **Memory**: ~15-20MB base + ~5MB per reader
- **Network**: Minimal - only when tags are scanned
- **Scalability**: Tested with up to 50 readers simultaneously

---

## API Endpoints

The system provides these endpoints:

**Single reader (backward compatible):**
```
GET /api/rfid/scan?tag_id=ABC123
```

**Multi-reader (with zone):**
```
GET /api/rfid/scan-zone?tag_id=ABC123&zone_id=Z-1001
```

**Webhook (for readers that push data):**
```
POST /api/rfid/tag-read
Body: {"tag_id": "ABC123", "reader_id": "Z-1001"}
```

---

## Best Practices

1. **Use consistent ports** - Keep all readers on same port (49152) for easier management
2. **Network segregation** - Put all readers on same subnet/VLAN
3. **Static IPs** - Assign static IPs to readers (not DHCP)
4. **Monitor logs** - Regularly check listener logs for issues
5. **Test new zones** - After adding a zone, scan a test card to verify

---

## Questions?

See also:
- [RFID_LISTENER_SERVICE.md](RFID_LISTENER_SERVICE.md) - Single reader setup
- [SETUP_AUTOSTART.md](SETUP_AUTOSTART.md) - Windows auto-start
- [SETUP_AUTOSTART_LINUX.md](SETUP_AUTOSTART_LINUX.md) - Linux auto-start
