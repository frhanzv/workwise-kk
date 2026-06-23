# RFID API Quick Reference

## Base URL
```
http://your-server/api/rfid/
```

---

## Endpoints

### 1. Tag Read Webhook (Primary Endpoint)
**URL**: `POST /api/rfid/tag-read`

**Purpose**: Main endpoint for SA810 reader to send tag data

**Request Body** (JSON):
```json
{
  "tag_id": "E2003412EF1234567890ABCD",
  "reader_id": "SA810_001",
  "timestamp": "2025-12-23 10:30:45",
  "antenna": 1
}
```

**Success Response** (Check-in):
```json
{
  "success": true,
  "message": "Check-in recorded successfully",
  "action": "checkin",
  "worker": {
    "id": "W001",
    "name": "John Doe",
    "photo": "uploads/profiles/w001.jpg"
  },
  "zone": {
    "id": 1,
    "name": "Main Entrance"
  },
  "time": "10:30:45"
}
```

**Success Response** (Check-out):
```json
{
  "success": true,
  "message": "Check-out recorded successfully",
  "action": "checkout",
  "worker": {
    "id": "W001",
    "name": "John Doe",
    "photo": "uploads/profiles/w001.jpg"
  },
  "zone": {
    "id": 1,
    "name": "Main Entrance"
  },
  "time": "18:30:45",
  "check_in_time": "10:30:45",
  "duration": "8h 0m"
}
```

**Error Response** (Tag not registered):
```json
{
  "success": false,
  "message": "RFID tag not registered in system",
  "tag_id": "E2003412EF1234567890ABCD",
  "action": "none"
}
```

---

### 2. Test Scan
**URL**: `GET /api/rfid/scan`

**Purpose**: Test endpoint without physical reader

**Parameters**:
- `tag_id` (required): RFID tag ID to test

**Example**:
```bash
curl "http://localhost/api/rfid/scan?tag_id=E2003412EF1234567890ABCD"
```

**Response**: Same as tag-read endpoint

---

### 3. Reader Status
**URL**: `GET /api/rfid/status`

**Purpose**: Check reader connection status

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

---

### 4. Test Connection
**URL**: `GET /api/rfid/test-connection`

**Purpose**: Test if server can connect to reader

**Success Response**:
```json
{
  "success": true,
  "message": "Successfully connected to RFID reader"
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Failed to connect to RFID reader",
  "status": 500
}
```

---

### 5. Manual Attendance Entry
**URL**: `POST /api/rfid/manual`

**Purpose**: Manually record attendance (backup/testing)

**Request Body** (JSON):
```json
{
  "worker_id": "W001",
  "zone_id": 1,
  "action": "in"
}
```

**Parameters**:
- `worker_id` (required): Worker ID
- `zone_id` (required): Zone ID
- `action` (required): "in" or "out"

**Success Response**:
```json
{
  "success": true,
  "message": "Check-in recorded successfully",
  "action": "checkin"
}
```

---

## Testing Examples

### Using cURL

**Test tag scan**:
```bash
curl "http://localhost/api/rfid/scan?tag_id=ABC123"
```

**Test reader connection**:
```bash
curl "http://localhost/api/rfid/test-connection"
```

**Manual check-in**:
```bash
curl -X POST http://localhost/api/rfid/manual \
  -H "Content-Type: application/json" \
  -d '{"worker_id":"W001","zone_id":1,"action":"in"}'
```

**Manual check-out**:
```bash
curl -X POST http://localhost/api/rfid/manual \
  -H "Content-Type: application/json" \
  -d '{"worker_id":"W001","zone_id":1,"action":"out"}'
```

---

## SA810 Configuration

**Configure the SA810 to send data to**:
```
URL: http://your-server-ip/api/rfid/tag-read
Method: POST
Content-Type: application/json
```

**Data format template**:
```json
{"tag_id":"{{EPC}}","reader_id":"SA810_001","timestamp":"{{TIMESTAMP}}","antenna":{{ANTENNA}}}
```

---

## Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request (invalid data) |
| 404 | Not Found (worker/zone not found) |
| 500 | Server Error (connection/processing error) |

---

## Notes

- All endpoints return JSON responses
- Tag IDs are case-insensitive in storage but case-sensitive in matching
- Timestamps are in format: `YYYY-MM-DD HH:MM:SS`
- Reader ID must match configuration or zone mapping
- First tap = check-in, second tap = check-out, third tap = new check-in
