<?php

namespace App\Console\Commands;


use App\Http\Controllers\CanalesController;
use App\Http\Controllers\TwitchController;
use App\Models\UrlCanal;
use App\Models\UrlClip;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class PublicarMediaTwitch extends Command
{

    protected $signature = 'publicar_media_twitch';

    protected $description = '';

    public function __construct(TwitchController $contolador_twitch)
    {
        parent::__construct();
        $this->contolador_twitch = $contolador_twitch;

    }

    public function handle()
    {

        try {
            $this->comment(PHP_EOL . "PUBLICAR MEDIA" . PHP_EOL);

            $video = Video::whereNotNull('id_contenedor_publicacion')
//                ->whereNull('id_publicacion')
                ->where('subido', 0)
                ->inRandomOrder()
                ->take(1)
                ->first();
            Log::info('Id video a publicar: ' . $video->id);
            $this->contolador_twitch->publicarMedia($video->id);
            Log::info('El comando publicar_media_twitch ha sido ejecutado correctamente.');

            $this->comment(PHP_EOL . "FIN PUBLICAR MEDIA" . PHP_EOL);
        } catch (\Exception $e) {
            // Log de fracaso con detalles de la excepción
            Log::error('Error al ejecutar crear_media_twitch: ' . $e->getMessage());
        }

    }

}
