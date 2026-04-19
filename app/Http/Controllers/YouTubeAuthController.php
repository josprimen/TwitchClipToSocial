<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_YouTube;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class YouTubeAuthController extends Controller
{
    /**
     * Rutas utilizadas por el controlador
     */
    public static function routes()
    {
        Route::group(['prefix' => 'youtube', 'as' => 'youtube.'], static function () {
            Route::get('auth', [self::class, 'redirectToGoogle'])->name('auth');
            Route::get('callback', [self::class, 'handleGoogleCallback'])->name('callback');
            Route::get('test-upload', [self::class, 'testUpload'])->name('test-upload');
        });
    }

    /**
     * Crear cliente de Google configurado para YouTube
     */
    private function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('YOUTUBE_CLIENT_ID'));
        $client->setClientSecret(env('YOUTUBE_CLIENT_SECRET'));
        $client->setRedirectUri(url('/youtube/callback'));
        
        // Scopes necesarios para YouTube
        $client->addScope(Google_Service_YouTube::YOUTUBE_UPLOAD);
        $client->addScope(Google_Service_YouTube::YOUTUBE);
        $client->addScope(Google_Service_YouTube::YOUTUBE_FORCE_SSL);
        
        $client->setAccessType('offline');
        $client->setPrompt('consent'); // Fuerza a pedir consent para obtener refresh token
        
        return $client;
    }

    /**
     * Redirigir al usuario a Google para autorización
     */
    public function redirectToGoogle()
    {
        $client = $this->getClient();
        $authUrl = $client->createAuthUrl();
        
        return redirect($authUrl);
    }

    /**
     * Manejar el callback de Google y guardar los tokens
     */
    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        
        if (!$request->has('code')) {
            return response()->json(['error' => 'No authorization code received'], 400);
        }

        try {
            // Intercambiar código por tokens
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
            
            if (isset($token['error'])) {
                Log::error('Error obteniendo token de YouTube: ' . json_encode($token));
                return response()->json(['error' => $token['error_description'] ?? 'Error desconocido'], 400);
            }

            // Guardar tokens en un archivo JSON
            $tokenPath = storage_path('app/credentials/youtube_token.json');
            
            // Crear directorio si no existe
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0755, true);
            }
            
            file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT));
            
            Log::info('Tokens de YouTube guardados exitosamente');
            Log::info('Access Token: ' . substr($token['access_token'], 0, 20) . '...');
            if (isset($token['refresh_token'])) {
                Log::info('Refresh Token: ' . substr($token['refresh_token'], 0, 20) . '...');
            }

            return response()->json([
                'success' => true,
                'message' => '¡Autorización exitosa! Los tokens se han guardado.',
                'token_path' => $tokenPath,
                'has_refresh_token' => isset($token['refresh_token'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback de YouTube: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test de subida a YouTube
     */
    public function testUpload()
    {
        try {
            $tokenPath = storage_path('app/credentials/youtube_token.json');
            
            if (!file_exists($tokenPath)) {
                return response()->json([
                    'error' => 'No se encontró el archivo de tokens. Primero autoriza en /youtube/auth'
                ], 400);
            }

            $client = $this->getClient();
            $token = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($token);

            // Refrescar token si expiró
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($newToken, JSON_PRETTY_PRINT));
                    Log::info('Token de YouTube refrescado');
                } else {
                    return response()->json([
                        'error' => 'Token expirado y no hay refresh token. Vuelve a autorizar en /youtube/auth'
                    ], 401);
                }
            }

            $youtube = new Google_Service_YouTube($client);
            
            // Obtener información del canal para verificar que funciona
            $channelsResponse = $youtube->channels->listChannels('snippet,contentDetails,statistics', [
                'mine' => true
            ]);

            if (empty($channelsResponse->getItems())) {
                return response()->json([
                    'error' => 'No se encontró ningún canal asociado a esta cuenta'
                ], 404);
            }

            $channel = $channelsResponse->getItems()[0];
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión con YouTube exitosa',
                'channel' => [
                    'id' => $channel->getId(),
                    'title' => $channel->getSnippet()->getTitle(),
                    'subscribers' => $channel->getStatistics()->getSubscriberCount(),
                    'videos' => $channel->getStatistics()->getVideoCount()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en test de YouTube: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
