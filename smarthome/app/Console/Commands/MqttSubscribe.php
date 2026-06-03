<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttSubscribe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("Démarrage du client MQTT pour s'abonner au topic 'maison/1/etat'...");
        $server = env("MQTT_SERVER_SUBSCRIBE", "localhost");
        $port = env("MQTT_PORT", 8883);
        $clientId = env("MQTT_CLIENT_ID", "laravel");
        $username = env('MQTT_USERNAME');
        $password = env('MQTT_PASSWORD');

        $connectionSettings = (new ConnectionSettings())
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true);
        $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings, true);

        $mqtt->subscribe('maison/1/etat', function ($topic, $message) {
            Log::info("Message reçu : " . $message);
            //Découpage JSON
            $data = json_decode($message, true);

            if (isset($data['temperature'])) {
                $temperature = floatval($data['temperature']);
                Log::info("Température reçue : " . $temperature);

                $lastTemperature = \App\Models\Donnee::where('type', 'temperature')->latest()->first();

                if (!($lastTemperature) || (abs($temperature - floatval($lastTemperature->valeur)) > 2)) {
                    $ancienneValeur = $lastTemperature ? $lastTemperature->valeur : 'aucune';
                    Log::info("Variation détectée : {$temperature}°C (dernière valeur : {$ancienneValeur})");
                    \App\Models\Donnee::create([
                        'type' => 'temperature',
                        'valeur' => $temperature,
                        'device_id' => 1, // ID du device associé à la température
                    ]);
                    Log::info("Température enregistrée : {$temperature}");
                }
            }
                        
            if(isset($data['humidite'])) {
                $humidite = floatval($data['humidite']);
                Log::info("Humidité reçue : " . $humidite);

                $lasthumidite = \App\Models\Donnee::where('type', 'humidite')->latest()->first();

                if (!$lasthumidite || abs($humidite - floatval($lasthumidite->valeur)) > 2) {
                    $ancienneValeur = $lasthumidite ? $lasthumidite->valeur : 'aucune';
                    Log::info("Variation d'humidité importante détectée : " . $humidite . " (dernière valeur : " . $ancienneValeur . ")" . PHP_EOL);
                    \App\Models\Donnee::create([
                        'type' => 'humidite',
                        'valeur' => $humidite,
                        'device_id' => 1, // ID du device associé à l'humidité
                    ]);
                    Log::info("Humidité enregistrée : {$humidite}");
                }
            }
        }, 0);

        $mqtt->loop(true);
    }
}
