<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionsTableSeeder::class);
        $this->call(FeaturesSeeder::class);
        $this->call(PlansSeeder::class);
        // $this->call(RoleTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(IconsTableSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SubCategorySeeder::class);
        $this->call(SellerSeeder::class);
        $this->call(ProvidersTableSeeder::class);
        $this->call(ColorSeeder::class);
        $this->call(ArticlesTableSeeder::class);
        $this->call(ClientsTableSeeder::class);
        $this->call(BuyerSeeder::class);
        // $this->call(SaleTimesTableSeeder::class);
        // $this->call(CollectionsSeeder::class);
        // $this->call(ImageSeeder::class);
        $this->call(DiscountSeeder::class);
        $this->call(CommissionerSeeder::class);
        $this->call(SaleTypeSeeder::class);
        $this->call(CurrentAcountSeeder::class);
        $this->call(SalesTableSeeder::class);
        $this->call(ScheduleSeeder::class);
        $this->call(MessageSeeder::class);
        $this->call(AddressSeeder::class);
        $this->call(WorkdaySeeder::class);
        $this->call(ConditionSeeder::class);
        $this->call(TitleSeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(SizeSeeder::class);
        $this->call(PricesListSeeder::class);
    }
}
