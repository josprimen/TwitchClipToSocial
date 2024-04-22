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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Sunra\PhpSimple\HtmlDomParser;
use Symfony\Component\DomCrawler\Crawler;
use Yajra\DataTables\Facades\DataTables;

class TratamientoVideoController extends Controller
{

    public static function routes()
    {

        Route::group([ 'prefix' => 'tratamiento-video', 'as' => 'tratamiento-video.' ], function () {

            Route::get('transformar-video', [ self::class, 'transformarVideo' ])->name('transformar-video');

        });
    }


    /**
     * Método inicial de prueba
     */
    public function transformarVideo(Request $request)
    {
        try {

            // Ruta del vídeo de entrada
            $inputVideo = public_path('videos/video_16_9.mp4');
            $inputVideo1 = public_path('videos/video_16_9.mp4');
            $inputVideo2= public_path('videos/video_16_9.mp4');

            // Ruta del vídeo de salida
            $outputVideo = public_path('videos/video_9_16_opcion83.mp4');

            // Comando para convertir el vídeo
//            $command = "ffmpeg -i $inputVideo -vf 'scale=ih*9/16:ih,pad=ih:ih*16/9:(ow-iw)/2:(oh-ih)/2' -c:v libx264 -c:a copy $outputVideo"; //escala el video
//            $command = "ffmpeg -i $inputVideo -vf 'scale=iw:ih*16/9,pad=iw:ih+2*(ih-ih*9/16):(ow-iw)/2:(oh-ih)/2' -c:v libx264 -c:a copy $outputVideo"; //escala menos el video y añade franjas negras

            $command = "ffmpeg -i $inputVideo1 -i $inputVideo2 -filter_complex \"[0:v]scale=2276:1280,boxblur=4[bg];[1:v]scale=720:-1[fg];[bg][fg]overlay=(W-w)/2:(H-h)/2[tmp];[tmp]crop=720:1280:(2276-720)/2:0[out]\" -map [out] -map 0:a $outputVideo"; //no escala el video y añade blur al fondo

            exec($command, $output, $returnCode);

            return '¡Vídeo convertido exitosamente!';


        }catch (\Exception $e){
            dd($e);
        }
    }

    /**
     * Descarga y transforma un video a 9:16
     */
    public function transformarVideo916($url)
    {

        if (!Storage::exists('videos'))
        {
            Storage::makeDirectory('videos');
        }


        // Descargar el video en la carpeta storage
        $url_video = $url;
        $downloaded_video_path = storage_path('videos/' . 'descargado.mp4');
        $curl = curl_init();
        $file = fopen($downloaded_video_path, 'wb');

        curl_setopt_array($curl, [
            CURLOPT_URL => $url_video,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FILE => $file,
            CURLOPT_FOLLOWLOCATION => true, // Seguir redirecciones
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        fclose($file);

        // Definir las rutas de entrada y salida para FFmpeg
        $inputVideo1 = $downloaded_video_path;
        $inputVideo2 = $downloaded_video_path; // Usamos el mismo video como segunda entrada
        $outputVideo = storage_path('videos/output_' . time() . '.mp4'); // Agregar timestamp al nombre del archivo de salida

        // Comando de FFmpeg
        $command = "ffmpeg -i $inputVideo1 -i $inputVideo2 -filter_complex \"[0:v]scale=2276:1280,boxblur=4[bg];[1:v]scale=720:-1[fg];[bg][fg]overlay=(W-w)/2:(H-h)/2[tmp];[tmp]crop=720:1280:(2276-720)/2:0[out]\" -map [out] -map 0:a $outputVideo";

        // Ejecutar el comando de FFmpeg
        exec($command, $output, $returnCode);

        // Eliminar el video descargado original
        unlink($downloaded_video_path);

        // Retornar la URL del video resultante
        return $outputVideo;
    }


}
