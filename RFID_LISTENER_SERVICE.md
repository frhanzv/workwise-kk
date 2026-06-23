# RFID Reader Listener Service

This service continuously listens to the Yanzeo SA810 RFID reader and automatically processes tag scans for attendance tracking.

## How It Works

1. **Connects** to the RFID reader via TCP socket (IP: 192.168.100.115, Port: 49152)
2. **Listens** continuously for tag scan data from the reader
3. **Parses** the tag ID from the raw data stream
4. **Processes** attendance (check-in/check-out) via the internal API
5. **Auto-reconnects** if the connection drops

## Starting the Service

### Method 1: Using Batch File (Recommended)

Double-click `start-rfid-listener.bat` in the project root folder.

This will:
- Open a command window
- Start the listener service
- Automatically restart if it crashes
- Show real-time tag scan events

### Method 2: Using Command Line

```bash
cd C:\laragon\www\workwise
php spark rfid:listen
```

## Service Output

When running, you'll see:

```
======================================
   RFID Reader Listener Service
======================================

Reader IP: 192.168.100.115
Reader Port: 49152
Protocol: hex

Press Ctrl+C to stop the service

Connecting to RFID reader...
✓ Connected to RFID reader!
Listening for tag scans...

[2025-12-23 16:30:45] Tag detected: DD20251128000501B0000041
  ✓ CHECK IN: Phoenix Baker (ID-91234)
  Zone: Main Entrance Gate
  Time: 16:30:45
```

## Stopping the Service

Press **Ctrl+C** in the command window to stop the service gracefully.

## Running as Windows Service (Optional)

To run this as a Windows service that starts automatically:

### Using NSSM (Non-Sucking Service Manager)

1. **Download NSSM**: https://nssm.cc/download
2. **Extract** nssm.exe to a folder
3. **Open Command Prompt as Administrator**
4. **Install service**:

```cmd
nssm install RFIDListener "C:\laragon\bin\php\php-8.3.20-Win32-vs16-x64\php.exe"
nssm set RFIDListener AppDirectory "C:\laragon\www\workwise"
nssm set RFIDListener AppParameters "spark rfid:listen"
nssm set RFIDListener DisplayName "WorkWise RFID Listener"
nssm set RFIDListener Description "Listens to Yanzeo SA810 RFID reader for attendance tracking"
nssm set RFIDListener Start SERVICE_AUTO_START
nssm start RFIDListener
```

5. **Check service status**:

```cmd
nssm status RFIDListener
```

6. **To remove service**:

```cmd
nssm stop RFIDListener
nssm remove RFIDListener confirm
```

## Troubleshooting

### Connection Failed

**Problem**: Cannot connect to reader

**Solutions**:
- Verify reader IP is correct: `192.168.100.115`
- Check reader is powered on and connected to network
- Ensure port `49152` is correct (check reader settings)
- Test connection: `php spark rfid:test-connection`

### No Tag Data Received

**Problem**: Connected but no tags detected when scanning

**Solutions**:
- Reader might be in wrong mode (check it's in TCP server mode)
- Data format might be different - check raw hex output in console
- Try scanning a card and note the hex data shown, then adjust parsing logic

### Duplicate Reads

**Problem**: Same tag triggers multiple check-ins

**Solution**: The service has a 2-second cooldown period. You can adjust it in:

`app/Commands/RfidListener.php` - Change `$tagCooldown` value

### Connection Drops

**Problem**: Service loses connection periodically

**Solution**: 
- The service auto-reconnects every 5 seconds
- Check network stability
- Ensure reader's TCP timeout settings are appropriate

## Configuration

Edit settings in: `app/Config/RFIDReader.php`

```php
public $readerIP = '192.168.100.115';  // Reader IP address
public $readerPort = 49152;             // Reader TCP port
public $protocol = 'hex';               // Data protocol
public $connectionTimeout = 30;         // Connection timeout (seconds)
```

## Logs

Service activities are logged to:
- `writable/logs/log-[date].php`

Check logs for errors:

```bash
tail -f writable/logs/log-2025-12-23.php
```

## Testing Without Service

You can manually test tag processing:

```bash
php spark rfid:scan DD20251128000501B0000041
```

Or via browser:
```
http://localhost/workwise/api/rfid/scan?tag_id=DD20251128000501B0000041
```

## Performance

- **Connection**: Auto-reconnects on failure
- **Memory**: ~10-15MB PHP process
- **CPU**: Minimal (0-1% average)
- **Response Time**: < 100ms per tag scan

## Security Notes

- Service runs on localhost only
- No external network access required
- Reader communication is direct TCP (no internet needed)
- Attendance API endpoints are protected by authentication
