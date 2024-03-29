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
        if (env('FOR_SERVER') == 'la_barraca') {
            $this->call(UsersTableSeeder::class);
            $this->call(ExtencionSeeder::class);
            $this->call(PermissionsTableSeeder::class);
            $this->call(FeaturesSeeder::class);
            $this->call(PlansSeeder::class);
            $this->call(OrderStatusSeeder::class);
            $this->call(ProviderOrderStatusSeeder::class);
            $this->call(ColorSeeder::class);
            $this->call(IvaSeeder::class);
            $this->call(IvaConditionSeeder::class);
            $this->call(WorkdaySeeder::class);
            $this->call(CurrentAcountPaymentMethodSeeder::class);
            $this->call(BudgetStatusSeeder::class);
        } else {
            $this->call(ExtencionSeeder::class);
            $this->call(PermissionsTableSeeder::class);
            $this->call(FeaturesSeeder::class);
            $this->call(PlansSeeder::class);
            $this->call(OrderStatusSeeder::class);
            $this->call(ProviderOrderStatusSeeder::class);
            $this->call(UsersTableSeeder::class);
            $this->call(IconsTableSeeder::class);
            $this->call(CategorySeeder::class);
            $this->call(SubCategorySeeder::class);
            $this->call(SellerSeeder::class);
            $this->call(ProvidersTableSeeder::class);
            $this->call(ColorSeeder::class);
            $this->call(IvaSeeder::class);
            $this->call(DepositSeeder::class);
            $this->call(ArticlesTableSeeder::class);
            $this->call(IvaConditionSeeder::class);
            $this->call(ClientsTableSeeder::class);
            $this->call(BuyerSeeder::class);
            $this->call(DiscountSeeder::class);
            $this->call(SurchageSeeder::class);
            $this->call(CommissionerSeeder::class);
            $this->call(SaleTypeSeeder::class);
            $this->call(CurrentAcountSeeder::class);
            $this->call(ScheduleSeeder::class);
            $this->call(AddressSeeder::class);
            $this->call(EmployeeSeeder::class);
            $this->call(SalesTableSeeder::class);
            $this->call(WorkdaySeeder::class);
            $this->call(ConditionSeeder::class);
            $this->call(TitleSeeder::class);
            $this->call(BrandSeeder::class);
            $this->call(SizeSeeder::class);
            $this->call(PricesListSeeder::class);
            $this->call(PlateletSeeder::class);
            $this->call(OrderProductionStatusSeeder::class);
            $this->call(CurrentAcountPaymentMethodSeeder::class);
            $this->call(PaymentMethodSeeder::class);
            $this->call(DeliveryZoneSeeder::class);
            $this->call(LocationSeeder::class);
            $this->call(PaymentMethodTypeSeeder::class);
            $this->call(CuponSeeder::class);
            $this->call(PriceTypeSeeder::class);
            $this->call(BudgetStatusSeeder::class);
            $this->call(RecipeSeeder::class);
            $this->call(OrderProductionSeeder::class);
            $this->call(SuperBudgetSeeder::class);
            $this->call(CreditCardSeeder::class);
            $this->call(CreditCardPaymentPlanSeeder::class);
            $this->call(UpdateFeatureSeeder::class);
            $this->call(OrderSeeder::class);
        }
    }
}
