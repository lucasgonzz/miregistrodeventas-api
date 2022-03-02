<?php

use App\Permission;
use App\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lite = Plan::create([
            'name' => 'Lite'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->pluck('id');
        $lite->permissions()->sync($permissions);

        $basico = Plan::create([
            'name' => 'Basico'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'articles.cost')
                                ->orWhere('slug', 'articles.stock')
                                ->orWhere('slug', 'providers')
                                ->orWhere('slug', 'special_prices')
                                ->orWhere('slug', 'categories')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'employees')
                                ->orWhere('slug', 'discounts_sellers')
                                ->orWhere('slug', 'clients.index')
                                ->pluck('id');
        $basico->permissions()->sync($permissions);

        $premium = Plan::create([
            'name' => 'Premium'
        ]);
        $permissions = Permission::where('slug', 'articles.store')
                                ->orWhere('slug', 'articles.index')
                                ->orWhere('slug', 'articles.cost')
                                ->orWhere('slug', 'articles.stock')
                                ->orWhere('slug', 'articles.images')
                                ->orWhere('slug', 'providers')
                                ->orWhere('slug', 'special_prices')
                                ->orWhere('slug', 'categories')
                                ->orWhere('slug', 'sales.store')
                                ->orWhere('slug', 'sales.index')
                                ->orWhere('slug', 'employees')
                                ->orWhere('slug', 'discounts_sellers')
                                ->orWhere('slug', 'clients.index')
                                ->orWhere('slug', 'online.orders')
                                ->orWhere('slug', 'online.questions')
                                ->orWhere('slug', 'online.buyers')
                                ->orWhere('slug', 'online.messages')
                                ->orWhere('slug', 'online.cupons')
                                ->orWhere('slug', 'tags')
                                ->orWhere('slug', 'colors')
                                ->orWhere('slug', 'sizes')
                                ->orWhere('slug', 'brands')
                                ->orWhere('slug', 'descriptions')
                                ->orWhere('slug', 'conditions')
                                ->orWhere('slug', 'articles.with_dolar')
                                ->pluck('id');
        $premium->permissions()->sync($permissions);
    }
}
