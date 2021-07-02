<?php

use App\Message;
use App\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $commerce = User::where('name', 'Mi Negocio')->first();
        Message::create([
            'text' => 'Hola',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Como andas',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
        Message::create([
            'text' => 'Todo bien vos',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Todo bien me alegro',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
        Message::create([
            'text' => 'Hola',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Como andas',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
        Message::create([
            'text' => 'Todo bien vos',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Todo bien me alegro',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
        Message::create([
            'text' => 'Hola',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Como andas',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
        Message::create([
            'text' => 'Todo bien vos',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
            'from_buyer' => true,
        ]);
        Message::create([
            'text' => 'Todo bien me alegro',
            'user_id' => $commerce->id,
            'buyer_id' => 1,
        ]);
    }
}
