# Yanzeo SA810 UHF RFID Reader Implementation Guide

## Overview

This guide explains how to integrate the Yanzeo SA810 UHF RFID reader into your WorkWise attendance system. When workers tap their RFID card on the SA810 reader, their attendance will be automatically recorded.

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Database Migration](#database-migration)
3. [Configuration](#configuration)
4. [Hardware Setup](#hardware-setup)
5. [API Endpoints](#api-endpoints)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)

---

## Installation & Setup

### Step 1: Run Database Migration

The migration adds an `rfid_tag_id` field to the workers table to store RFID tag IDs.

```bash
php spark migrate
```

This will add the `rfid_tag_id` column to your `workers` table.

### Step 2: Verify Files Created

The following files have been created:

- **Migration**: `app/Database/Migrations/2025-12-23-000001_AddRfidTagToWorkersTable.php`
- **Library**: `app/Libraries/YanzeoSA810.php`
- **Controller**: `app/Controllers/RFID.php`
- **Config**: `app/Config/RFIDReader.php`
- **Routes**: Updated in `app/Config/Routes.php`
- **Model**: Updated `app/Models/WorkerModel.php`

---

## Database Migration

### Manual SQL (if needed)

If you prefer to run SQL manually instead of migration:

```sql
ALTER TABLE `workers` 
ADD COLUMN `rfid_tag_id` VARCHAR(100) NULL UNIQUE 
COMMENT 'UHF RFID tag ID from Yanzeo SA810 reader' 
AFTER `worker_id`;
```

---

## Configuration

### 1. Configure RFID Reader Settings

Edit `app/Config/RFIDReader.php`:

```php
// Reader IP address (change to your reader's IP)
public string $readerIP = '192.168.1.100';

// Reader TCP port (default is usually 6000 or 8080)
public int $readerPort = 6000;

// Reader identifier
public string $readerID = 'SA810_001';

// Protocol format (hex, json, wiegand, or default)
public string $protocol = 'hex';

// Default zone ID for attendance
public int $defaultZoneID = 1;
```

### 2. Configure Reader-to-Zone Mapping

If you have multiple readers at different zones:

```php
public array $readerZoneMapping = [
    'SA810_001' => 1,  // Main entrance
    'SA810_002' => 2,  // Warehouse
    'SA810_003' => 3,  // Office
];
```

### 3. Update Zone Table (Optional)

If you want to associate readers with zones in the database, add a `reader_id` column to the zones table:

```sql
ALTER TABLE `zones` ADD COLUMN `reader_id` VARCHAR(50) NULL;
UPDATE `zones` SET `reader_id` = 'SA810_001' WHERE `zone_id` = 1;
```

---

## Hardware Setup

### Connection Methods

The Yanzeo SA810 supports multiple connection methods:

#### Method 1: Network/TCP Connection (Recommended)

1. **Connect SA810 to your network**
   - Use Ethernet cable to connect reader to your network
   - Power on the reader

2. **Find the reader's IP address**
   - Default IP is usually: `192.168.1.100`
   - Check the reader's display or use the manufacturer's tool
   - Or scan your network: `ping 192.168.1.100`

3. **Configure the reader**
   - Access the web interface: `http://192.168.1.100`
   - Set IP address, port, and output format
   - Configure to send HTTP POST requests to your server

4. **Set the webhook URL in SA810**
   - In the SA810 web configuration, set the HTTP POST URL to:
     ```
     http://your-server-ip/api/rfid/tag-read
     ```
   - Example: `http://192.168.1.50/api/rfid/tag-read`

#### Method 2: Serial Connection (RS232/RS485)

1. Connect SA810 to your server via USB-to-Serial adapter
2. Update configuration:
   ```php
   public ?string $serialPort = 'COM3'; // Windows
   // or
   public ?string $serialPort = '/dev/ttyUSB0'; // Linux
   ```

#### Method 3: Wiegand Output

Connect to a Wiegand-compatible controller or use a Wiegand-to-TCP converter.

### SA810 Configuration Settings

Access the SA810 web interface and configure:

1. **Network Settings**
   - IP Address: `192.168.1.100` (or your preferred IP)
   - Subnet Mask: `255.255.255.0`
   - Gateway: Your network gateway

2. **Output Settings**
   - Protocol: `TCP/IP` or `HTTP`
   - Format: `Hexadecimal` or `JSON`
   - Enable: `Auto-upload when tag detected`

3. **Antenna Settings**
   - Enable antennas 1-4 (based on your setup)
   - RF Power: 26 dBm (adjust based on read range needed)

4. **Reading Mode**
   - Set to: `Continuous` or `Trigger`
   - Scan interval: 1000ms (1 second)

---

## API Endpoints

### 1. Main Webhook Endpoint (for SA810)

**URL**: `POST /api/rfid/tag-read`

This is where the SA810 reader sends tag data.

**Expected Data Format** (configure SA810 to send):

```json
{
  "tag_id": "E2003412EF1234567890ABCD",
  "reader_id": "SA810_001",
  "timestamp": "2025-12-23 10:30:45",
  "antenna": 1
}
```

**Response**:

```json
{
  "success": true,
  "message": "Check-in recorded successfully",
  "action": "checkin",
  "worker": {
    "id": "W001",
    "name": "John Doe",
    "photo": "profile.jpg"
  },
  "zone": {
    "id": 1,
    "name": "Main Entrance"
  },
  "time": "10:30:45"
}
```

### 2. Test Endpoint (for development)

**URL**: `GET /api/rfid/scan?tag_id=ABC123`

Test attendance recording without the physical reader.

**Example**:
```bash
curl "http://localhost/api/rfid/scan?tag_id=E2003412EF1234567890ABCD"
```

### 3. Status Endpoint

**URL**: `GET /api/rfid/status`

Check reader connection status.

**Response**:
```json
{
  "connected": true,
  "reader_id": "SA810_001",
  "ip": "192.168.1.100",
  "port": 6000,
  "protocol": "hex"
}
```

### 4. Test Connection

**URL**: `GET /api/rfid/test-connection`

Test if the server can connect to the reader.

### 5. Manual Attendance Entry

**URL**: `POST /api/rfid/manual`

Manually record attendance (backup method).

**Body**:
```json
{
  "worker_id": "W001",
  "zone_id": 1,
  "action": "in"
}
```

---

## Testing

### Step 1: Assign RFID Tags to Workers

1. Go to Workers page
2. Edit a worker
3. Add their RFID tag ID in the `rfid_tag_id` field
4. Save

Or via SQL:
```sql
UPDATE workers SET rfid_tag_id = 'E2003412EF1234567890ABCD' WHERE worker_id = 'W001';
```

### Step 2: Test with Browser

Test the endpoint without the physical reader:

```
http://localhost/api/rfid/scan?tag_id=E2003412EF1234567890ABCD
```

You should see a JSON response with the attendance record.

### Step 3: Test with Physical Reader

1. Configure SA810 to send HTTP POST to your server
2. Scan an RFID tag
3. Check the attendance records in your system
4. Check logs: `writable/logs/log-*.php`

### Step 4: Verify Attendance Records

```sql
SELECT * FROM attendance_records ORDER BY created_at DESC LIMIT 10;
```

---

## How It Works

### Attendance Flow

1. **Worker taps RFID card** on SA810 reader
2. **SA810 reads the UHF RFID tag** and gets the tag ID
3. **SA810 sends HTTP POST** to your server (`/api/rfid/tag-read`)
4. **RFID Controller** receives the data
5. **System looks up the worker** by `rfid_tag_id`
6. **System checks** if worker has an active check-in for today
   - **If NO active check-in**: Records a **check-in**
   - **If active check-in exists**: Records a **check-out**
7. **Response sent back** to the reader (can trigger LED/buzzer on reader)
8. **Worker's last_active** time is updated

### Check-in/Check-out Logic

- **First tap of the day** = Check-in
- **Second tap** = Check-out
- **Third tap** = New check-in (if they left and came back)
- Each check-in/out is tied to a specific **zone**

---

## Integration with Workers UI

### Adding RFID Field to Worker Form

Update your worker edit form (`app/Views/workers/edit.php` or `add.php`):

```php
<div class="form-group">
    <label for="rfid_tag_id">RFID Tag ID</label>
    <input type="text" 
           class="form-control" 
           id="rfid_tag_id" 
           name="rfid_tag_id" 
           value="<?= old('rfid_tag_id', $worker['rfid_tag_id'] ?? '') ?>"
           placeholder="E2003412EF1234567890ABCD">
    <small class="form-text text-muted">
        UHF RFID tag ID from worker's card (scan with reader to get ID)
    </small>
</div>
```

### Display RFID Status in Worker List

```php
<?php if (!empty($worker['rfid_tag_id'])): ?>
    <span class="badge badge-success">
        <i class="material-icons" style="font-size: 14px;">credit_card</i>
        RFID Enabled
    </span>
<?php else: ?>
    <span class="badge badge-secondary">
        <i class="material-icons" style="font-size: 14px;">credit_card_off</i>
        No RFID
    </span>
<?php endif; ?>
```

---

## Troubleshooting

### Issue: Reader not connecting

**Solution**:
1. Check IP address and port in config
2. Verify reader is on the same network
3. Ping the reader: `ping 192.168.1.100`
4. Check firewall settings
5. Test connection: `http://localhost/api/rfid/test-connection`

### Issue: Tags not being recognized

**Solution**:
1. Verify RFID tag IDs are stored correctly in database
2. Check protocol setting matches reader output format
3. Check logs for raw data: `writable/logs/log-*.php`
4. Test with scan endpoint first

### Issue: Duplicate attendance records

**Solution**:
- Adjust `readDebounceSeconds` in config
- Reader might be sending multiple reads - configure scan interval

### Issue: Wrong zone assignment

**Solution**:
- Verify `readerZoneMapping` in config
- Or add `reader_id` to zones table and link properly

### Issue: Worker not found

**Solution**:
- Ensure worker's `rfid_tag_id` exactly matches the tag being scanned
- Tag IDs are case-sensitive
- Check for spaces or special characters

---

## Advanced Configuration

### Multiple Readers Setup

If you have multiple SA810 readers at different locations:

1. **Configure each reader** with a unique `reader_id`
2. **Map readers to zones** in config:
   ```php
   public array $readerZoneMapping = [
       'SA810_ENTRANCE' => 1,
       'SA810_WAREHOUSE' => 2,
       'SA810_OFFICE' => 3,
   ];
   ```

3. **Set webhook URL** on each reader to the same endpoint:
   ```
   http://your-server/api/rfid/tag-read
   ```

### Auto Check-out

Enable automatic check-out after a certain period:

```php
public bool $autoCheckout = true;
public int $autoCheckoutHours = 12;
```

Create a cron job to run:
```bash
*/30 * * * * php /path/to/spark rfid:auto-checkout
```

### Custom Notifications

Enable notifications when workers check in/out:

```php
public bool $enableNotifications = true;
public array $notificationEmails = [
    'hr@company.com',
];
```

---

## Security Considerations

1. **Restrict API access**: Consider adding IP whitelist for RFID reader
2. **Use HTTPS**: Secure communication between reader and server
3. **Tag encryption**: Consider encrypted RFID tags for sensitive areas
4. **Audit logs**: All RFID reads are logged automatically
5. **Access control**: Limit who can edit RFID tag assignments

---

## API Documentation for SA810 Configuration

When configuring the SA810 reader's HTTP upload settings:

| Setting | Value |
|---------|-------|
| Upload Method | HTTP POST |
| URL | `http://your-server-ip/api/rfid/tag-read` |
| Content Type | application/json |
| Format | JSON or Hex |
| Upload Trigger | On Tag Read |

**Data Template** (configure in SA810):
```
{"tag_id":"{{EPC}}","reader_id":"{{READER_ID}}","timestamp":"{{TIMESTAMP}}","antenna":{{ANTENNA}}}
```

---

## Support

For issues related to:
- **CodeIgniter integration**: Check logs in `writable/logs/`
- **SA810 hardware**: Consult Yanzeo documentation or support
- **Network connectivity**: Check firewall, router, and network settings

## Logs

All RFID events are logged to:
```
writable/logs/log-YYYY-MM-DD.php
```

Enable detailed logging in config:
```php
public bool $logAllReads = true;
public bool $logUnregisteredTags = true;
```

---

## Summary Checklist

- [ ] Run database migration: `php spark migrate`
- [ ] Configure RFID reader IP and port in `app/Config/RFIDReader.php`
- [ ] Connect SA810 to network and power it on
- [ ] Configure SA810 to send HTTP POST to `/api/rfid/tag-read`
- [ ] Assign RFID tag IDs to workers in the database
- [ ] Test with: `http://localhost/api/rfid/scan?tag_id=TEST123`
- [ ] Test with physical reader by scanning a tag
- [ ] Verify attendance records are created
- [ ] Check logs for any errors
- [ ] Add RFID field to worker forms (optional UI enhancement)

---

**Congratulations!** Your Yanzeo SA810 RFID reader is now integrated with your attendance system.
