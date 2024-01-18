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
        // Ejecutar el comando crear_media_twitch
        $schedule->command('crear_media_twitch')->cron('0 10,12,15,17,19,22 * * *');

        // Ejecutar el comando publicar_media_twitch
        $schedule->command('publicar_media_twitch')->cron('0 11,13,16,18,20,23 * * *');

        // Ejecutar el comando actualizar_informacion_clips_twitch
        $schedule->command('actualizar_informacion_clips_twitch')->dailyAt('1:00');

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
