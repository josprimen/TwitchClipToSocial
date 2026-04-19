<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;
use Google_Http_MediaFileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class YouTubeShortsController extends Controller
{
    /**
     * Rutas utilizadas por el controlador
     */
    public static function routes()
    {
        Route::group(['prefix' => 'youtube-shorts', 'as' => 'youtube-shorts.'], static function () {
            Route::post('upload', [self::class, 'uploadShort'])->name('upload');
            Route::get('test', [self::class, 'testUpload'])->name('test');
        });
    }

    /**
     * Crear cliente de Google configurado para YouTube
     */
    private function getClient()
    {
        $tokenPath = storage_path('app/credentials/youtube_token.json');
        
        if (!file_exists($tokenPath)) {
            throw new \Exception('No se encontró el archivo de tokens. Ejecuta: php artisan youtube:authorize');
        }

        $client = new Google_Client();
        $client->setClientId(env('YOUTUBE_CLIENT_ID'));
        $client->setClientSecret(env('YOUTUBE_CLIENT_SECRET'));
        
        $token = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($token);

        // Refrescar token si expiró
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                
                // Verificar si hubo error en el refresh
                if (isset($newToken['error'])) {
                    Log::error('Error al refrescar token de YouTube', [
                        'error' => $newToken['error'],
                        'description' => $newToken['error_description'] ?? 'Sin descripción'
                    ]);
                    throw new \Exception(
                        'Token de YouTube revocado o expirado. ' .
                        'Error: ' . $newToken['error'] . '. ' .
                        'Ejecuta: php artisan youtube:authorize'
                    );
                }
                
                // Mantener el refresh token si no viene en la respuesta
                if (!isset($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $token['refresh_token'];
                }
                
                file_put_contents($tokenPath, json_encode($newToken, JSON_PRETTY_PRINT));
                Log::info('Token de YouTube refrescado automáticamente');
            } else {
                throw new \Exception('Token expirado sin refresh token. Ejecuta: php artisan youtube:authorize');
            }
        }

        return $client;
    }

    /**
     * Subir un Short a YouTube
     * 
     * @param string $videoPath Ruta del archivo de video
     * @param string $title Título del video
     * @param string $description Descripción del video
     * @param string $privacy Privacidad: public, unlisted, private (default: public)
     * @param array $tags Tags del video
     * @return array Información del video subido
     */
    public function uploadShort($videoPath, $title, $description = '', $privacy = 'public', $tags = [])
    {
        try {
            if (!file_exists($videoPath)) {
                throw new \Exception("El archivo de video no existe: {$videoPath}");
            }

            $client = $this->getClient();
            $youtube = new Google_Service_YouTube($client);

            // Agregar #Shorts al título si no lo tiene
            if (stripos($title, '#shorts') === false && stripos($title, '#short') === false) {
                $title .= ' #Shorts';
            }

            // Configurar snippet del video
            $snippet = new Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle($title);
            $snippet->setDescription($description);
            $snippet->setTags($tags);
            $snippet->setCategoryId('24'); // 24 = Entertainment

            // Configurar estado del video
            $status = new Google_Service_YouTube_VideoStatus();
            $status->setPrivacyStatus($privacy);

            // Crear objeto de video
            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            // Configurar chunk size para subida
            $chunkSizeBytes = 5 * 1024 * 1024; // 5MB
            $client->setDefer(true);

            // Crear solicitud de inserción
            $insertRequest = $youtube->videos->insert('status,snippet', $video);

            // Crear objeto de subida de medios
            $media = new Google_Http_MediaFileUpload(
                $client,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(filesize($videoPath));

            // Subir el video en chunks
            $status = false;
            $handle = fopen($videoPath, 'rb');
            
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }
            
            fclose($handle);
            $client->setDefer(false);

            // Obtener información del video subido
            $videoId = $status['id'];
            $videoUrl = "https://www.youtube.com/shorts/{$videoId}";

            Log::info("Short subido a YouTube exitosamente", [
                'video_id' => $videoId,
                'title' => $title,
                'url' => $videoUrl
            ]);

            return [
                'success' => true,
                'video_id' => $videoId,
                'url' => $videoUrl,
                'title' => $title,
                'privacy' => $privacy
            ];

        } catch (\Google_Service_Exception $e) {
            $error = json_decode($e->getMessage(), true);
            $errorMessage = $error['error']['message'] ?? $e->getMessage();
            
            Log::error('Error de YouTube API al subir Short', [
                'error' => $errorMessage,
                'code' => $e->getCode()
            ]);

            throw new \Exception("Error de YouTube API: {$errorMessage}");

        } catch (\Exception $e) {
            Log::error('Error al subir Short a YouTube: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test de subida con un video de ejemplo
     */
    public function testUpload()
    {
        try {
            // Buscar videos en storage/app/videos
            $videosPath = storage_path('app/videos');
            
            if (!is_dir($videosPath)) {
                return response()->json([
                    'error' => 'No existe el directorio: ' . $videosPath
                ], 400);
            }

            // Buscar archivos .mp4
            $videos = glob($videosPath . '/output_*.mp4');
            
            if (empty($videos)) {
                return response()->json([
                    'error' => 'No se encontraron videos en: ' . $videosPath,
                    'message' => 'Ejecuta primero "php artisan crear_media_twitch" para generar videos'
                ], 400);
            }

            // Tomar el video más reciente
            $testVideoPath = end($videos);
            $videoName = basename($testVideoPath);
            
            Log::info('Test YouTube: Intentando subir video', [
                'path' => $testVideoPath,
                'size' => filesize($testVideoPath) . ' bytes'
            ]);

            // Subir el video de prueba
            $result = $this->uploadShort(
                $testVideoPath,
                'Test Short desde Laravel ' . date('H:i:s'),
                'Este es un video de prueba subido automáticamente desde mi aplicación Laravel. #Shorts #Test',
                'public', // público como los videos reales
                ['test', 'laravel', 'shorts', 'automatico']
            );

            return response()->json([
                'success' => true,
                'message' => '¡Short subido exitosamente!',
                'video_file' => $videoName,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Test YouTube falló: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
