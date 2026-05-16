<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function commands()
    {
        // Load commands from the Commands directory if any
        if (is_dir($dir = __DIR__.'/Commands')) {
            $this->load($dir);
        }
    }

    protected function schedule(Schedule $schedule)
    {
        // Schedule your jobs here
    }
}
