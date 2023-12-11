<?php

namespace App\Console\Commands;


use App\Http\Controllers\CanalesController;
use App\Models\UrlCanal;
use App\Models\UrlClip;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ActualizarInformacionVideosTwitch extends Command
{

    protected $signature = 'actualizar_informacion_videos_twitch';

    protected $description = '';

    public function __construct(CanalesController $contolador_canales)
    {
        parent::__construct();
        $this->contolador_canales = $contolador_canales;

    }

    public function handle()
    {
        $this->comment(PHP_EOL . "CARGA INFORMACION VIDEOS CANALES" . PHP_EOL);

        $clips = UrlClip::where('obtenido_video', false)->get();
        $count = $clips->count();
        $output = new ConsoleOutput();
        $bar = new ProgressBar($output, $count);

        foreach ($clips as $clip){
            $this->contolador_canales->recopilarUrlVideos($clip->id);
            $bar->advance();
        }

        $bar->finish();
        $this->comment(PHP_EOL . "CARGA INFORMACION TERMINADA" . PHP_EOL);

    }

}
