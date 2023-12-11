<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UrlCanal;
use App\Models\UrlClip;
use App\Models\Video;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;

class CanalesController extends Controller
{

    public static function routes()
    {

        Route::group([ 'prefix' => 'canales', 'as' => 'canales.' ], function () {

                Route::post('guardar', [ CanalesController::class, 'guardar' ])->name('guardar');
                Route::get('recopilar-clips', [ CanalesController::class, 'recopilarClips' ])->name('recopilar-clips');
                Route::get('recopilar-videos', [ CanalesController::class, 'recopilarUrlVideos' ])->name('recopilar-videos');

        });
    }


    /**
     * Metodo principal
     */
    public function guardar(Request $request)
    {
        try {
//            dd($request->all(), explode('/', $request->url_canal)[3]);

            if ($request->has('url_canal')){
                DB::beginTransaction();

                $canal = new UrlCanal();
                $canal->url = $request->url_canal;
                $canal->nombre_canal = explode('/', $request->url_canal)[3];
                $canal->save();

                DB::commit();
                \request()->session()->flash('success', 'Canal añadido con éxito');
                return redirect()->route('dashboard');

            }


        }catch (\Exception $e){
            dd($e);
        }
    }


    /**
     * Metodo para recopilar los clips de un canal y guardarlos si son nuevos
     */
    public function recopilarClips($canal = 1){

        try {
            $canal = UrlCanal::find($canal);

            DB::beginTransaction();

            $url = $canal->url;
            if (isset($url)){

                $body = Browsershot::url($url)
                    ->setNodeBinary('/home/vagrant/.nvm/versions/node/v21.2.0/bin/node') // Ruta específica de tu versión de Node.js
                    ->setNpmBinary('/home/vagrant/.nvm/versions/node/v21.2.0/bin/npm')   // Ruta específica de tu versión de npm
                    ->setChromePath('/usr/bin/chromium-browser') // Ruta específica de tu instalación global de Chromium
                    ->setOption('waitUntil', 'networkidle0')
                    ->timeout(60000)
                    ->bodyHtml(); // returns the html of the body

                $crawler = new Crawler($body);

                // Selecciona todos los elementos que coinciden con el patrón
                $elementos = $crawler->filter('a[data-a-target="preview-card-image-link"]');

                // Itera sobre cada elemento y extrae la URL del atributo href
                $elementos->each(function (Crawler $elemento) use ($canal) {
                    $url = $elemento->attr('href');
                    $tituloClip = $elemento->filter('img')->attr('alt');


                    // Concatena el string "https://www.twitch.com" a la URL
                    $urlCompleta = "https://www.twitch.com" . $url;

                    // Verifica si ya existe un objeto con la misma URL
                    $existingClip = UrlClip::where('url', $urlCompleta)->first();

                    if (!$existingClip) {
                        $clip = new UrlClip();
                        $clip->url = $urlCompleta;
                        $clip->id_url_canal = $canal->id;
                        $clip->titulo_clip = $tituloClip;
                        $clip->save();
                    }


                });

            }

            DB::commit();
            return response('Operación exitosa', 200);



        }catch (\Exception $e){
            dd($e);
        }

    }



    /**
     * Metodo para recopilar la url del clip de un vídeo
     */
    public function recopilarUrlVideos($clip = 1){

        try {
            $clip = UrlClip::find($clip);

            DB::beginTransaction();

            $url = $clip->url;
            if (isset($url)){

                $body = Browsershot::url($url)
                    ->setNodeBinary('/home/vagrant/.nvm/versions/node/v21.2.0/bin/node') // Ruta específica de tu versión de Node.js
                    ->setNpmBinary('/home/vagrant/.nvm/versions/node/v21.2.0/bin/npm')   // Ruta específica de tu versión de npm
                    ->setChromePath('/usr/bin/chromium-browser') // Ruta específica de tu instalación global de Chromium
                    ->setOption('waitUntil', 'networkidle0')
                    ->timeout(60000)
                    ->bodyHtml(); // returns the html of the body

                $crawler = new Crawler($body);

                // Selecciona todos los elementos que coinciden con el patrón de etiqueta video
                $elementos = $crawler->filter('video');

                // Itera sobre cada elemento y extrae la URL del atributo href
                $elementos->each(function (Crawler $elemento) use ($clip) {
                    $url = $elemento->attr('src');

                    // Verifica si ya existe un objeto con la misma URL
                    $existingVideo = Video::where('url', $url)->first();

                    if (!$existingVideo) {
                        $video = new Video();
                        $video->url = $url;
                        $video->id_url_clip = $clip->id;
                        $video->save();

                        $clip->obtenido_video = true;
                        $clip->save();

                    }


                });

            }

            DB::commit();
            return response('Operación exitosa', 200);



        }catch (\Exception $e){
            dd($e);
        }

    }



}
