# 🎉 Yanzeo SA810 RFID Integration - Complete Package

## 📦 What's Included

Your WorkWise attendance system now has complete RFID integration with the Yanzeo SA810 UHF RFID reader.

### Core Integration Files

| File | Purpose | Status |
|------|---------|--------|
| `app/Database/Migrations/2025-12-23-000001_AddRfidTagToWorkersTable.php` | Database migration | ✅ Ready |
| `app/Libraries/YanzeoSA810.php` | RFID reader library | ✅ Ready |
| `app/Controllers/RFID.php` | API controller | ✅ Ready |
| `app/Config/RFIDReader.php` | Configuration | ⚙️ Configure |
| `app/Config/Routes.php` | API routes | ✅ Ready |
| `app/Models/WorkerModel.php` | Enhanced model | ✅ Ready |

### Documentation Files

| File | Content |
|------|---------|
| `RFID_QUICKSTART.md` | Quick 5-step setup guide |
| `RFID_IMPLEMENTATION_GUIDE.md` | Complete implementation guide |
| `RFID_API_REFERENCE.md` | API endpoint documentation |
| `RFID_CONTROLLER_EXAMPLES.php` | Code examples for Workers controller |
| `RFID_VIEW_EXAMPLES.html` | HTML/UI code examples |

### Testing Tools

| File | Purpose |
|------|---------|
| `public/rfid-test.html` | Web-based testing interface |

---

## 🚀 Installation Steps

### 1️⃣ Database Setup
```bash
# Run migration to add rfid_tag_id field to workers table
php spark migrate
```

### 2️⃣ Configuration
Edit `app/Config/RFIDReader.php`:
```php
public string $readerIP = '192.168.1.100';     // ← Change this
public int $readerPort = 6000;                  // ← Verify this
public string $readerID = 'SA810_001';          // ← Name your reader
public int $defaultZoneID = 1;                  // ← Set default zone
```

### 3️⃣ Hardware Setup
1. Connect SA810 to your network via Ethernet
2. Power on the reader
3. Access reader's web interface: `http://192.168.1.100`
4. Configure HTTP POST settings:
   - **URL**: `http://your-server-ip/api/rfid/tag-read`
   - **Method**: `POST`
   - **Format**: `JSON` or `Hex`

### 4️⃣ Assign RFID Tags
```sql
-- Assign RFID tags to your workers
UPDATE workers SET rfid_tag_id = 'E2003412EF1234567890ABCD' WHERE worker_id = 'W001';
UPDATE workers SET rfid_tag_id = 'E2003412EF1234567890ABCE' WHERE worker_id = 'W002';
```

### 5️⃣ Test the System
Open in browser:
```
http://localhost/rfid-test.html
```

---

## 🎯 How It Works

### Attendance Workflow

```
┌─────────────┐
│   Worker    │
│  taps card  │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ SA810 Reader│ Reads UHF RFID tag
└──────┬──────┘
       │
       │ HTTP POST
       ▼
┌─────────────────┐
│ /api/rfid/      │
│  tag-read       │ API endpoint
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ RFID Controller │ Processes request
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Worker Model    │ Lookup by rfid_tag_id
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Attendance Model│ Check if already checked-in
└──────┬──────────┘
       │
       ├─► No active check-in → Record CHECK-IN
       │
       └─► Active check-in → Record CHECK-OUT
       
       ▼
┌─────────────────┐
│   Response      │ Success/Error message
└─────────────────┘
```

### Check-in/Check-out Logic

- **First tap** of the day → ✅ Check-in
- **Second tap** → ❌ Check-out
- **Third tap** → ✅ New check-in (if returning)
- Each check-in/out is tied to a **specific zone**

---

## 🔌 API Endpoints

### Main Webhook (for SA810)
```
POST /api/rfid/tag-read
```
This is where the SA810 sends tag data.

### Testing Endpoint
```
GET /api/rfid/scan?tag_id=ABC123
```
Test without hardware.

### Reader Status
```
GET /api/rfid/status
```
Check connection status.

### Test Connection
```
GET /api/rfid/test-connection
```
Verify server can connect to reader.

### Manual Entry
```
POST /api/rfid/manual
Body: {"worker_id":"W001", "zone_id":1, "action":"in"}
```
Manually record attendance.

---

## 🧪 Testing Scenarios

### Scenario 1: Test without hardware
```bash
# Simulate a tag scan
curl "http://localhost/api/rfid/scan?tag_id=ABC123"
```

### Scenario 2: Test with web interface
1. Open `http://localhost/rfid-test.html`
2. Enter a tag ID (e.g., `ABC123`)
3. Click "Scan Tag"
4. View the response

### Scenario 3: Test with real hardware
1. Configure SA810 to point to your server
2. Scan an RFID card
3. Check attendance records in database
4. View logs: `writable/logs/log-*.php`

---

## 🛠️ Customization Options

### Multiple Readers
```php
// In app/Config/RFIDReader.php
public array $readerZoneMapping = [
    'SA810_ENTRANCE' => 1,
    'SA810_WAREHOUSE' => 2,
    'SA810_OFFICE' => 3,
];
```

### Auto Check-out
```php
public bool $autoCheckout = true;
public int $autoCheckoutHours = 12;
```

### Protocol Selection
```php
public string $protocol = 'hex';  // or 'json', 'wiegand', 'default'
```

### Antenna Configuration
```php
public array $enabledAntennas = [1, 2, 3, 4];
public int $rfPower = 26;  // dBm
```

---

## 📱 UI Integration (Optional)

### Add RFID field to worker forms
See `RFID_VIEW_EXAMPLES.html` for complete HTML/JavaScript examples.

Quick snippet for edit form:
```html
<div class="form-group">
    <label for="rfid_tag_id">RFID Tag ID</label>
    <input type="text" name="rfid_tag_id" 
           value="<?= $worker['rfid_tag_id'] ?? '' ?>">
</div>
```

### Display RFID status in worker list
```php
<?php if (!empty($worker['rfid_tag_id'])): ?>
    <span class="badge badge-success">RFID Enabled</span>
<?php endif; ?>
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| Reader not connecting | Check IP/port, ping reader, verify firewall |
| Tags not recognized | Verify tag IDs in database, check protocol setting |
| Duplicate records | Adjust `readDebounceSeconds` in config |
| Wrong zone | Verify `readerZoneMapping` or add `reader_id` to zones table |
| Worker not found | Check tag ID matches exactly (case-sensitive) |

### View Logs
```bash
# Check logs for errors
tail -f writable/logs/log-*.php
```

### Enable Debug Logging
```php
// In app/Config/RFIDReader.php
public bool $logAllReads = true;
public bool $logUnregisteredTags = true;
```

---

## 📊 Database Schema

### Workers Table (Updated)
```sql
CREATE TABLE `workers` (
  `worker_id` VARCHAR(50) PRIMARY KEY,
  `rfid_tag_id` VARCHAR(100) NULL UNIQUE,  -- ← NEW FIELD
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  ...
);
```

### Attendance Records Table
```sql
CREATE TABLE `attendance_records` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `worker_id` VARCHAR(50) NOT NULL,
  `zone_id` INT NOT NULL,
  `check_in_time` DATETIME NOT NULL,
  `check_out_time` DATETIME NULL,
  `date` DATE NOT NULL,
  ...
);
```

---

## 🔐 Security Best Practices

1. **IP Whitelist**: Restrict API access to SA810 IP addresses
2. **HTTPS**: Use SSL for production deployment
3. **Authentication**: Add API key validation for webhook
4. **Rate Limiting**: Prevent abuse of API endpoints
5. **Audit Logs**: Enable detailed logging (already implemented)

---

## 📈 Performance Optimization

- **Database Indexes**: Already set on `rfid_tag_id` (UNIQUE)
- **Caching**: Consider caching worker lookups
- **Async Processing**: For high-traffic scenarios
- **Connection Pooling**: Reuse reader connections

---

## 🎓 Learning Resources

### SA810 Documentation
- Yanzeo website: [yanzeo.com](http://yanzeo.com)
- SA810 Manual (consult manufacturer)
- Network configuration guide

### CodeIgniter 4
- [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
- RESTful API development
- Database migrations

---

## ✅ Pre-flight Checklist

Before going live:

- [ ] Database migration completed
- [ ] Configuration file updated with correct IP/port
- [ ] SA810 connected to network
- [ ] SA810 configured to send POST requests
- [ ] Worker RFID tags assigned in database
- [ ] Testing completed with web interface
- [ ] Testing completed with real hardware
- [ ] Logs checked for errors
- [ ] Multiple scenarios tested (check-in/out)
- [ ] UI updated with RFID fields (optional)
- [ ] Documentation reviewed
- [ ] Backup created

---

## 📞 Support & Maintenance

### Log Files
```
writable/logs/log-YYYY-MM-DD.php
```

### Test Interface
```
http://localhost/rfid-test.html
```

### API Status Check
```bash
curl http://localhost/api/rfid/status
```

---

## 🎊 Success!

Your RFID attendance system is now complete and ready for production use!

### Quick Commands

```bash
# Run migration
php spark migrate

# View recent logs
tail -100 writable/logs/log-$(date +%Y-%m-%d).php

# Test endpoint
curl "http://localhost/api/rfid/scan?tag_id=TEST123"
```

---

**Created on**: December 23, 2025  
**System**: WorkWise Attendance Management  
**Integration**: Yanzeo SA810 UHF RFID Reader  
**Status**: ✅ Ready for Production
