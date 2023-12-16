<?php

namespace App\Console\Commands;


use App\Http\Controllers\CanalesController;
use App\Http\Controllers\TwitchController;
use App\Models\UrlCanal;
use App\Models\UrlClip;
use App\Models\Video;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class CrearMediaTwitch extends Command
{

    protected $signature = 'crear_media_twitch';

    protected $description = '';

    public function __construct(TwitchController $contolador_twitch, CanalesController $contolador_canales)
    {
        parent::__construct();
        $this->contolador_twitch = $contolador_twitch;
        $this->contolador_canales = $contolador_canales;


    }

    public function handle()
    {
        $this->comment(PHP_EOL . "CREAR MEDIA" . PHP_EOL);



        $clip = UrlClip::where('obtenido_video', false)->inRandomOrder()->first();

        $video = $this->contolador_canales->recopilarUrlVideos($clip->id);

        if (!is_null($video)){
            $this->contolador_twitch->crearMedia($video->id);
        }


        $this->comment(PHP_EOL . "FIN CREAR MEDIA" . PHP_EOL);

    }

}
