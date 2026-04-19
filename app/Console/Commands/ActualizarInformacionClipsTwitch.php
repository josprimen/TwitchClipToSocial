<?php

namespace App\Console\Commands;


use App\Http\Controllers\CanalesController;
use App\Models\UrlCanal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ActualizarInformacionClipsTwitch extends Command
{

    protected $signature = 'actualizar_informacion_clips_twitch';

    protected $description = '';

    public function __construct(CanalesController $contolador_canales)
    {
        parent::__construct();
        $this->contolador_canales = $contolador_canales;

    }

    public function handle()
    {
        $this->comment(PHP_EOL . "CARGA INFORMACION CLIPS CANALES" . PHP_EOL);
        Log::info('Inicio recopilacion clips');
        $canales = UrlCanal::all();
        $count = $canales->count();
        $output = new ConsoleOutput();
        $bar = new ProgressBar($output, $count);

        foreach ($canales as $canal){
            $this->contolador_canales->recopilarClips($canal->id);
            $bar->advance();
        }

        Log::info('Recopilados clips');
        $bar->finish();
        $this->comment(PHP_EOL . "CARGA INFORMACION TERMINADA" . PHP_EOL);

    }

}
