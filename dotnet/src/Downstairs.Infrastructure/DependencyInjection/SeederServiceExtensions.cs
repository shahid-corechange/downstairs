using Downstairs.Infrastructure.Persistence.Seeders.DataSources;
using Downstairs.Infrastructure.Persistence.Seeders.Foundation;
using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;

namespace Downstairs.Infrastructure.DependencyInjection;

/// <summary>
/// Dependency injection extensions for database seeding services
/// Organizes registration of all 41 active seeders plus optional/special purpose seeders
/// </summary>
public static class SeederServiceExtensions
{
    /// <summary>
    /// Register all active database seeders (41 seeders from Laravel DatabaseSeeder.php)
    /// </summary>
    public static IServiceCollection AddDatabaseSeeders(this IServiceCollection services)
    {
        // Register the orchestrator
        services.AddScoped<DatabaseSeederOrchestrator>();

        // Register foundation seeders (Order 10-70) - Core dependencies
        services.AddScoped<ISeeder, PermissionsSeeder>();
        services.AddScoped<ISeeder, RolesSeeder>();
        services.AddScoped<ISeeder, CountriesSeeder>();
        services.AddScoped<ISeeder, CitiesSeeder>();
        services.AddScoped<ISeeder, KeyPlacesSeeder>();
        services.AddScoped<ISeeder, PropertyTypeSeeder>();
        services.AddScoped<ISeeder, GlobalSettingsSeeder>();

        // Register service and product seeders (Order 100-170)
        services.AddScoped<ISeeder, DefaultServicesSeeder>();
        services.AddScoped<ISeeder, DefaultServiceQuartersSeeder>();
        services.AddScoped<ISeeder, DefaultProductsSeeder>();
        services.AddScoped<ISeeder, ServicesSeeder>();
        services.AddScoped<ISeeder, ProductsSeeder>();
        services.AddScoped<ISeeder, CategoriesSeeder>();
        services.AddScoped<ISeeder, StoresSeeder>();

        // Register user and company seeders (Order 200-210)
        services.AddScoped<ISeeder, UsersSeeder>();
        services.AddScoped<ISeeder, UserCompaniesSeeder>();

        // Register business logic seeders (Order 300-380)
        services.AddScoped<ISeeder, BlockDaysSeeder>();
        services.AddScoped<ISeeder, AddFortnoxArticleIdSeeder>();
        services.AddScoped<ISeeder, FixedPricesSeeder>();
        services.AddScoped<ISeeder, CustomerDiscountsSeeder>();
        services.AddScoped<ISeeder, NotificationSeeder>();
        services.AddScoped<ISeeder, FeedbackSeeder>();
        services.AddScoped<ISeeder, OauthSeeder>();
        services.AddScoped<ISeeder, TeamSeeder>();

        // Register workflow and schedule seeders (Order 400-490)
        services.AddScoped<ISeeder, ScheduleSeeder>();
        services.AddScoped<ISeeder, ScheduleProductSeeder>();
        services.AddScoped<ISeeder, ScheduleCleaningDeviationSeeder>();
        services.AddScoped<ISeeder, OrderLaundrySeeder>();
        services.AddScoped<ISeeder, CreditSeeder>();
        services.AddScoped<ISeeder, WorkersWithoutSchedulesSeeder>();
        services.AddScoped<ISeeder, LeaveRegistrationSeeder>();
        services.AddScoped<ISeeder, ScheduleChangeRequestSeeder>();
        services.AddScoped<ISeeder, WorkHourSeeder>();
        services.AddScoped<ISeeder, TimeAdjustmentSeeder>();
        services.AddScoped<ISeeder, PriceAdjustmentSeeder>();

        // Register sales and order seeders (Order 500-580)
        services.AddScoped<ISeeder, StoreSeeder>();
        services.AddScoped<ISeeder, StoreProductSeeder>();
        services.AddScoped<ISeeder, LaundryOrderSeeder>();
        services.AddScoped<ISeeder, LaundryOrderProductSeeder>();
        services.AddScoped<ISeeder, LaundryOrderHistorySeeder>();
        services.AddScoped<ISeeder, StoreSaleSeeder>();

        return services;
    }

    /// <summary>
    /// Register seeder data sources with configuration-driven paths
    /// </summary>
    public static IServiceCollection AddSeederDataSources(this IServiceCollection services, IConfiguration configuration)
    {
        var dataSourcesConfig = configuration.GetSection("DatabaseSeeding:DataSources");

        // Register data sources for files from Laravel project
        services.AddScoped<ISeederDataSource<Country>>(provider =>
            new JsonFileDataSource<Country>(
                dataSourcesConfig["CountriesFilePath"] ?? "Data/Seeders/countries.json"));

        services.AddScoped<ISeederDataSource<City>>(provider =>
            new SqlFileDataSource<City>(
                dataSourcesConfig["CitiesFilePath"] ?? "Data/Seeders/cities.sql",
                provider.GetRequiredService<DownstairsDbContext>()));

        services.AddScoped<ISeederDataSource<GlobalSetting>>(provider =>
            new JsonFileDataSource<GlobalSetting>(
                dataSourcesConfig["GlobalSettingsFilePath"] ?? "Data/Seeders/global_settings.json"));

        // Register configuration-based data sources
        services.AddScoped<ISeederDataSource<Permission>>(provider =>
            new ConfigurationDataSource<Permission>(
                provider.GetRequiredService<IConfiguration>(),
                "DatabaseSeeding:Permissions"));

        services.AddScoped<ISeederDataSource<Role>>(provider =>
            new ConfigurationDataSource<Role>(
                provider.GetRequiredService<IConfiguration>(),
                "DatabaseSeeding:Roles"));

        return services;
    }

    /// <summary>
    /// Register optional and special purpose seeders (not executed by default)
    /// </summary>
    public static IServiceCollection AddOptionalSeeders(this IServiceCollection services)
    {
        // Register commented/optional seeders (IsEnabled = false by default)
        services.AddScoped<ISeeder, SubscriptionSeeder>();
        services.AddScoped<ISeeder, UserTestSeeder>();
        services.AddScoped<ISeeder, SchedulePendingSeeder>();
        services.AddScoped<ISeeder, ScheduleDoneSeeder>();
        services.AddScoped<ISeeder, UnassignSubscriptionSeeder>();

        // Register special purpose seeders
        services.AddScoped<ISeeder, DatabaseMergeSeeder>();
        services.AddScoped<ISeeder, OldDatabaseSeeder>();
        services.AddScoped<ISeeder, SpecificScheduleSeeder>();
        services.AddScoped<ISeeder, InitialCashierSeeder>();
        services.AddScoped<ISeeder, PrimaryAddressRUTCoApplicantSeeder>();
        services.AddScoped<ISeeder, ProductCategoriesSeeder>();
        services.AddScoped<ISeeder, TestSubscriptionSeeder>();
        services.AddScoped<ISeeder, UserMergeSeeder>();

        return services;
    }

    /// <summary>
    /// Register all seeding services with full configuration
    /// </summary>
    public static IServiceCollection AddCompleteDatabaseSeeding(this IServiceCollection services, IConfiguration configuration)
    {
        services.AddDatabaseSeeders();
        services.AddSeederDataSources(configuration);

        // Add optional seeders in development environment
        var environment = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT");
        if (environment == "Development")
        {
            services.AddOptionalSeeders();
        }

        return services;
    }
}