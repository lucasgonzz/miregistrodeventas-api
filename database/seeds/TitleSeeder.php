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
        $fiushh = User::where('company_name', 'Fiushh')->first();
        $pinocho = User::where('company_name', 'Pinocho')->first();
        $kas_aberturas = User::where('company_name', 'kas aberturas')->first();
        $colman = User::where('company_name', 'colman')->first();
        $titles = [
            // Fisuhh
            [
                'user_id'   => $fiushh->id,
                'header'    => 'Actualízate hoy',
                'lead'      => null,
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png',
                'hosting_image_url' => ''
            ],
            [
                'user_id'   => $fiushh->id,
                'header'    => 'Equipos usados',
                'lead'      => 'Con la mejor condicion de bateria A+',
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png',
                'hosting_image_url' => ''
            ],
            [
                'user_id'   => $fiushh->id,
                'header'    => 'Todas las marcas',
                'lead'      => null,
                'color'     => '#f7f0fe',
                'image_url' => 'v1643308728/articles/zzur3sccerk7f7vndkjn.png',
                'hosting_image_url' => ''
            ],
            // Pinocho
            [
                'user_id'   => $pinocho->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351573/articles/bofqeirvpjhiftewmx07.png',
                'hosting_image_url' => '',
            ],
            [
                'user_id'   => $pinocho->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351480/articles/dopmuvwafreurlxjbeim.png',
                'hosting_image_url' => '',
            ],
            [
                'user_id'   => $pinocho->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351459/articles/juzmron8lzikfagntdjh.png',
                'hosting_image_url' => '',
            ],
            // Kas
            [
                'user_id'   => $kas_aberturas->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351573/articles/bofqeirvpjhiftewmx07.png',
                'hosting_image_url' => '',
            ],
            [
                'user_id'   => $kas_aberturas->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351480/articles/dopmuvwafreurlxjbeim.png',
                'hosting_image_url' => '',
            ],
            [
                'user_id'   => $kas_aberturas->id,
                'header'    => null,
                'lead'      => null,
                'color'     => '#f9b234',
                'image_url' => 'v1649351459/articles/juzmron8lzikfagntdjh.png',
                'hosting_image_url' => '',
            ],
            // Colman
            [
                'user_id'           => $colman->id,
                'header'            => null,
                'lead'              => null,
                'color'             => '#FFF',
                'image_url'         => '',
                'hosting_image_url' => 'http://miregistrodeventas.local:8001/storage/ral7blncvvjhvzywccz7.webp'
            ],
        ];
        foreach ($titles as $title) {
            Title::create([
                'user_id'           => $title['user_id'],
                'header'            => $title['header'],
                'lead'              => $title['lead'],
                'color'             => $title['color'],
                'image_url'         => $title['image_url'],
                'hosting_image_url' => $title['hosting_image_url'],
            ]);
        }
    }

}
