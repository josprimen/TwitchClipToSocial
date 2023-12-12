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
        $this->comment(PHP_EOL . "PUBLICAR MEDIA" . PHP_EOL);

        $video = Video::whereNotNull('id_contenedor_publicacion')
            ->whereNull('id_publicacion')
            ->inRandomOrder()
            ->take(1)
            ->get();
        $this->contolador_twitch->publicarMedia($video->id);

        $this->comment(PHP_EOL . "FIN PUBLICAR MEDIA" . PHP_EOL);

    }

}
