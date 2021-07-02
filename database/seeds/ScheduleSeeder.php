<?php

use App\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $commerce = App\User::where('name', 'Mi Negocio')->first();
        Schedule::create([
            'user_id' => $commerce->id,
            'name' => 'Mañana',
            'from' => '08:00:00',
            'to' => '13:00:00',
        ]);
        Schedule::create([
            'user_id' => $commerce->id,
            'name' => 'Tarde',
            'from' => '16:00:00',
            'to' => '20:00:00',
        ]);
    }
}
