<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar el comando crear_media_twitch cada hora
        $schedule->command('crear_media_twitch')->hourlyAt(30);

        // Esperar cinco minutos y luego ejecutar el comando publicar_media_twitch cada hora
        $schedule->command('publicar_media_twitch')->hourlyAt(35);

        // Busca nuevos clips entre los canales
        $schedule->command('actualizar_informacion_clips_twitch')->everyTwoHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
