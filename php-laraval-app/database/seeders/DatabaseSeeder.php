<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            RolesSeeder::class,
            CountriesSeeder::class,
            CitiesSeeder::class,
            KeyPlacesSeeder::class,
            PropertyTypeSeeder::class,
            GlobalSettingsSeeder::class,
            DefaultServicesSeeder::class,
            DefaultServiceQuartersSeeder::class,
            DefaultProductsSeeder::class,
            UsersSeeder::class,
            UserCompaniesSeeder::class,
            BlockDaysSeeder::class,
            ServicesSeeder::class,
            ProductsSeeder::class,
            CategoriesSeeder::class,
            StoresSeeder::class,
            AddFortnoxArticleIdSeeder::class,
            // SubscriptionSeeder::class,
            FixedPricesSeeder::class,
            CustomerDiscountsSeeder::class,
            NotificationSeeder::class,
            FeedbackSeeder::class,
            OauthSeeder::class,
            // UserTestSeeder::class,
            ScheduleSeeder::class,
            // SchedulePendingSeeder::class,
            ScheduleProductSeeder::class,
            // ScheduleDoneSeeder::class,
            ScheduleCleaningDeviationSeeder::class,
            TeamSeeder::class,
            OrderLaundrySeeder::class,
            CreditSeeder::class,
            // UnassignSubscriptionSeeder::class,
            WorkersWithoutSchedulesSeeder::class,
            LeaveRegistrationSeeder::class,
            ScheduleChangeRequestSeeder::class,
            WorkHourSeeder::class,
            TimeAdjustmentSeeder::class,
            PriceAdjustmentSeeder::class,
            StoreSeeder::class,
            StoreProductSeeder::class,
            LaundryOrderSeeder::class,
            LaundryOrderProductSeeder::class,
            LaundryOrderHistorySeeder::class,
            StoreSaleSeeder::class,
        ]);
    }
}
