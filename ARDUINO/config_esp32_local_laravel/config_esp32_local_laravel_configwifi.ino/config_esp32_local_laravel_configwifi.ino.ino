#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <PubSubClient.h>
#include <ESP32Servo.h>
#include <DHT.h>

Preferences preferences;
Servo chservo;
Servo saservo;
WebServer server(80);

String ssid;
String password;
bool wifiConfigured = false;

String deviceId;

// // Paramètres réseau
// IPAddress local_IP(192, 168, 1, 254);   // IP fixe
// IPAddress gateway(192, 168, 1, 1);      // passerelle (souvent l’adresse du routeur)
// IPAddress subnet(255, 255, 255, 0);     // masque de sous-réseau
// IPAddress primaryDNS(8, 8, 8, 8);       // DNS primaire (Google)
// IPAddress secondaryDNS(8, 8, 4, 4);     // DNS secondaire (Google)

// MQTT HiveMQ
const char* mqtt_server = "86c6b61405e14199853067d4b067f0a2.s1.eu.hivemq.cloud";
const char* mqtt_server2 = "2e56547e75fd40c79c79d8a2415bec89.s1.eu.hivemq.cloud";
const char* mqtt_user = "smarthome";
const int mqtt_port = 8883;
const char* mqtt_pass = "1Dev*001";
String channel = "maison/1/etat";

WiFiClientSecure espClient;
WiFiClientSecure espClient2;

PubSubClient client(espClient);
PubSubClient client2(espClient2);

//GPIO devices
#define LED_SALON 2
#define LED_CHAMBRE 4
#define VOLET_PIN 17 // Servo moteur porte d'entrée principale
#define DHTPIN 16       // Broche DATA connectée à D16
#define DHTTYPE DHT22  // Type de capteur
#define PIN_RELAIS 5 //Relais connectée à D5

DHT dht(DHTPIN, DHTTYPE);

static unsigned long lastWifiCheck = 0;
static unsigned long lastSend = 0;
/* ================================
   PAGE HTML
================================ */

String page = R"rawliteral(
  <!DOCTYPE html>
  <html>
    <head>
      <meta charset="UTF-8">
      <title>ESP32 CONFIG</title>
      <style>
        body{
          font-family:Arial;
          background:#f2f2f2;
          display:flex;
          justify-content:center;
          align-items:center;
          height:100vh;
        }
        .card{
          background:white;
          padding:30px;
          border-radius:10px;
          box-shadow:0 4px 10px rgba(0,0,0,0.2);
          width:300px;
        }
        input,select{
          width:100%;
          padding:10px;
          margin-bottom:10px;
        }
        button{
          background:#007BFF;
          color:white;
          border:none;
          padding:10px;
          width:100%;
        }
      </style>
    </head>

    <body>

    <div class="card">

    <h3>Configuration WiFi</h3>

    <form method="POST" action="/save">

    <select name="ssid">
    {{WIFI_LIST}}
    </select>

    <input type="password" name="password" placeholder="Mot de passe">

    <button type="submit">Enregistrer</button>

    </form>

    </div>

    </body>
  </html>
  )rawliteral";



/* ================================
   SCAN WIFI
================================ */

String getWifiList(){
  String options;
  options.reserve(512);

  int n = WiFi.scanNetworks();

  for(int i=0;i<n;i++){
    options += "<option value='";
    options += WiFi.SSID(i);
    options += "'>";
    options += WiFi.SSID(i);
    options += "</option>";
  }
  return options;
}



/* ================================
   PAGE PRINCIPALE
================================ */

void handleRoot(){
  String pageHtml = page;
  pageHtml.replace("{{WIFI_LIST}}",getWifiList());

  server.send(200,"text/html",pageHtml);
}



/* ================================
   SAUVEGARDE WIFI
================================ */

void saveWifi(String ssid, String password){
  preferences.begin("wifi_config",false);

  preferences.putString("ssid",ssid);

  preferences.putString("password",password);

  preferences.end();
}



/* ================================
   LECTURE WIFI
================================ */

bool loadWifi(){
  preferences.begin("wifi_config",true);//true => lecture seule

  bool exist =
  preferences.isKey("ssid") &&
  preferences.isKey("password");

  if(exist){
    ssid = preferences.getString("ssid","");
    password = preferences.getString("password","");
  }
  preferences.end();
  return exist;
}



/* ================================
   CONNEXION WIFI
================================ */

bool connectWifi(){
   // Configuration IP statique
  // if (!WiFi.config(local_IP, gateway, subnet, primaryDNS, secondaryDNS)) {
  //   Serial.println("Erreur de configuration IP");
  // }
  bool isExist = loadWifi();
  if(!isExist){
    return false;
  }
  WiFi.begin(ssid.c_str(),password.c_str());

  Serial.print("Connexion WiFi");

  // int timeout = 20;
  // while(WiFi.status()!=WL_CONNECTED && timeout>0){
  //   delay(500);
  //   Serial.print(".");
  //   timeout--;
  // }

  unsigned long start = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - start < 15000) {
      delay(100);
  }

  if(WiFi.status()==WL_CONNECTED){
    Serial.println("");
    Serial.println("Connecté !");
    Serial.println(WiFi.localIP());
    return true;
  }

  Serial.println("");
  Serial.println("Echec connexion");
  return false;
}


// FONCTIONS MQTT
float* readDHT () {
  static float  data[2];

  data[0] = dht.readHumidity();
  data[1] = dht.readTemperature();
  if(isnan(data[0]) || isnan(data[1])) {
    Serial.println("Erreur de lecture du capteur !");
    return NULL;
  }
  return data;
}

//Envoi des données 
void sendData(String topic) {
  Serial.println("Topic: " + topic);

  float* values = readDHT();
  if(values == NULL) {
    Serial.println("Données invalides, envoi annulé");
    return;
  }

  //création du message JSON
  String payload = "{";
  payload += "\"humidite\":" + String(values[0]) + ",";
  payload += "\"temperature\":" + String(values[1]);
  payload += "}";

  Serial.println("Payload: " + payload);

  client2.publish(topic.c_str(), payload.c_str());
}

//Parser Topic
void handleMessage(String topic, String message) {

  Serial.println("Topic: " + topic);
  Serial.println("Message: " + message);

  // if (topic.split("/").length < 5) return;
  // Découper topic
  int first = topic.indexOf('/');
  int second = topic.indexOf('/', first + 1);
  int third = topic.indexOf('/', second + 1);
  int fourth = topic.indexOf('/', third + 1);

  String maison = topic.substring(first + 1, second);
  String piece  = topic.substring(second + 1, third);
  String type   = topic.substring(third + 1, fourth);
  String device = topic.substring(fourth + 1);
  Serial.println("Piece: " + piece);
  // 🔥 LOGIQUE INTELLIGENTE

  // 💡 LIGHT
  if (type == "light") {

    if (piece == "salon") {
      digitalWrite(LED_SALON, message == "on" ? HIGH : LOW);
    }

    if (piece == "chambre") {
      digitalWrite(LED_CHAMBRE, message == "on" ? HIGH : LOW);
    }
  }

  // 🪟 VOLET
  else if (type == "shutter") {

    if (piece == "salon" && message == "open") {
      saservo.write(90);
    } 
    else if (piece == "salon" && message == "close") {
      saservo.write(0);
    }
    
    if (piece == "chambre" && message == "open") {
      chservo.write(90);
    }
    else if (piece == "chambre" && message == "close") {
      chservo.write(0);
    }
  }

  // 🌡️ TEMP (ex: config seuil)
  else if (type == "temperature") {
    if (piece == "salon") {
      digitalWrite(PIN_RELAIS, message == "on" ? HIGH : LOW);
    }

    if (piece == "chambre") {
      digitalWrite(PIN_RELAIS, message == "on" ? HIGH : LOW);
    }
  }
}

// Callback MQTT
void callback(char* topic, byte* payload, unsigned int length) {
  char msg[length + 1]; // buffer C
  memcpy(msg, payload, length);
  msg[length] = '\0'; // fin de chaîne

  Serial.printf("Topic: %s\n", topic);
  Serial.printf("Message: %s\n", msg);

  handleMessage(String(topic), String(msg));
}

// Reconexion
unsigned long lastReconnectAttempt = 0;
void reconnect() {
  if (millis() - lastReconnectAttempt > 5000) {
    lastReconnectAttempt = millis();
    if (client.connect("ESP32_SMARTHOME", mqtt_user, mqtt_pass)) {
      // Subscribe toute la maison
      client.subscribe("maison/1/#");
    }
  }
}

void reconnectPublish(){
  if (millis() - lastReconnectAttempt > 5000) {
    lastReconnectAttempt = millis();
    if (client2.connect("ESP32_SMARTHOME", mqtt_user, mqtt_pass)) {
      // Subscribe toute la maison
      Serial.println("Connected to publish server.");
    }
  }
}

/*FONCTIONS API*/
/*ENREGISTREMENT API */

// void registerDevice(){
//   if(WiFi.status()!=WL_CONNECTED) return;

//   HTTPClient http;

//   String url="http://localhost:8000/api/devices";

//   http.begin(url);

//   http.addHeader("Content-Type","application/json");

//   String payload="{";

//   payload += "\"device_id\":\""+deviceId+"\",";

//   payload += "\"ip\":\""+WiFi.localIP().toString()+"\"";

//   payload+="}";

//   int response=http.POST(payload);

//   Serial.println("API Response : "+String(response));

//   http.end();
// }

// /*RECUPERER LES DONNEES*/
// void fetchData() {
//   //On va récupérer les données du serveur MQTT;
//   //Les données seront dans l'url sous la forme : maison/{$maisonId}/{$pieceId}/{$typeDevice}/{$deviceId}
//   HTTPClient http;

// }





/* ================================
   ROUTE SAVE
================================ */

void handleSave(){
  if(server.hasArg("ssid") && server.hasArg("password")){

    ssid=server.arg("ssid");

    password=server.arg("password");

    saveWifi(ssid,password);

    server.send(200,"text/html","<h2>Configuration sauvegardée</h2><p>Redémarrage...</p>");

    delay(2000);

    ESP.restart();

  }

}



/* ================================
   MODE CONFIGURATION
================================ */

void startConfigPortal(){
  WiFi.softAP("ESP32_SETUP","12345678"); //Configurer un point d'accès

  Serial.println("Mode configuration");

  Serial.println(WiFi.softAPIP()); //Afficher l'adresse IP de l'ESP32 dans la commande série

  server.on("/",HTTP_GET,handleRoot);

  server.on("/save",HTTP_POST,handleSave);

  server.begin();

}

void clearSettings() {
  preferences.begin("wifi_config",false); //false => lecture + écriture
  preferences.clear();
  preferences.end();
}



/* ================================
   SETUP
================================ */

void setup(){
  //put the setup code here
  Serial.begin(115200);
  dht.begin();
  chservo.attach(18);
  saservo.attach(19);

  pinMode(LED_SALON, OUTPUT);
  pinMode(LED_CHAMBRE, OUTPUT);
  pinMode(VOLET_PIN, OUTPUT);
  pinMode(PIN_RELAIS, OUTPUT);

  deviceId = WiFi.macAddress();

  Serial.println("Device ID : "+deviceId);

  wifiConfigured = loadWifi();

  if(wifiConfigured){
    if(connectWifi()){

    //  registerDevice();
      Serial.println("CONNECTED !");
    }
    else{
      startConfigPortal();
    }
  }
  else{
    startConfigPortal();
  }

  espClient.setInsecure();
  espClient2.setInsecure();

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
  client2.setServer(mqtt_server2, mqtt_port);
  client2.setCallback(callback);
}

void loop(){
  //put the loop code here
  // if (millis() - lastWifiCheck > 10000) {
  //   lastWifiCheck = millis();
  //   if (WiFi.status() != WL_CONNECTED) {
  //     connectWifi();
  //   }
  // }

  server.handleClient();

  if (!client.connected()) reconnect();
  if (!client2.connected()) reconnectPublish();

  if(millis() - lastSend >= 5000) {
    sendData(channel);
    lastSend = milis();
  }

  client.loop();
  client2.loop();
}