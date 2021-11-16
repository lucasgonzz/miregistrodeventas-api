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
        for ($user_id=1; $user_id < 4; $user_id++) { 
            Schedule::create([
                'user_id' => $user_id,
                'name' => 'Mañana',
                'from' => '08:00:00',
                'to' => '13:00:00',
            ]);
            Schedule::create([
                'user_id' => $user_id,
                'name' => 'Siesta',
                'from' => '13:00:00',
                'to' => '17:00:00',
            ]);
            Schedule::create([
                'user_id' => $user_id,
                'name' => 'Tarde',
                'from' => '17:00:00',
                'to' => '20:00:00',
            ]);
            Schedule::create([
                'user_id' => $user_id,
                'name' => 'Noche',
                'from' => '20:00:00',
                'to' => '23:00:00',
            ]);
        }
    }
}
