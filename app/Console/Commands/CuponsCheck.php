<?php

namespace App\Console\Commands;

use App\Cupon;
use App\Http\Controllers\Helpers\Numbers;
use App\Http\Controllers\Helpers\TwilioHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CuponsCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cupons:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checkea la fecha de vencimiento de los cupones y avisa en caso de que esta proximo a vencer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cupons = Cupon::where('expiration_date', '<=', Carbon::now())
                            ->where('valid', 1)
                            ->get();
        foreach ($cupons as $cupon) {
            $cupon->valid = 0;
            $cupon->save();
        }

        $cupons = Cupon::where('expiration_date', '<=', Carbon::now()->addDays(3))
                        ->where('valid', 1)
                        ->get();
        foreach ($cupons as $cupon) {
            $diff = Carbon::createFromDate($cupon->exporation_date)->diffForHumans();
            $message = 'Tu cupon vence el '.Carbon::parse($cupon->exporation_date)->diffInDays();
            $title = 'Usa tu cupon de ';
            if (!is_null($cupon->amount)) {
                $title .= '$'.Numbers::price($cupon->amount);
            } else {
                $title .= Numbers::price($cupon->percentage).'%';
            }
            $title .= ' de descuento';
            TwilioHelper::sendNotification($cupon->buyer_id, $title, $message);
        }
    }
}
