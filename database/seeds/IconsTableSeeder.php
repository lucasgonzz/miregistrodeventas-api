<?php

use App\Icon;
use Illuminate\Database\Seeder;

class IconsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $icons = [
            ['name' => 'Celular', 'slug' => 'mobile'],
            ['name' => 'Telefono', 'slug' => 'phone'],
            ['name' => 'Casa', 'slug' => 'home'],
            ['name' => 'Camara', 'slug' => 'camera'],
            ['name' => 'Internet', 'slug' => 'network'],
            ['name' => 'Mensaje', 'slug' => 'message'],
            ['name' => 'Avion', 'slug' => 'send'],
            ['name' => 'Ubicacion', 'slug' => 'location'],
            ['name' => 'Corazon', 'slug' => 'heart'],
            ['name' => 'Cupon', 'slug' => 'cupon'],
            ['name' => 'Campana', 'slug' => 'notification'],
            ['name' => 'Usuario', 'slug' => 'user-black'],
            ['name' => 'Abajo', 'slug' => 'down'],
            ['name' => 'Bolsa', 'slug' => 'bag-o'],
            ['name' => 'WhatsApp', 'slug' => 'whatsapp'],
            ['name' => 'Diamante', 'slug' => 'diamond'],
            ['name' => 'Trofeo', 'slug' => 'trophy'],
            ['name' => 'Escudo', 'slug' => 'shield'],
        ];
        foreach ($icons as $icon) {
            Icon::create([
                'name' => $icon['name'],
                'slug' => $icon['slug'],
            ]);
        }
    }
}
