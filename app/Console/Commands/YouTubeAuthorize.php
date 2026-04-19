<?php

namespace App\Console\Commands;

use Google_Client;
use Google_Service_YouTube;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class YouTubeAuthorize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'youtube:authorize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autoriza la aplicación con YouTube para subir videos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=================================================');
        $this->info('   Autorización de YouTube para subir Shorts');
        $this->info('=================================================');
        $this->newLine();

        try {
            $client = new Google_Client();
            $client->setClientId(env('YOUTUBE_CLIENT_ID'));
            $client->setClientSecret(env('YOUTUBE_CLIENT_SECRET'));
            $client->setRedirectUri('http://localhost:9000'); // Puerto local para recibir el código
            
            // Scopes necesarios para YouTube
            $client->addScope(Google_Service_YouTube::YOUTUBE_UPLOAD);
            $client->addScope(Google_Service_YouTube::YOUTUBE);
            $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
            
            $client->setAccessType('offline');
            $client->setPrompt('consent');

            // Generar URL de autorización
            $authUrl = $client->createAuthUrl();

            $this->warn('Iniciando servidor temporal en http://localhost:9000...');
            $this->newLine();
            
            // Obtener código mediante servidor HTTP temporal
            $authCode = $this->captureAuthCodeWithServer($authUrl);

            if (empty($authCode)) {
                $this->error('No se pudo obtener el código de autorización.');
                return 1;
            }

            $this->info('✓ Código recibido correctamente');
            $this->info('Intercambiando código por tokens...');

            // Intercambiar código por tokens
            $token = $client->fetchAccessTokenWithAuthCode(trim($authCode));

            if (isset($token['error'])) {
                $this->error('Error al obtener el token: ' . ($token['error_description'] ?? $token['error']));
                Log::error('Error obteniendo token de YouTube: ' . json_encode($token));
                return 1;
            }

            // Guardar tokens
            $tokenPath = storage_path('app/credentials/youtube_token.json');
            
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0755, true);
            }
            
            file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));

            $this->newLine();
            $this->info('✓ ¡Tokens guardados exitosamente!');
            $this->line('  Ubicación: ' . $tokenPath);

            // Verificar que funciona
            $this->newLine();
            $this->info('Verificando conexión con YouTube...');

            $client->setAccessToken($token);
            $youtube = new Google_Service_YouTube($client);

            $channelsResponse = $youtube->channels->listChannels('snippet,contentDetails,statistics', [
                'mine' => true
            ]);

            if (empty($channelsResponse->getItems())) {
                $this->warn('⚠ No se encontró ningún canal asociado a esta cuenta.');
                return 1;
            }

            $channel = $channelsResponse->getItems()[0];
            
            $this->newLine();
            $this->info('✓ ¡Conexión exitosa con YouTube!');
            $this->newLine();
            $this->line('  Canal: ' . $channel->getSnippet()->getTitle());
            $this->line('  ID: ' . $channel->getId());
            $this->line('  Suscriptores: ' . number_format($channel->getStatistics()->getSubscriberCount()));
            $this->line('  Videos: ' . $channel->getStatistics()->getVideoCount());
            $this->newLine();

            if (isset($token['refresh_token'])) {
                $this->info('✓ Refresh token obtenido (autorización permanente)');
            } else {
                $this->warn('⚠ No se obtuvo refresh token. Puede que necesites volver a autorizar.');
            }

            $this->newLine();
            $this->info('=================================================');
            $this->info('  Ya puedes subir Shorts a YouTube');
            $this->info('=================================================');

            Log::info('Autorización de YouTube completada exitosamente');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error en autorización de YouTube: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Captura el código de autorización mediante un servidor HTTP temporal
     */
    private function captureAuthCodeWithServer($authUrl)
    {
        $this->info('Abre esta URL en tu navegador:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();
        $this->comment('Esperando que autorices la aplicación en el navegador...');
        $this->comment('(Presiona Ctrl+C para cancelar)');
        $this->newLine();

        $socket = @stream_socket_server('tcp://127.0.0.1:9000', $errno, $errstr);
        
        if (!$socket) {
            $this->error("No se pudo iniciar el servidor: $errstr ($errno)");
            $this->warn('Intentando método manual...');
            return $this->captureAuthCodeManually($authUrl);
        }

        $authCode = null;
        
        // Esperar conexión (timeout de 5 minutos)
        stream_set_timeout($socket, 300);
        
        while (true) {
            $connection = @stream_socket_accept($socket, 300);
            
            if (!$connection) {
                fclose($socket);
                $this->error('Tiempo de espera agotado.');
                return null;
            }

            $request = fread($connection, 1024);
            
            // Extraer el código de la petición GET
            if (preg_match('/GET \/\?code=([^&\s]+)/', $request, $matches)) {
                $authCode = urldecode($matches[1]);
                
                // Enviar respuesta HTML de éxito
                $response = "HTTP/1.1 200 OK\r\n";
                $response .= "Content-Type: text/html; charset=UTF-8\r\n";
                $response .= "Connection: close\r\n\r\n";
                $response .= "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Autorización Exitosa</title>";
                $response .= "<style>body{font-family:Arial,sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#f0f0f0}";
                $response .= ".container{text-align:center;background:white;padding:40px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
                $response .= "h1{color:#0f9d58;margin:0 0 20px}p{color:#666;margin:10px 0}</style></head><body>";
                $response .= "<div class='container'><h1>✓ Autorización Exitosa</h1>";
                $response .= "<p>Ya puedes cerrar esta ventana y volver a la terminal.</p></div></body></html>";
                
                fwrite($connection, $response);
                fclose($connection);
                break;
            } elseif (preg_match('/GET \/\?error=/', $request)) {
                // Error en la autorización
                $response = "HTTP/1.1 200 OK\r\n";
                $response .= "Content-Type: text/html; charset=UTF-8\r\n";
                $response .= "Connection: close\r\n\r\n";
                $response .= "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title>";
                $response .= "<style>body{font-family:Arial,sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#f0f0f0}";
                $response .= ".container{text-align:center;background:white;padding:40px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}";
                $response .= "h1{color:#d93025;margin:0 0 20px}p{color:#666;margin:10px 0}</style></head><body>";
                $response .= "<div class='container'><h1>✗ Error en la Autorización</h1>";
                $response .= "<p>Vuelve a la terminal e intenta de nuevo.</p></div></body></html>";
                
                fwrite($connection, $response);
                fclose($connection);
                break;
            }
            
            fclose($connection);
        }
        
        fclose($socket);
        return $authCode;
    }

    /**
     * Método manual de respaldo si el servidor no funciona
     */
    private function captureAuthCodeManually($authUrl)
    {
        $this->warn('Abre esta URL manualmente en tu navegador:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();
        $this->info('Después de autorizar, copia el CÓDIGO de la URL de redirección');
        $this->comment('Ejemplo: http://localhost:9000/?code=4/CODIGO_AQUI&scope=...');
        $this->comment('Copia solo: 4/CODIGO_AQUI');
        $this->newLine();
        
        return $this->ask('Pega aquí el código de autorización');
    }
}
