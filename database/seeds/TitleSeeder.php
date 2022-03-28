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
        $titles = [
            [
                'user_id'   => $user->id,
                'header'    => 'Actualízate hoy',
                'lead'      => null,
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png'
            ],
            [
                'user_id'   => $user->id,
                'header'    => 'Equipos usados',
                'lead'      => 'Con la mejor condicion de bateria A+',
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png'
            ],
            [
                'user_id'   => $user->id,
                'header'    => 'Todas las marcas',
                'lead'      => null,
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png',
            ],
        ];
        foreach ($titles as $title) {
            Title::create([
                'user_id'   => $title['user_id'],
                'header'    => $title['header'],
                'lead'      => $title['lead'],
                'color'     => $title['color'],
                'image_url' => $title['image_url'],
            ]);
        }
    }
}
