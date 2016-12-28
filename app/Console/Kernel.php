<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use VKAction;
use TumblrAction;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Tests::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
			$intCurTime = intval(date('G'))*60 + intval(date('i'));
			$objTasks = DB::table('tasks')->where('time', $intCurTime)->get();
			if (count($objTasks)) {
				foreach ($objTasks as $objTask) {
					VKAction::sendPost($objTask->id);
					if (count($objTasks) > 1) sleep(120);
				}//\\ foreach
			}//\\ if
		})->everyMinute();
		
		
		$schedule->call(function () {
			TumblrAction::getPostsFromDashboard();
		})->everyFiveMinutes();
		
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
