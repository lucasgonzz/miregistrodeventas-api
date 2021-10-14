<?php

namespace App\Console;

use App\Cupon;
use App\Http\Controllers\Helpers\TwilioHelper;
use Carbon\Carbon;
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
        //
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
            $cupons = Cupon::where('expiration_date', '>=', Carbon::now())
                            ->where('valid', 1)
                            ->get();
            foreach ($cupons as $cupon) {
                $cupon->valid = 0;
                $cupon->save();
            }
            $cupons = Cupon::where('expiration_date', '>=', Carbon::now()->addDays(2))
                            ->where('valid', 1)
                            ->get();

            foreach ($cupons as $cupon) {
                $diff = Carbon::createFromDate($cupon->exporation_date)->diffForHumans();
                $message = 'Tu cupon vence en '.$diff;
                $title = 'Usa tu cupon de ';
                if (!is_null($cupon->amount)) {
                    $title .= '$'.$cupon->amount;
                } else {
                    $title .= $cupon->percentage.'%';
                }
                $title .= ' de descuento';
                TwilioHelper::sendNotification($cupon->buyer_id, $title, $message);
            }

        })->dailyAt('13:43');
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
