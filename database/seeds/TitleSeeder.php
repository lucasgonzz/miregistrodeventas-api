<?php

use App\Title;
use App\User;
use Illuminate\Database\Seeder;

class TitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('company_name', 'Fiushh')->first();
        Title::create([
            'user_id'   => $user->id,
            'header'    => 'Actualízate hoy',
            'color'     => '#f7f0fe'
            'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png'
        ]);
    }
}
