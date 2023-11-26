<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;

class TwitchController extends Controller
{

    public static function routes()
    {

        Route::group([ 'prefix' => 'twitch', 'as' => 'twitch.' ], function () {

                Route::get('prueba', [ TwitchController::class, 'prueba' ])->name('prueba');
//            Route::post('datatable', [ TwitchController::class, 'datatable' ])->name('datatable');

        });
    }




    /**
     * Delete the user's account.
     */
    public function prueba(Request $request): RedirectResponse
    {



            // URL de la página web que deseas capturar
//            $url = 'https://www.twitch.tv/illojuan/clips?featured=false&filter=clips&range=all';
            $url = 'https://www.marca.com';

            // Usar Browsershot para capturar la pantalla
//            Browsershot::url($url)
//                //->content()
//                ->setOption('waitUntil', 'networkidle0')
//                ->timeout(60000) // Aumentar el tiempo de espera a 60 segundos
//                ->save($rutaArchivo);

            $body = Browsershot::url($url)
                ->setOption('waitUntil', 'networkidle0')
                ->timeout(60000)
                ->bodyHtml(); // returns the html of the body
            dd($body);






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
        });

        // Verifica si se encontraron elementos
        if (count($urls) > 0) {
            // Muestra el array completo (puedes omitir esta línea si no es necesario)
            dd($urls);

        // Aquí puedes realizar cualquier otra acción con el array de URLs
        } else {
            dd('No se encontraron elementos que coincidan con el patrón.');
            echo "No se encontraron elementos que coincidan con el patrón.\n";
        }


    }


}
