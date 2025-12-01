# ESP32 SADE System Integration Guide

## Hardware Requirements
- ESP32 DevBoard (WROOM-32)
- Barcode Scanner (Serial UART 9600 baud)
- 4x4 Matrix Keypad
- 2x LED (Red & Green)
- Solenoid Door Lock
- Relay Module for solenoid control
- Power Supply (5V/3.3V)

## Pin Configuration

### Barcode Scanner (Serial UART2)
- RX: GPIO 16
- TX: GPIO 17
- Baud Rate: 9600

### Keypad (4x4 Matrix)
**Rows:**
- Row 1: GPIO 32
- Row 2: GPIO 33
- Row 3: GPIO 25
- Row 4: GPIO 26

**Columns:**
- Col 1: GPIO 27
- Col 2: GPIO 14
- Col 3: GPIO 12

### Output Pins
- Status LED (Green): GPIO 2
- Error LED (Red): GPIO 4
- Door Lock Solenoid (via Relay): GPIO 5
- Success LED (Optional): GPIO 15

## Installation Steps

### 1. PlatformIO Setup (Recommended)
\`\`\`bash
# Install PlatformIO CLI
pip install -U platformio

# Or use VSCode extension: PlatformIO IDE
# Create project with provided platformio.ini
\`\`\`

### 2. Configure WiFi & Server
Edit `src/main.cpp`:
\`\`\`cpp
const char* SSID = "YOUR_WIFI_SSID";
const char* PASSWORD = "YOUR_WIFI_PASSWORD";
const char* SERVER_URL = "http://192.168.x.x/xampp";  // Your XAMPP server IP
const char* DEVICE_ID = "SADE_BARCODE_SCANNER_01";
const char* LAB_ID = "1811";
\`\`\`

### 3. Build and Upload
\`\`\`bash
# Build
pio run

# Upload to ESP32
pio run --target upload

# Monitor serial output
pio device monitor --baud 115200
\`\`\`

## XAMPP API Setup

### 1. Create API Directory
\`\`\`bash
mkdir C:\xampp\htdocs\sade-api
\`\`\`

### 2. Copy PHP Files
Place these files in `C:\xampp\htdocs\sade-api\`:
- `barcode-scan.php` - Process barcode scans
- `authenticate-technician.php` - Validate technician PINs
- `change-keypad-password.php` - Update keypad passwords
- `esp32-config.php` - Centralized configuration
- `esp32-device-status.php` - Device health checks

### 3. Database Setup
\`\`\`bash
# Open phpMyAdmin at http://localhost/phpmyadmin
# Import sade_system.sql database
# Verify tables exist: users, access_logs, activity_logs, devices
\`\`\`

### 4. Register Device
\`\`\`sql
-- Add ESP32 device to database
INSERT INTO devices (device_id, lab_id, room, is_online, door_locked, created_at)
VALUES ('SADE_BARCODE_SCANNER_01', '1811', 'Lab 1811', 0, 1, NOW());
\`\`\`

## Updated API Testing Section

### Test Connectivity
Verify in ESP32 serial monitor:
\`\`\`
[v0] Connecting to WiFi: YOUR_SSID
[v0] WiFi connected!
[v0] IP Address: 192.168.x.x
[v0] Setup complete. Ready for barcode scanning and keypad input
\`\`\`

### Test Barcode Scanning
1. Scan valid student barcode (e.g., 2022178651)
2. Check serial: `[v0] Barcode scanned: 2022178651`
3. Expected response: Success LED flashes, door unlocks for 3 seconds
4. Verify in database: `SELECT * FROM access_logs ORDER BY id DESC;`

### Test Technician Authentication
1. Enter PIN: 9999 (default)
2. Press # to submit
3. Check serial: `[v0] Authenticating technician with PIN: 9999`
4. Expected: Success LED flashes 3 times, door unlocks for 5 seconds
5. Verify in database access logs

### Test PIN Change
1. After technician auth succeeds, enter new PIN (4-8 digits)
2. Press # to confirm
3. Check response: `[v0] PIN changed successfully`
4. New PIN stored in users table

### Monitor All Logs
- **Access Logs**: `SELECT * FROM access_logs WHERE device_id='SADE_BARCODE_SCANNER_01' ORDER BY id DESC;`
- **Activity Logs**: `SELECT * FROM activity_logs WHERE activity_type IN ('BARCODE_SCAN', 'TECHNICIAN_LOGIN', 'TECHNICIAN_PIN_CHANGE') ORDER BY created_at DESC;`
- **System Logs**: `SELECT * FROM system_logs WHERE event_type LIKE '%ESP32%' ORDER BY timestamp DESC;`

## Troubleshooting

### WiFi Connection Issues
- Verify SSID & password exactly match your router
- Check ESP32 is within WiFi range
- Ensure router has 2.4GHz band enabled (ESP32 doesn't support 5GHz)
- Check firewall - may need to allow port 80 for HTTP
- Try power-cycling both ESP32 and router

### Barcode Scanner Not Reading
- Verify barcode scanner is configured for 9600 baud
- Check GPIO 16 (RX) and GPIO 17 (TX) connections
- Test with serial monitor - send test barcode data
- Verify barcode format matches database id_number field
- Check barcode is in participants table

### Server Connection Failed
- Verify SERVER_URL is correct in code (use IP, not localhost)
- Ensure XAMPP Apache server is running
- Test URL in browser: `http://192.168.x.x/xampp/barcode-scan.php`
- Check Windows firewall - allow Apache through
- Verify MySQL is running (check XAMPP Control Panel)
- Check database credentials in PHP files match your setup

### Database Errors
- Verify MySQL is running in XAMPP Control Panel
- Check database name: `sade_system` (case-sensitive on Linux)
- Verify user has read/write permissions
- Check all required tables exist
- View MySQL error logs in XAMPP

### Door Lock Not Engaging
- Verify GPIO 5 relay connection
- Check solenoid is receiving power
- Test relay with direct GPIO HIGH/LOW command
- Verify solenoid polarity
- Check transistor/relay circuit for shorts

### LEDs Not Lighting
- Verify GPIO pin connections to LED cathode/anode
- Check LED polarity (longer leg = positive)
- Test with analogWrite for brightness control
- Verify resistor values (220-330Ω recommended)

## API Endpoints

### 1. Barcode Scan
\`\`\`
POST /sade-api/barcode-scan.php
Content-Type: application/json

Request:
{
  "device_id": "SADE_BARCODE_SCANNER_01",
  "lab_id": "1811",
  "barcode": "2022178651",
  "action": "SCAN"
}

Response (Success):
{
  "success": true,
  "message": "Access granted for Jericho Wayne G. Co Leng",
  "access_granted": true,
  "user_id": 1,
  "user_name": "Jericho Wayne G. Co Leng"
}

Response (Failed):
{
  "success": false,
  "message": "Student not found or not enrolled",
  "access_granted": false
}
\`\`\`

### 2. Authenticate Technician
\`\`\`
POST /sade-api/authenticate-technician.php
Content-Type: application/json

Request:
{
  "device_id": "SADE_BARCODE_SCANNER_01",
  "lab_id": "1811",
  "pin": "9999"
}

Response (Success):
{
  "authenticated": true,
  "message": "Technician authenticated: Co Leng",
  "technician_id": "T8363",
  "technician_name": "Co Leng",
  "access_level": "TECHNICIAN"
}

Response (Failed):
{
  "authenticated": false,
  "message": "Invalid technician PIN"
}
\`\`\`

### 3. Change Keypad Password
\`\`\`
POST /sade-api/change-keypad-password.php
Content-Type: application/json

Request:
{
  "device_id": "SADE_BARCODE_SCANNER_01",
  "lab_id": "1811",
  "new_pin": "1234",
  "technician_id": "T8363"
}

Response (Success):
{
  "success": true,
  "message": "Keypad PIN updated successfully",
  "updated_count": 1,
  "new_pin": "1234",
  "timestamp": "2025-11-25 10:30:45"
}

Response (Failed):
{
  "success": false,
  "message": "PIN must be 4-8 digits"
}
\`\`\`

### 4. Device Status Check
\`\`\`
POST /sade-api/esp32-device-status.php
Content-Type: application/json

Request:
{
  "device_id": "SADE_BARCODE_SCANNER_01"
}

Response:
{
  "success": true,
  "device_status": "online",
  "device_id": "SADE_BARCODE_SCANNER_01",
  "server_time": "2025-11-25 10:30:45",
  "door_locked": 1
}
\`\`\`

## Security Best Practices

1. **Change Default PINs**: Update technician PIN from 9999 to a secure value
2. **Use HTTPS**: Deploy with SSL certificate in production
3. **Validate Input**: All inputs validated on both ESP32 and server
4. **Log Everything**: All access attempts logged for audit trails
5. **Firewall Rules**: Restrict API access to trusted networks only
6. **Database Backups**: Set up regular automated backups
7. **Monitor Logs**: Review access logs regularly for suspicious activity
8. **Restrict Permissions**: Use dedicated database user with minimal privileges

## Serial Monitor Debug Messages

Expected output for successful operations:
\`\`\`
[v0] Barcode scanned: 2022178651
[v0] Sending barcode data: {"device_id":"SADE_BARCODE_SCANNER_01",...}
[v0] Server response: {"success":true,"message":"Access granted..."
[v0] Barcode accepted - Student entry logged

[v0] Key pressed: 9
[v0] Input buffer: 1 digits
[v0] Authenticating technician with PIN: 9999
[v0] Auth response: {"authenticated":true,"message":"Technician authenticated..."
[v0] Technician authenticated!
[v0] Door unlocking for 5000ms
[v0] Door locked
\`\`\`

## Production Deployment

For live server deployment:
1. Move PHP files to production server
2. Update SERVER_URL to production domain/IP
3. Implement HTTPS with SSL certificate
4. Add API key authentication
5. Enable database encryption
6. Set up log rotation and archival
7. Configure automated backups
8. Monitor system 24/7
9. Set up email alerts for failed access attempts

## Support Resources

- **ESP32 Docs**: https://docs.espressif.com/projects/esp-idf/en/latest/esp32/
- **PlatformIO Docs**: https://docs.platformio.org/
- **ArduinoJson**: https://arduinojson.org/
- **XAMPP**: https://www.apachefriends.org/

For issues or questions:
1. Check serial monitor for debug output
2. Review error logs in XAMPP/phpMyAdmin
3. Test individual components separately
4. Verify database connectivity with test queries
5. Check device registration in esp32-config.php
