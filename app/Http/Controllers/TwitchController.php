<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UrlCanal;
use App\Models\Video;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;

class TwitchController extends Controller
{

    protected $tratamientoVideoController;

    public function __construct(TratamientoVideoController $tratamientoVideoController)
    {
        $this->tratamientoVideoController = $tratamientoVideoController;
    }

    public static function routes()
    {

        Route::group([ 'prefix' => 'twitch', 'as' => 'twitch.' ], function () {

                Route::get('main', [ TwitchController::class, 'prueba' ])->name('prueba');
                Route::get('prueba', [ TwitchController::class, 'prueba' ])->name('prueba');
                Route::get('clips-canal-txt', [ TwitchController::class, 'obtenerYAlmacenarClipsTwitchPruebaTxt' ])->name('clips-canal-txt');
                Route::get('crear-media', [ TwitchController::class, 'crearMedia' ])->name('crear-media');
                Route::get('publicar-media', [ TwitchController::class, 'publicarMedia' ])->name('publicar-media');
//            Route::post('datatable', [ TwitchController::class, 'datatable' ])->name('datatable');

        });
    }

    public function crearMedia($id_video) {
        try {
            $video = Video::findOrFail($id_video);
            $flag_con_descarga = false;

            $curl = curl_init();

            $instagram_id = env('INSTAGRAM_ID');
            $access_token = env('API_GRAPH_ACCESS_TOKEN');
            try {
                $url_video = $this->tratamientoVideoController->transformarVideo916($video->url);
                $flag_con_descarga = true;
                Log::info('Usando formato vertical');

            }catch (\Exception $e){
                $url_video = $video->url;
                Log::info('Ha habido un fallo en el formato vertical' . $e);

            }
            $hastags = ' #' . $video->clip->canal->nombre_canal . ' #TwitchClips #HighlightReel #ClipOfTheDay #TwitchHighlight #TwitchCommunity #ContentCreators';
            $caption = $video->clip->titulo_clip . $hastags ?? 'JAJAJAJAJA';

            // Utiliza http_build_query para construir la cadena de consulta de manera segura
            $query_params = http_build_query([
                'video_url' => $url_video,
                'caption' => $caption,
                'media_type' => 'REELS',
                'access_token' => $access_token,
            ]);

            $url = 'https://graph.facebook.com/v18.0/' . $instagram_id . '/media?' . $query_params;

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: sb=bDtzZbBNqvRVK_x44b2Odt4-'
                ),
            ));

            $response = curl_exec($curl);

            // Agrega esta línea para verificar el código de respuesta HTTP
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            echo 'HTTP Code: ' . $httpCode;
            echo 'response: ' . $response;
            curl_close($curl);

            $video->id_contenedor_publicacion = json_decode($response)->id;
            $video->save();

//            if ($flag_con_descarga) unlink($url_video);

        } catch (\Exception $e) {
            dd($e);
        }
    }


    public function publicarMedia($id_video) {
        try {
            $video = Video::findOrFail($id_video);

            $curl = curl_init();

            $instagram_id = env('INSTAGRAM_ID');
            $access_token = env('API_GRAPH_ACCESS_TOKEN');
            $creation_id = $video->id_contenedor_publicacion;

            // Utiliza http_build_query para construir la cadena de consulta con parámetros
            $query_params = http_build_query([
                'creation_id' => $creation_id,
                'access_token' => $access_token,
            ]);

            $url = 'https://graph.facebook.com/v18.0/' . $instagram_id . '/media_publish?' . $query_params;

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'Cookie: sb=bDtzZbBNqvRVK_x44b2Odt4-'
                ),
            ));

            $response = curl_exec($curl);

            // Agrega esta línea para verificar el código de respuesta HTTP
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            echo 'HTTP Code: ' . $httpCode;

            $id_publicacion = json_decode($response)->id ?? null;

            curl_close($curl);
            $video->subido = true;
            $video->id_publicacion = $id_publicacion;
            $video->save();

            if(is_null($id_publicacion)) {
                Log::info('ID PUBLICACION VACÍO:');
                Log::info($response);
                Artisan::call('publicar_media_twitch');
            }


        } catch (\Exception $e) {
            Log::info($e);
            dd($e);
        }
    }



    /**
     * Método de prueba que recopila y descarga los clips de twitch de un canal de twitch que indiquemos.
     * La url tiene que tener el formato de ejemplo.
     */
    public function obtenerYAlmacenarClipsTwitchPruebaTxt(Request $request): RedirectResponse
    {
        try {

            // URL de la página web que deseas capturar
            $url = 'https://www.twitch.tv/rubius/clips?featured=false&filter=clips&range=30d';

            //Limpiamos el archivo
            $this->guardarTextoEnArchivo('', 'twitch_web.txt', true);


            $body = Browsershot::url($url)
                ->setNodeBinary(env('NODE_BINARY_PATH', '/home/vagrant/.nvm/versions/node/v21.2.0/bin/node')) // Ruta específica de tu versión de Node.js
                ->setNpmBinary(env('NPM_BINARY_PATH', '/home/vagrant/.nvm/versions/node/v21.2.0/bin/npm'))   // Ruta específica de tu versión de npm
                ->setChromePath(env('CHROMIUM_PATH', '/usr/bin/chromium-browser')) // Ruta específica de tu instalación global de Chromium
                ->setOption('waitUntil', 'networkidle0')
                ->timeout(60000)
                ->bodyHtml(); // returns the html of the body

            $this->guardarTextoEnArchivo($body);

//            dd($body);

            // $crawler = new Crawler($html);
            $text = Storage::get('twitch_web.txt');

            $crawler = new Crawler($text);

            // Selecciona todos los elementos que coinciden con el patrón
            $elementos = $crawler->filter('a[data-a-target="preview-card-image-link"]');

            // Inicializa un array para almacenar las URLs
            $urls = [];

            //Limpiamos el archivo.
            $this->guardarTextoEnArchivo('', 'urls_clips.txt', true);

            // Itera sobre cada elemento y extrae la URL del atributo href
            $elementos->each(function (Crawler $elemento) use (&$urls) {
                $url = $elemento->attr('href');

                // Concatena el string "https://www.twitch.com" a la URL
                $urlCompleta = "https://www.twitch.com" . $url;

                // Muestra cada URL completa en la consola (puedes omitir esta línea si no es necesario)
                //echo "URL completa: $urlCompleta\n";

                // Agrega la URL completa al array
                $urls[] = $urlCompleta;

                //Añade la URL al archivo de URLs
                $url_con_coma = $urlCompleta . ",\n";
                $this->guardarTextoEnArchivo($url_con_coma, 'urls_clips.txt', false);


            });


            //Limpiamos el archivo.
            $this->guardarTextoEnArchivo('', 'urls_videos.txt', true);


            foreach ($urls as $url_video){
                $body = Browsershot::url($url_video)
                    ->setNodeBinary(env('NODE_BINARY_PATH', '/home/vagrant/.nvm/versions/node/v21.2.0/bin/node')) // Ruta específica de tu versión de Node.js
                    ->setNpmBinary(env('NPM_BINARY_PATH', '/home/vagrant/.nvm/versions/node/v21.2.0/bin/npm'))   // Ruta específica de tu versión de npm
                    ->setChromePath(env('CHROMIUM_PATH', '/usr/bin/chromium-browser')) // Ruta específica de tu instalación global de Chromium
                    ->setOption('waitUntil', 'networkidle0')
                    ->timeout(60000)
                    ->bodyHtml(); // returns the html of the body

                $crawler = new Crawler($body);

                // Selecciona todos los elementos que coinciden con el patrón de etiqueta video
                $elementos = $crawler->filter('video');

                // Inicializa un array para almacenar las URLs
                $urls = [];

                // Itera sobre cada elemento y extrae la URL del atributo src
                $elementos->each(function (Crawler $elemento) use (&$urls) {
                    $url = $elemento->attr('src');

                    // Agrega la URL completa al array
                    $urls[] = $url;
                    $this->descargarVideo($url);

                    // Añade la URL al archivo de URLs
                    $url_con_coma = $url . ",\n";
                    $this->guardarTextoEnArchivo($url_con_coma, 'urls_videos.txt', false);
                });
            }

            dd('Proceso terminado! Tienes los clips en la carpeta storage/app/videos y los txt con los enlaces en storage/app.');

        }catch (\Exception $e){
            dd($e);
        }
    }


    function guardarTextoEnArchivo($texto, $nombreArchivo = 'twitch_web.txt' , $sobrescribir = true) {

        if (!Storage::exists($nombreArchivo)) {
            // Si el archivo no existe, lo crea con el nuevo texto
            Storage::put($nombreArchivo, $texto);
        } else {
            if ($sobrescribir) {
                // Sobrescribe el archivo si se especifica
                Storage::put($nombreArchivo, $texto);
            } else {
                // Obtiene el contenido actual del archivo y agrega el nuevo texto
                $contenidoActual = Storage::get($nombreArchivo);
                $nuevoContenido = $contenidoActual . $texto;

                // Almacena el nuevo contenido en el archivo
                Storage::put($nombreArchivo, $nuevoContenido);
            }
        }
    }


    function descargarVideo($url) {

        try {

            // Carpeta donde se guardarán los videos
            $carpetaVideos = storage_path('app/videos');

            // Verifica si la carpeta existe, si no, la crea
            if (!file_exists($carpetaVideos)) {
                mkdir($carpetaVideos, 0755, true);
            }

            // Nombre del archivo de salida (puedes personalizarlo según tus necesidades)
            $nombreArchivo = 'video_descargado' . Carbon::now()->timestamp . '.mp4';

            // Ruta completa del archivo de salida
            $rutaCompleta = $carpetaVideos . '/' . $nombreArchivo;

            // Comando wget para descargar el video
            $comandoWget = "wget -O \"$rutaCompleta\" \"$url\"";

            // Ejecuta el comando wget
            shell_exec($comandoWget);

//        // Verifica si el archivo se descargó exitosamente
//        if (file_exists($rutaCompleta)) {
//            echo "El video se descargó exitosamente en $rutaCompleta";
//        } else {
//            echo "Hubo un problema al descargar el video";
//        }

        }catch(\Exception $e){
            dd($e);
        }
    }

    /**
     * Metodo principal
     */
    public function main(Request $request): RedirectResponse
    {
        try {

            // URL de la página web que deseas capturar
            $url = 'https://www.twitch.tv/illojuan/clips?featured=false&filter=clips&range=all';
            $this->obtenerUrlClips($url);

        }catch (\Exception $e){
            dd($e);
        }
    }


    function obtenerUrlClips($url){

        if (!env('ENTORNO') == 'windows'){
            $body = Browsershot::url($url)
                ->setOption('waitUntil', 'networkidle0')
                ->timeout(60000)
                ->bodyHtml(); // returns the html of the body

            $this->guardarTextoEnArchivo($body);
        }

        //Limpiamos el archivo
        $this->guardarTextoEnArchivo('', 'urls_clips.txt', true);


        // $crawler = new Crawler($html);
        $text = Storage::get('twitch_web.txt');

        $crawler = new Crawler($text);

        // Selecciona todos los elementos que coinciden con el patrón
        $elementos = $crawler->filter('a[data-a-target="preview-card-image-link"]');

        // Inicializa un array para almacenar las URLs
        $urls = [];

        // Itera sobre cada elemento y extrae la URL del atributo href
        $elementos->each(function (Crawler $elemento) use (&$urls) {
            $url = $elemento->attr('href');

            // Concatena el string "https://www.twitch.com" a la URL
            $urlCompleta = "https://www.twitch.com" . $url;

            // Muestra cada URL completa en la consola (puedes omitir esta línea si no es necesario)
            //echo "URL completa: $urlCompleta\n";

            // Agrega la URL completa al array
            $urls[] = $urlCompleta;

            //Añade la URL al archivo de URLs
            $url_con_coma = $urlCompleta . ",\n";
            $this->guardarTextoEnArchivo($url_con_coma, 'urls_clips.txt', false);


        });


        foreach ($urls as $url_video){
            $this->obtenerUrlVideo($url_video);
        }


    }


    function obtenerUrlVideo($url){


        if (!env('ENTORNO') == 'windows'){
            $body = Browsershot::url($url)
                ->setOption('waitUntil', 'networkidle0')
                ->timeout(60000)
                ->bodyHtml(); // returns the html of the body

            $this->guardarTextoEnArchivo($body);
        }else{
            // $crawler = new Crawler($html);
            $body = Storage::get('clip_example.txt');
        }



        //Limpiamos el archivo.
        $this->guardarTextoEnArchivo('', 'urls_videos.txt', true);



        $crawler = new Crawler($body);

        // Selecciona todos los elementos que coinciden con el patrón de etiqueta video
        $elementos = $crawler->filter('video');

        // Inicializa un array para almacenar las URLs
        $urls = [];

        // Itera sobre cada elemento y extrae la URL del atributo src
        $elementos->each(function (Crawler $elemento) use (&$urls) {
            $url = $elemento->attr('src');

            // Agrega la URL completa al array
            $urls[] = $url;
            $this->descargarVideo($url);

            // Añade la URL al archivo de URLs
            $url_con_coma = $url . ",\n";
             $this->guardarTextoEnArchivo($url_con_coma, 'urls_videos.txt', false);
        });

        echo $urls;
    }


}
