#include <WiFi.h>
#include <HTTPClient.h>
#include <Keypad.h>

const char* ssid = "YOUR_WIFI_NAME";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverName = "http://192.168.1.10/SADE%20System/receive_pin.php"; // change to your XAMPP IP

const byte ROWS = 4; 
const byte COLS = 4;
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};

// Pins for ESP32-CAM (avoid camera pins)
byte rowPins[ROWS] = {13, 12, 15, 14};
byte colPins[COLS] = {2, 4, 16, 0};

Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("Connecting to WiFi...");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\nWiFi connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  static String pin = "";
  char key = keypad.getKey();

  if (key) {
    Serial.println(key);
    if (key == '#') {
      sendPIN(pin);
      pin = "";
    } else if (key == '*') {
      pin = "";
    } else {
      pin += key;
    }
  }
}

void sendPIN(String pin) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = serverName + "?pin=" + pin;
    Serial.println("Sending to: " + url);
    http.begin(url);
    int httpCode = http.GET();
    if (httpCode > 0) {
      String response = http.getString();
      Serial.println("Server response: " + response);
    } else {
      Serial.println("Error sending request");
    }
    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}
