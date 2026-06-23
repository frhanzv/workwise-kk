# Yanzeo SA810 RFID Integration - Quick Start

## ✅ Implementation Complete!

The Yanzeo SA810 UHF RFID reader has been successfully integrated into your WorkWise attendance system.

## 📁 Files Created/Modified

### New Files:
1. **Migration**: `app/Database/Migrations/2025-12-23-000001_AddRfidTagToWorkersTable.php`
2. **Library**: `app/Libraries/YanzeoSA810.php`
3. **Controller**: `app/Controllers/RFID.php`
4. **Config**: `app/Config/RFIDReader.php`
5. **Documentation**: `RFID_IMPLEMENTATION_GUIDE.md`
6. **API Reference**: `RFID_API_REFERENCE.md`
7. **Test Interface**: `public/rfid-test.html`

### Modified Files:
1. **Routes**: `app/Config/Routes.php` (added API endpoints)
2. **Model**: `app/Models/WorkerModel.php` (added RFID methods)

## 🚀 Quick Setup (5 Steps)

### 1. Run Database Migration
```bash
php spark migrate
```

### 2. Configure Reader Settings
Edit `app/Config/RFIDReader.php`:
```php
public string $readerIP = '192.168.1.100';  // Your reader's IP
public int $readerPort = 6000;              // Reader's port
public string $readerID = 'SA810_001';      // Reader identifier
```

### 3. Configure SA810 Hardware
- Connect SA810 to your network
- Access web interface: `http://192.168.1.100`
- Set HTTP POST URL to: `http://your-server/api/rfid/tag-read`

### 4. Assign RFID Tags to Workers
```sql
UPDATE workers SET rfid_tag_id = 'E2003412EF1234567890ABCD' WHERE worker_id = 'W001';
```

### 5. Test the System
Open in browser: `http://localhost/rfid-test.html`

## 🔗 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/rfid/tag-read` | POST | Main webhook for SA810 |
| `/api/rfid/scan` | GET | Test endpoint |
| `/api/rfid/status` | GET | Reader status |
| `/api/rfid/test-connection` | GET | Test connection |
| `/api/rfid/manual` | POST | Manual attendance |

## 📖 How It Works

1. Worker taps RFID card on SA810 reader
2. SA810 reads the tag and sends data to your server
3. System looks up worker by `rfid_tag_id`
4. System records attendance:
   - **First tap** = Check-in
   - **Second tap** = Check-out
5. Response sent back to reader

## 🧪 Testing

### Test without hardware:
```bash
# Test tag scan
curl "http://localhost/api/rfid/scan?tag_id=ABC123"

# Test connection
curl "http://localhost/api/rfid/test-connection"
```

### Test with web interface:
Open: `http://localhost/rfid-test.html`

## 📚 Full Documentation

- **Complete Guide**: `RFID_IMPLEMENTATION_GUIDE.md`
- **API Reference**: `RFID_API_REFERENCE.md`

## ⚙️ Configuration Options

Edit `app/Config/RFIDReader.php` for:
- Multiple reader setup
- Zone mapping
- Auto check-out
- Protocol settings
- Antenna configuration
- And more...

## 🔧 Troubleshooting

**Reader not connecting?**
- Check IP and port in config
- Verify reader is on network: `ping 192.168.1.100`
- Test connection: `http://localhost/api/rfid/test-connection`

**Tags not recognized?**
- Verify RFID tag IDs in database
- Check logs: `writable/logs/log-*.php`
- Test with scan endpoint first

## 📊 Database Schema

New field added to `workers` table:
```sql
rfid_tag_id VARCHAR(100) NULL UNIQUE
```

## 🎯 Next Steps

1. [ ] Run migration: `php spark migrate`
2. [ ] Configure reader IP in config file
3. [ ] Connect and configure SA810 hardware
4. [ ] Assign RFID tags to workers
5. [ ] Test with web interface
6. [ ] Update worker forms to include RFID field (optional)

## 💡 Tips

- Use the web test interface for debugging
- Check logs regularly: `writable/logs/`
- Configure debounce to prevent duplicate reads
- Set up multiple readers with zone mapping
- Enable notifications for HR department

## 📞 Support

Check the logs for detailed information:
```
writable/logs/log-YYYY-MM-DD.php
```

---

**Ready to go!** Your RFID attendance system is now fully integrated and ready for use.
