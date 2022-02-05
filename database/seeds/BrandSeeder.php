<?php

use App\Brand;
use App\User;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'Fiushh')->first();
        $brands = ['Apple', 'Samsung'];
        foreach ($brands as $brand) {
            Brand::create([
                'name'      => $brand,
                'user_id'   => $user->id,
            ]);
        }
    }
}
