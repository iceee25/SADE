
#include <WiFi.h>
#include <HTTPClient.h>

// ═══ EDIT ONLY THESE 3 LINES ══════════════════
const char* WIFI_SSID  = "toto";
const char* WIFI_PASS  = "James@12192008";
const char* SERVER_IP  = "192.168.68.159";
// ══════════════════════════════════════════════

String PING_URL;

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\n=============================");
  Serial.println("  SADE ESP32 Connection Test  ");
  Serial.println("=============================\n");

  PING_URL = String("http://") + SERVER_IP
           + "/SADE%20System/public/ping.php";

  Serial.println("[1] Connecting to WiFi: " + String(WIFI_SSID));
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[OK] WiFi Connected!");
    Serial.println("     ESP32 IP : " + WiFi.localIP().toString());
    Serial.println("     Signal   : " + String(WiFi.RSSI()) + " dBm");
  } else {
    Serial.println("\n[FAIL] WiFi FAILED.");
    Serial.println("  - Check WIFI_SSID spelling (case sensitive)");
    Serial.println("  - Check WIFI_PASS is correct");
    Serial.println("  - Move ESP32 closer to router");
    while (true) delay(1000);
  }

  Serial.println("\n[2] Pinging: " + PING_URL);
  pingServer();
}

void loop() {
  delay(5000);
  Serial.println("\n[PING] Testing again...");
  pingServer();
}

void pingServer() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[FAIL] No WiFi.");
    return;
  }

  HTTPClient http;
  http.begin(PING_URL);
  http.setTimeout(5000);
  int code = http.GET();

  if (code == 200) {
    Serial.println("[OK] Server reached! HTTP 200");
    Serial.println("     " + http.getString());
  } else if (code == -1) {
    Serial.println("[FAIL] Cannot reach server.");
    Serial.println("  - Is XAMPP Apache running?");
    Serial.println("  - Did you run the firewall command?");
    Serial.println("  - SERVER_IP used: " + String(SERVER_IP));
  } else {
    Serial.println("[FAIL] HTTP error: " + String(code));
    Serial.println("  - Check ping.php exists in /public/");
  }

  http.end();
}