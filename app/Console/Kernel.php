<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
       
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        
        $schedule->command('pulledi:cron')
                ->everyThirtyMinutes();
        $schedule->command('poedi:cron')
            ->everyThirtyMinutes();
        $schedule->command('createteaorder:cron')
              ->everyThirtyMinutes();
        $schedule->command('createteaorderautoany:cron')
              ->everyThirtyMinutes();
        $schedule->command('updateshipping:cron')
          ->everyThirtyMinutes();
        
        $schedule->command('updateintransit:cron')
           ->everyThirtyMinutes();
        $schedule->command('sendshippingnotice:cron')
            ->everyThirtyMinutes();
        $schedule->command('sendshippingnoticeautoany:cron')
            ->everyThirtyMinutes();
        $schedule->command('sendinvoicenotice:cron')
                ->hourly();
        $schedule->command('sendinvoicenoticeautoany:cron')
                ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
