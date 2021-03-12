<?php

use Illuminate\Database\Seeder;
use App\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $lucas = User::create([
            'name' => 'Lucas',
        	'company_name' => 'Lucas',
            'status' => 'in_use',
        	'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire' => Carbon::now()->addWeeks(4),
        ]);
        $lucas->roles()->sync([1,2]);
        // $lucas->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        $commerce = User::create([
            'name'          => 'Mi Negocio',
            'online'        => 'http://localhost:8080',
            'email'         => 'marcos@gmail.com',
            'company_name'  => 'Mi Negocio',
            'status'        => 'trial',
            'password'      => bcrypt('1234'),
            // 'admin_id'      => $admin->id,
            'created_at'    => Carbon::now()->subMonths(2),
            'expire'        => Carbon::now()->subDay(),
        ]);
        $commerce->roles()->sync([1,3]);
        // $commerce->permissions()->sync([10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        $commerce = User::create([
            'name' => 'Fran',
            'company_name' => 'Lo de Fran',
            'status' => 'trial',
            'password' => bcrypt('1234'),
            // 'admin_id' => $admin->id,
            'created_at' => Carbon::now(),
            'expire' => Carbon::now(),
        ]);
        $commerce->roles()->sync([1,3]);
        // $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // $commerce = User::create([
        //     'name' => 'Juan',
        //     'company_name' => 'Lo de Juan',
        //     'status' => 'trial',
        //     'password' => bcrypt('1234'),
        //     'admin_id' => $admin->id,
        //     'created_at' => Carbon::now()->subWeeks(2),
        //     'expire' => Carbon::now()->subDay(),
        // ]);
        // $commerce->roles()->sync([1,3]);
        // $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);

        // for ($user_id=1; $user_id <= 8; $user_id++) { 
        //     $commerce = User::create([
        //         'company_name' => 'Negocio '.$user_id,
        //         'status' => 'for_trial',
        //         'password' => bcrypt('1234'),
        //         'admin_id' => $admin->id,
        //         'created_at' => Carbon::now(),
        //     ]);
        //     $commerce->roles()->sync([1,3]);
        //     $commerce->permissions()->sync([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]);
        // }

        // $super = User::create([
        //     'name' => 'Lucas',
        //     'status' => 'super',
        //     'password' => bcrypt('1234'),
        // ]);

        // $admin = User::create([
        //     'name' => 'Admin',
        //     'status' => 'admin',
        //     'password' => bcrypt('1234'),
        // ]);
    }
}
