# Database Seeder Migration Guide

## Overview

This document provides a comprehensive guide for migrating database seeding logic from PHP Laravel to .NET EF Core. The migration involves converting the existing Laravel seeder classes and their logic to C# methods within the .NET application.

### Current State
- **Laravel Seeder**: `php-laraval-app/database/seeders/DatabaseSeeder.php`
- **Target .NET Method**: `Downstairs.Infrastructure.Persistence.DatabaseSeeder.SeedAsync`

### Laravel Seeder Structure
The Laravel `DatabaseSeeder` orchestrates the execution of **41 active individual seeder classes**:

```php
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
    FixedPricesSeeder::class,
    CustomerDiscountsSeeder::class,
    NotificationSeeder::class,
    FeedbackSeeder::class,
    OauthSeeder::class,
    ScheduleSeeder::class,
    ScheduleProductSeeder::class,
    ScheduleCleaningDeviationSeeder::class,
    TeamSeeder::class,
    OrderLaundrySeeder::class,
    CreditSeeder::class,
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
```

**Additional Seeder Files (53 total files found - including commented/special purpose):**
- **Active in DatabaseSeeder.php**: 41 seeders (listed above)
- **Commented out in DatabaseSeeder.php**: 
  - `SubscriptionSeeder::class` (commented)
  - `UserTestSeeder::class` (commented)
  - `SchedulePendingSeeder::class` (commented)
  - `ScheduleDoneSeeder::class` (commented)
  - `UnassignSubscriptionSeeder::class` (commented)
- **Special Purpose Seeders** (not called in main DatabaseSeeder):
  - `DatabaseMergeSeeder` (special merge scenario)
  - `OldDatabaseSeeder` (legacy data)
  - `SpecificScheduleSeeder` (targeted data)
  - `InitialCashierSeeder` (specific initialization)
  - `PrimaryAddressRUTCoApplicantSeeder` (specific business logic)
  - `ProductCategoriesSeeder` (possibly replaced by CategoriesSeeder)
  - `TestSubscriptionSeeder` (test data)
  - `UserMergeSeeder` (merge operations)

---

## Migration Steps

### Step 1: Extract Seeding Logic from Laravel

1. **Identify All Seeder Classes**
   - Review `DatabaseSeeder.php` for the complete list of seeders
   - **Verified Count**: **41 active seeders** in main DatabaseSeeder.php
   - **Total Files**: **53 seeder files** including commented and special purpose
   - **Additional Discovery**: Found special purpose seeders including:
     - `DatabaseMergeSeeder` (special merge scenario)
     - `OldDatabaseSeeder` (legacy data)
     - `SpecificScheduleSeeder` (targeted data)
     - `InitialCashierSeeder` (specific initialization)
     - `PrimaryAddressRUTCoApplicantSeeder` (business logic)
     - `ProductCategoriesSeeder` (possibly replaced by CategoriesSeeder)
     - `TestSubscriptionSeeder` (test data)
     - `UserMergeSeeder` (merge operations)
   - Document the execution order (critical for relationships)

2. **Analyze Individual Seeders**
   For each seeder class:
   ```php
   // Example: CountriesSeeder.php
   class CountriesSeeder extends Seeder
   {
       public function run()
       {
           if (app()->environment() === 'testing') {
               Country::create([...]);
           } else {
               Country::insert($bulkData);
           }
       }
   }
   ```

3. **Extract Data Sources**
   - Static data arrays (countries, cities, etc.)
   - Configuration-based data (`config('downstairs.globalSettings')`)
   - **External data files in PHP project**: `C:\Code\CoreChange\DownstairsRepo\php-laraval-app\storage\app\seeders\`
     - `cities.sql` - 84,605 lines of INSERT statements for cities table
     - `products.json` - 2,892 lines of product data with multi-language name/description
     - `stores.json` - 44 stores with address information
   - Factory-generated test data with complex relationships
   - Environment-specific logic
   - **Service dependencies** (OrderService, WorkHourService)
   - **Third-party integrations** (Spatie Permissions, OAuth tokens)

4. **Identify Complex Patterns**
   - **Database transactions** (`DB::transaction()`)
   - **Polymorphic relationships** (translations, categories)
   - **Role-permission hierarchies** (Spatie Permission system)
   - **Factory chains** with relationship building
   - **Raw SQL execution** for large datasets
   - **Service layer dependencies** for business logic

### Step 2: Map Laravel Models to .NET Entities

**CRITICAL**: The .NET entities are **database-first generated models** that exactly match the existing database schema. Property names and types must match the actual database columns.

| Laravel Model | .NET Entity | Notes |
|---------------|-------------|-------|
| `App\Models\Country` | `Downstairs.Infrastructure.Persistence.Models.Country` | Direct mapping with snake_case → PascalCase |
| `App\Models\GlobalSetting` | `Downstairs.Infrastructure.Persistence.Models.GlobalSetting` | Has Key, Value, Type properties |
| `App\Models\User` | `Downstairs.Infrastructure.Persistence.Models.User` | Complex entity with many relationships |
| `App\Models\Service` | `Downstairs.Infrastructure.Persistence.Models.Service` | Has Type, MembershipType, Price, VatGroup, HasRut |
| `App\Models\Product` | `Downstairs.Infrastructure.Persistence.Models.Product` | Has FortnoxArticleId, Unit, Price, CreditPrice, VatGroup, HasRut, Color |
| `App\Models\BlockDay` | `Downstairs.Infrastructure.Persistence.Models.BlockDay` | Uses BlockDate (DateOnly), StartBlockTime, EndBlockTime (TimeOnly) |
| `App\Models\KeyPlace` | `Downstairs.Infrastructure.Persistence.Models.KeyPlace` | Only has Id, PropertyId, timestamps - no Name/Description |
| `App\Models\PropertyType` | `Downstairs.Infrastructure.Persistence.Models.PropertyType` | Only has Id, timestamps - no Name/Description |
| `App\Models\ServiceQuarter` | `Downstairs.Infrastructure.Persistence.Models.ServiceQuarter` | Has ServiceId, MinSquareMeters, MaxSquareMeters, Quarters |
| `App\Models\Category` | `Downstairs.Infrastructure.Persistence.Models.Category` | Only has Id, ThumbnailImage, timestamps - no Name/Description |
| `App\Models\FixedPrice` | `Downstairs.Infrastructure.Persistence.Models.FixedPrice` | Complex pricing entity with UserId, Type, date ranges, IsPerOrder |

**Property Mapping Example:**
```php
// Laravel
Country::create([
    'name' => 'Sweden',
    'code' => 'SE',
    'currency' => 'SEK',
    'dial_code' => '46',
    'flag' => '🇸🇪',
]);
```

```csharp
// .NET - Must match actual database schema
new Country
{
    Name = "Sweden",
    Code = "SE", 
    Currency = "SEK",
    DialCode = "46",
    Flag = "🇸🇪",
    CreatedAt = DateTime.UtcNow,
    UpdatedAt = DateTime.UtcNow
}
```

**Entity Schema Analysis Required:**
Before implementing any seeder, you must examine the actual .NET entity properties:
- Check `Models/EntityName.cs` for exact property names and types
- Note nullable properties (`string?` vs `string`)
- Verify date/time types (`DateTime?`, `DateOnly`, `TimeOnly`)
- Understand foreign key relationships (`long ServiceId`, virtual collections)
- Some entities may be minimal (just Id + timestamps) if they rely on separate translation/metadata tables

### Step 3: Implement .NET Seeding Logic

### Step 3: Implement .NET Seeding Logic

#### Database-First Entity Considerations

**IMPORTANT**: All entities are auto-generated from the existing database schema. Before implementing any seeder:

1. **Examine Entity Properties**: Use `read_file` to check the actual entity structure
2. **Match Database Schema**: Properties must exactly match database columns
3. **Respect Nullable Types**: Follow `string?` vs `string` patterns from the entities
4. **Use Correct Data Types**: Note `DateOnly`, `TimeOnly`, `decimal`, `ushort`, `byte` usage
5. **Understand Relationships**: Check virtual properties and foreign key fields

#### Entity Property Patterns Found:

```csharp
// Minimal entities (just structure, no content fields)
public partial class KeyPlace
{
    public long Id { get; set; }
    public long? PropertyId { get; set; }
    public DateTime? CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
    public DateTime? DeletedAt { get; set; }
    public virtual Property? Property { get; set; }
}

// Business entities with specific fields
public partial class Service  
{
    public long Id { get; set; }
    public string? FortnoxArticleId { get; set; }
    public string Type { get; set; } = null!;
    public string MembershipType { get; set; } = null!;
    public decimal Price { get; set; }
    public byte VatGroup { get; set; }
    public bool HasRut { get; set; }
    public string? ThumbnailImage { get; set; }
    public DateTime? CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
    public DateTime? DeletedAt { get; set; }
    // Virtual collections...
}

// Date-specific entities
public partial class BlockDay
{
    public long Id { get; set; }
    public DateOnly BlockDate { get; set; }  // Note: DateOnly, not DateTime
    public TimeOnly? StartBlockTime { get; set; }  // Note: TimeOnly
    public TimeOnly? EndBlockTime { get; set; }
    public DateTime? CreatedAt { get; set; }
    public DateTime? UpdatedAt { get; set; }
}
```

#### Updated DatabaseSeeder.SeedAsync Method

Replace the current placeholder implementation:

```csharp
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Hosting;
using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence;

public static class DatabaseSeeder
{
    public static async Task SeedAsync(IServiceProvider serviceProvider)
    {
        using var scope = serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();
        var logger = scope.ServiceProvider.GetRequiredService<ILogger<DownstairsDbContext>>();
        var configuration = scope.ServiceProvider.GetRequiredService<IConfiguration>();
        var environment = scope.ServiceProvider.GetRequiredService<IHostEnvironment>();

        try
        {
            logger.LogInformation("Starting comprehensive database seeding from Laravel DatabaseSeeder.php (41 active seeders)...");

            // Execute seeders in dependency order matching Laravel DatabaseSeeder.php (41 active)
            await SeedPermissionsAsync(context, configuration, logger);
            await SeedRolesAsync(context, logger);
            await SeedCountriesAsync(context, environment, logger);
            await SeedCitiesAsync(context, environment, logger);
            await SeedKeyPlacesAsync(context, logger);
            await SeedPropertyTypesAsync(context, logger);
            await SeedGlobalSettingsAsync(context, configuration, logger);
            await SeedDefaultServicesAsync(context, configuration, logger);
            await SeedDefaultServiceQuartersAsync(context, logger);
            await SeedDefaultProductsAsync(context, logger);
            await SeedUsersAsync(context, environment, logger);
            await SeedUserCompaniesAsync(context, logger);
            await SeedBlockDaysAsync(context, logger);
            await SeedServicesAsync(context, logger);
            await SeedProductsAsync(context, logger);
            await SeedCategoriesAsync(context, logger);
            await SeedStoresAsync(context, logger);
            await SeedAddFortnoxArticleIdAsync(context, logger);
            await SeedFixedPricesAsync(context, logger);
            await SeedCustomerDiscountsAsync(context, logger);
            await SeedNotificationsAsync(context, logger);
            await SeedFeedbackAsync(context, logger);
            await SeedOauthAsync(context, configuration, logger);
            await SeedSchedulesAsync(context, serviceProvider, logger);
            await SeedScheduleProductsAsync(context, logger);
            await SeedScheduleCleaningDeviationsAsync(context, logger);
            await SeedTeamsAsync(context, logger);
            await SeedOrderLaundryAsync(context, logger);
            await SeedCreditsAsync(context, logger);
            await SeedWorkersWithoutSchedulesAsync(context, logger);
            await SeedLeaveRegistrationsAsync(context, logger);
            await SeedScheduleChangeRequestsAsync(context, logger);
            await SeedWorkHoursAsync(context, logger);
            await SeedTimeAdjustmentsAsync(context, logger);
            await SeedPriceAdjustmentsAsync(context, logger);
            await SeedStoreAsync(context, logger);
            await SeedStoreProductsAsync(context, logger);
            await SeedLaundryOrdersAsync(context, logger);
            await SeedLaundryOrderProductsAsync(context, logger);
            await SeedLaundryOrderHistoryAsync(context, logger);
            await SeedStoreSalesAsync(context, logger);
            
            await context.SaveChangesAsync();
            logger.LogInformation("Database seeding completed successfully - 41 active seeders executed.");
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "Error occurred during database seeding");
            throw;
        }
    }

    // Example implementation respecting actual entity schema
    private static async Task SeedCountriesAsync(DownstairsDbContext context, IHostEnvironment environment, ILogger logger)
    {
        if (await context.Countries.AnyAsync())
        {
            logger.LogInformation("Countries already seeded, skipping...");
            return;
        }

        logger.LogInformation("Seeding countries...");

        if (environment.EnvironmentName == "Testing")
        {
            // Minimal test data - match actual Country entity properties
            var testCountry = new Country
            {
                Id = 217,  // May need IDENTITY_INSERT handling
                Code = "SE",
                Name = "Sweden", 
                Currency = "SEK",
                DialCode = "46",
                Flag = "🇸🇪",
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            };
            await context.Countries.AddAsync(testCountry);
        }
        else
        {
            var countries = GetAllCountriesData(); // Returns List<Country> with actual properties
            await context.Countries.AddRangeAsync(countries);
        }
    }

    private static async Task SeedBlockDaysAsync(DownstairsDbContext context, ILogger logger)
    {
        if (await context.BlockDays.AnyAsync())
        {
            logger.LogInformation("Block days already seeded, skipping...");
            return;
        }

        logger.LogInformation("Seeding block days...");

        // Must use DateOnly and TimeOnly as per entity definition
        var blockDays = new List<BlockDay>
        {
            new()
            {
                BlockDate = new DateOnly(2024, 12, 25),  // Christmas Day
                StartBlockTime = new TimeOnly(0, 0),     // Full day block
                EndBlockTime = new TimeOnly(23, 59),
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            },
            new()
            {
                BlockDate = new DateOnly(2025, 1, 1),    // New Year's Day  
                StartBlockTime = new TimeOnly(0, 0),
                EndBlockTime = new TimeOnly(23, 59),
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            }
        };

        await context.BlockDays.AddRangeAsync(blockDays);
        await context.SaveChangesAsync();
        
        logger.LogInformation($"Seeded {blockDays.Count} block days");
    }

    private static async Task SeedServicesAsync(DownstairsDbContext context, ILogger logger)
    {
        if (await context.Services.AnyAsync())
        {
            logger.LogInformation("Services already seeded, skipping...");
            return;
        }

        logger.LogInformation("Seeding services...");

        // Must provide all required properties as per Service entity
        var services = new List<Service>
        {
            new()
            {
                FortnoxArticleId = null,  // Optional
                Type = "cleaning",        // Required string
                MembershipType = "standard", // Required string  
                Price = 500.00m,         // Required decimal
                VatGroup = 25,           // Required byte (0-255)
                HasRut = true,           // Required bool
                ThumbnailImage = null,   // Optional
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            },
            new()
            {
                FortnoxArticleId = null,
                Type = "laundry", 
                MembershipType = "premium",
                Price = 200.00m,
                VatGroup = 25,
                HasRut = false,
                ThumbnailImage = null,
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow
            }
        };

        await context.Services.AddRangeAsync(services);
        await context.SaveChangesAsync();
        
        logger.LogInformation($"Seeded {services.Count} services");
    }

    // Minimal entity seeders (entities with just Id + timestamps)
    private static async Task SeedKeyPlacesAsync(DownstairsDbContext context, ILogger logger)
    {
        if (await context.KeyPlaces.AnyAsync())
        {
            logger.LogInformation("Key places already seeded, skipping...");
            return;
        }

        logger.LogInformation("Seeding key places...");

        // KeyPlace entity only has Id, PropertyId, timestamps - no Name/Description
        var keyPlaces = new List<KeyPlace>
        {
            new() { PropertyId = null, CreatedAt = DateTime.UtcNow, UpdatedAt = DateTime.UtcNow },
            new() { PropertyId = null, CreatedAt = DateTime.UtcNow, UpdatedAt = DateTime.UtcNow },
            new() { PropertyId = null, CreatedAt = DateTime.UtcNow, UpdatedAt = DateTime.UtcNow }
        };

        await context.KeyPlaces.AddRangeAsync(keyPlaces);
        await context.SaveChangesAsync();
        
        logger.LogInformation($"Seeded {keyPlaces.Count} key places");
    }

    // Placeholder methods for remaining 40 seeders from Laravel DatabaseSeeder.php
    private static async Task SeedUserCompaniesAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("UserCompaniesSeeder - Check Laravel seeder for implementation details");
        // TODO: Examine UserCompany entity properties and implement accordingly
        await Task.CompletedTask;
    }

    // ... Continue for all 41 active seeders from Laravel DatabaseSeeder.php

    #region Optional/Commented Seeder Methods (may be needed for specific scenarios)
    
    // These seeders are commented out in Laravel DatabaseSeeder.php but may be needed
    private static async Task SeedSubscriptionsAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("SubscriptionSeeder - Currently commented in Laravel, implement if needed");
        // TODO: Check if subscription seeding is needed for .NET implementation
        await Task.CompletedTask;
    }

    private static async Task SeedUserTestAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("UserTestSeeder - Currently commented in Laravel, for test environments");
        // TODO: Implement for test data generation if needed
        await Task.CompletedTask;
    }

    private static async Task SeedSchedulePendingAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("SchedulePendingSeeder - Currently commented in Laravel");
        // TODO: Check if pending schedule seeding is needed
        await Task.CompletedTask;
    }

    private static async Task SeedScheduleDoneAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("ScheduleDoneSeeder - Currently commented in Laravel");
        // TODO: Check if done schedule seeding is needed
        await Task.CompletedTask;
    }

    private static async Task SeedUnassignSubscriptionsAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("UnassignSubscriptionSeeder - Currently commented in Laravel");
        // TODO: Check if unassigned subscription handling is needed
        await Task.CompletedTask;
    }

    #endregion

    #region Special Purpose Seeder Methods (for specific scenarios)

    // These are special purpose seeders not called in main DatabaseSeeder but may be useful
    private static async Task SeedDatabaseMergeAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("DatabaseMergeSeeder - Special merge scenario seeder");
        // TODO: Implement if database migration/merge is needed
        await Task.CompletedTask;
    }

    private static async Task SeedOldDatabaseAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("OldDatabaseSeeder - Legacy data seeder");
        // TODO: Implement if legacy data import is needed
        await Task.CompletedTask;
    }

    private static async Task SeedSpecificScheduleAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("SpecificScheduleSeeder - Targeted schedule seeder");
        // TODO: Implement for specific schedule scenarios
        await Task.CompletedTask;
    }

    private static async Task SeedInitialCashierAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("InitialCashierSeeder - Cashier initialization seeder");
        // TODO: Implement if cashier setup is needed
        await Task.CompletedTask;
    }

    private static async Task SeedPrimaryAddressRUTCoApplicantAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("PrimaryAddressRUTCoApplicantSeeder - RUT co-applicant seeder");
        // TODO: Implement for Swedish RUT tax system if needed
        await Task.CompletedTask;
    }

    private static async Task SeedProductCategoriesAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("ProductCategoriesSeeder - May be replaced by CategoriesSeeder");
        // TODO: Check if this is duplicate of CategoriesSeeder or separate functionality
        await Task.CompletedTask;
    }

    private static async Task SeedTestSubscriptionsAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("TestSubscriptionSeeder - Test subscription data");
        // TODO: Implement for test environments
        await Task.CompletedTask;
    }

    private static async Task SeedUserMergeAsync(DownstairsDbContext context, ILogger logger)
    {
        logger.LogInformation("UserMergeSeeder - User merge operations");
        // TODO: Implement if user data merging is needed
        await Task.CompletedTask;
    }

    #endregion
}
```

---

## Code Conversion Examples

### Simple Entity Creation

**Laravel:**
```php
Country::create([
    'name' => 'Sweden',
    'code' => 'SE',
    'currency' => 'SEK',
    'dial_code' => '46',
    'flag' => '🇸🇪',
]);
```

**C# Equivalent:**
```csharp
var country = new Country
{
    Name = "Sweden",
    Code = "SE",
    Currency = "SEK",
    DialCode = "46",
    Flag = "🇸🇪"
};
await context.Countries.AddAsync(country);
```

### Bulk Insert

**Laravel:**
```php
Country::insert($bulkCountriesArray);
```

**C# Equivalent:**
```csharp
await context.Countries.AddRangeAsync(countriesCollection);
await context.SaveChangesAsync();
```

### Factory Usage with Relationships

**Laravel:**
```php
User::factory()->count(3)->hasInfo(1)->create()->each(function (User $user) {
    $user->assignRole('Employee', 'Worker');
    $addresses = Address::factory()->count(1)->create();
    Employee::factory()->count(1)->forUser($user, $addresses[0]->id)->create();
});
```

**C# Equivalent:**
```csharp
// Create users with related data
for (int i = 0; i < 3; i++)
{
    var user = CreateTestUser($"worker{i}@email.com");
    await context.Users.AddAsync(user);
    await context.SaveChangesAsync(); // Get ID

    // Assign roles
    await AssignUserRolesAsync(context, user.Id, new[] { "Employee", "Worker" });
    
    // Create related address and employee
    var address = CreateTestAddress();
    await context.Addresses.AddAsync(address);
    await context.SaveChangesAsync();

    var employee = new Employee
    {
        UserId = user.Id,
        AddressId = address.Id,
        // Other properties
    };
    await context.Employees.AddAsync(employee);
}
```

### Database Transactions

**Laravel:**
```php
DB::transaction(function () use ($product, $storeIds, $nameData) {
    $product = Product::create($product);
    $product->translations()->createMany([
        to_translation('name', $nameData),
        to_translation('description', $descriptionData),
    ]);
    $product->categories()->syncWithoutDetaching($categoryId);
    $product->stores()->syncWithoutDetaching($storeIds);
});
```

**C# Equivalent:**
```csharp
using var transaction = await context.Database.BeginTransactionAsync();
try
{
    var product = new Product { /* properties */ };
    await context.Products.AddAsync(product);
    await context.SaveChangesAsync(); // Get ID

    // Add translations
    var translations = new[]
    {
        new Translation { TranslatableId = product.Id, Key = "name", /* other props */ },
        new Translation { TranslatableId = product.Id, Key = "description", /* other props */ }
    };
    await context.Translations.AddRangeAsync(translations);

    // Handle many-to-many relationships
    await SyncProductCategoriesAsync(context, product.Id, categoryIds);
    await SyncProductStoresAsync(context, product.Id, storeIds);

    await context.SaveChangesAsync();
    await transaction.CommitAsync();
}
catch
{
    await transaction.RollbackAsync();
    throw;
}
```

### Configuration-Based Seeding

**Laravel:**
```php
foreach (config('downstairs.globalSettings') as $value) {
    $setting = GlobalSetting::create([
        'key' => strtoupper(Str::snake($value['key'])),
        'value' => $value['value'],
        'type' => $value['type'],
    ]);
    $setting->translations()->create([
        'key' => 'description',
        ...$value['description'],
    ]);
}
```

**C# Equivalent:**
```csharp
var globalSettingsConfig = configuration.GetSection("DownstairsSettings:GlobalSettings")
    .Get<List<GlobalSettingConfig>>();

foreach (var configSetting in globalSettingsConfig)
{
    var setting = new GlobalSetting
    {
        Key = configSetting.Key.ToUpperInvariant().Replace(" ", "_"),
        Value = configSetting.Value,
        Type = configSetting.Type
    };

    await context.GlobalSettings.AddAsync(setting);
    await context.SaveChangesAsync(); // Get ID

    // Add translations
    foreach (var description in configSetting.Description)
    {
        await context.Translations.AddAsync(new Translation
        {
            TranslatableId = setting.Id,
            TranslatableType = nameof(GlobalSetting),
            Key = "description",
            Locale = description.Key,
            Value = description.Value
        });
    }
}
```

### External SQL File Processing

**Laravel:**
```php
$sqlFile = storage_path('app/seeders/cities.sql');
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    DB::unprepared($sql);
}
```

**C# Equivalent:**
```csharp
var sqlFilePath = Path.Combine("Data", "Seeders", "cities.sql");
if (File.Exists(sqlFilePath))
{
    var sql = await File.ReadAllTextAsync(sqlFilePath);
    await context.Database.ExecuteSqlRawAsync(sql);
}
```

### Service Dependency Injection

**Laravel:**
```php
public function run(OrderService $orderService): void
{
    $orderService->createScheduleOrder($subscription);
}
```

**C# Equivalent:**
```csharp
private static async Task SeedSchedulesAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
{
    var orderService = serviceProvider.GetRequiredService<IOrderService>();
    await orderService.CreateScheduleOrderAsync(subscription);
}
```

### Permission Cache Management

**Laravel:**
```php
app()[PermissionRegistrar::class]->forgetCachedPermissions();
Permission::insert($permissions);
```

**C# Equivalent:**
```csharp
// Note: .NET permission systems may not have the same caching mechanism
// This depends on your permission system implementation
var permissionService = serviceProvider.GetRequiredService<IPermissionService>();
await permissionService.ClearCacheAsync();
await context.Permissions.AddRangeAsync(permissions);
```

### Environment-Specific Logic

**Laravel:**
```php
if (app()->environment() === 'testing') {
    // Test data
} else {
    // Production data
}
```

**C# Equivalent:**
```csharp
var environment = serviceProvider.GetRequiredService<IHostEnvironment>();

if (environment.EnvironmentName == "Testing")
{
    // Test data
}
else if (environment.IsDevelopment())
{
    // Development data
}
else if (environment.IsProduction())
{
    // Production data
}
```

### Complex Entity Creation with Business Logic

**Laravel:**
```php
$schedules = ScheduleEmployee::where('status', ScheduleEmployeeStatusEnum::Done())->get();
foreach ($schedules as $schedule) {
    [$startTime, $endTime] = WorkHourService::getTimes($schedule, $date);
    WorkHour::create([
        'user_id' => $schedule->user_id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);
}
```

**C# Equivalent:**
```csharp
var schedules = await context.ScheduleEmployees
    .Where(s => s.Status == ScheduleEmployeeStatus.Done)
    .ToListAsync();

var workHourService = serviceProvider.GetRequiredService<IWorkHourService>();

foreach (var schedule in schedules)
{
    var (startTime, endTime) = await workHourService.GetTimesAsync(schedule, date);
    
    var workHour = new WorkHour
    {
        UserId = schedule.UserId,
        StartTime = startTime,
        EndTime = endTime,
        Date = date
    };
    
    await context.WorkHours.AddAsync(workHour);
}
```

---

## Registration and Execution

### Option 1: Application Startup

Add to `Program.cs` or startup configuration:

```csharp
// In Program.cs
if (app.Environment.IsDevelopment())
{
    using var scope = app.Services.CreateScope();
    await DatabaseSeeder.SeedAsync(scope.ServiceProvider);
}
```

### Option 2: CLI Command Integration

Create a custom EF Core command:

```csharp
// Custom CLI command
public class SeedCommand : ICommand
{
    public async Task<int> ExecuteAsync(IServiceProvider serviceProvider)
    {
        await DatabaseSeeder.SeedAsync(serviceProvider);
        return 0;
    }
}
```

### Option 3: Migration Integration

```csharp
// In a migration's Up method (not recommended for large datasets)
protected override void Up(MigrationBuilder migrationBuilder)
{
    // Schema changes first
    migrationBuilder.CreateTable(...);
    
    // Then seed critical data
    migrationBuilder.InsertData(
        table: "Countries",
        columns: new[] { "Name", "Code", "Currency", "DialCode", "Flag" },
        values: new object[] { "Sweden", "SE", "SEK", "46", "🇸🇪" }
    );
}
```

---

## Important Notes and Pitfalls

### 1. Identity Inserts

**Issue:** Laravel uses auto-incrementing IDs, but some seeders may specify explicit IDs.

**Solution:**
```csharp
// For explicit ID assignment
context.Database.ExecuteSqlRaw("SET IDENTITY_INSERT Countries ON");
await context.Countries.AddRangeAsync(countriesWithExplicitIds);
await context.SaveChangesAsync();
context.Database.ExecuteSqlRaw("SET IDENTITY_INSERT Countries OFF");
```

### 2. Foreign Key Relationships

**Issue:** Seeding order matters for related entities.

**Solution:**
```csharp
// Seed parent entities first
await SeedCountriesAsync(context);
await context.SaveChangesAsync(); // Ensure IDs are generated

// Then seed child entities
await SeedCitiesAsync(context); // Cities reference Countries
```

### 3. Large Dataset Performance

**Issue:** Laravel uses `insert()` for bulk operations, which is faster than individual `create()` calls.

**Solution:**
```csharp
// Use AddRangeAsync for bulk operations
await context.Countries.AddRangeAsync(largeCountriesList);

// Consider batching for very large datasets
const int batchSize = 1000;
for (int i = 0; i < countries.Count; i += batchSize)
{
    var batch = countries.Skip(i).Take(batchSize);
    await context.Countries.AddRangeAsync(batch);
    await context.SaveChangesAsync();
}
```

### 4. Configuration Data Migration

**Issue:** Laravel uses `config('downstairs.globalSettings')` which needs .NET equivalent.

**Solution:**
```csharp
// Create configuration section in appsettings.json
{
  "DownstairsSettings": {
    "GlobalSettings": [
      {
        "Key": "COMPANY_NAME",
        "Value": "Downstairs",
        "Type": "string",
        "Description": { "en": "Company name", "sv": "Företagsnamn" }
      }
    ]
  }
}

// Access in seeder
var configuration = serviceProvider.GetRequiredService<IConfiguration>();
var globalSettings = configuration.GetSection("DownstairsSettings:GlobalSettings")
    .Get<List<GlobalSettingConfig>>();
```

### 5. Testing Considerations

**Issue:** Different data sets for testing vs production.

**Solution:**
```csharp
private static async Task SeedCountriesAsync(DownstairsDbContext context, IHostEnvironment environment)
{
    if (environment.EnvironmentName == "Testing")
    {
        // Minimal test data
        await context.Countries.AddAsync(new Country 
        { 
            Id = 217, 
            Name = "Sweden", 
            Code = "SE", 
            Currency = "SEK", 
            DialCode = "46", 
            Flag = "🇸🇪" 
        });
    }
    else
    {
        // Full production data
        await context.Countries.AddRangeAsync(GetAllCountries());
    }
}
```

### 6. Translation Handling

**Issue:** Laravel has polymorphic translations that need mapping.

**Solution:**
```csharp
// If GlobalSetting has translations
var setting = new GlobalSetting
{
    Key = "COMPANY_NAME",
    Value = "Downstairs",
    Type = "string"
};

// Add translations if translation table exists
var translations = new List<Translation>
{
    new() { Key = "description", Locale = "en", Value = "Company name" },
    new() { Key = "description", Locale = "sv", Value = "Företagsnamn" }
};

await context.GlobalSettings.AddAsync(setting);
await context.SaveChangesAsync(); // Get the ID

// Associate translations
foreach (var translation in translations)
{
    translation.TranslatableId = setting.Id;
    translation.TranslatableType = nameof(GlobalSetting);
}
await context.Translations.AddRangeAsync(translations);
```

---

## Migration Checklist

- [ ] **Inventory all Laravel seeders** (41 active seeders verified in DatabaseSeeder.php + 12 additional special purpose seeders)
- [ ] **Examine each .NET entity structure** (use read_file to check actual properties before implementing)
- [ ] **Map Laravel models to .NET entities** (verify property names and types match database schema)
- [ ] **Handle minimal entities** (KeyPlace, PropertyType, Category have no Name/Description fields)
- [ ] **Implement date/time entities correctly** (BlockDay uses DateOnly/TimeOnly, not DateTime)
- [ ] **Extract static data** (countries, cities, settings, etc.)
- [ ] **Convert configuration-based data** (globalSettings, products, categories configs)  
- [ ] **Handle external SQL files** (cities.sql with 84K+ records)
- [ ] **Convert factory patterns** (User::factory()->hasInfo(1)->create() equivalents)
- [ ] **Implement service dependencies** (OrderService, WorkHourService injection)
- [ ] **Handle database transactions** (DB::transaction() → EF transactions)
- [ ] **Migrate permission system** (Spatie Permission → .NET equivalent)
- [ ] **Convert polymorphic relationships** (translations, categories)
- [ ] **Implement all 41 active seeding methods** (one per Laravel seeder class in DatabaseSeeder.php)
- [ ] **Consider special purpose seeders** (DatabaseMergeSeeder, OldDatabaseSeeder if needed)
- [ ] **Handle entity relationships** (ensure proper seeding order, foreign keys)
- [ ] **Add environment logic** (testing vs production data)
- [ ] **Respect entity constraints** (required vs optional fields, string vs string?)
- [ ] **Use correct data types** (decimal for prices, byte for VAT, DateOnly/TimeOnly)
- [ ] **Performance optimization** (bulk inserts, batching, change tracker clearing)
- [ ] **Memory management** (large dataset handling)
- [ ] **Error handling** (logging, rollback on failure)
- [ ] **OAuth token seeding** (Fortnox integration tokens)
- [ ] **Complex business logic** (schedules with status changes)
- [ ] **Integration testing** (verify seeded data integrity)
- [ ] **Documentation** (update deployment procedures)

## Critical Implementation Notes

### 1. **Always Examine Entity First**
Before implementing any seeder method:
```csharp
// Always check the actual entity structure:
var entityFile = "Models/EntityName.cs";
// Use read_file tool to examine properties, types, nullability
```

### 2. **Complete List of Required Seeder Methods (41 Active)**

The following methods must be implemented to match the 41 active seeders in Laravel DatabaseSeeder.php:

```csharp
// Core Foundation Seeders (must implement first due to dependencies)
private static async Task SeedPermissionsAsync(DownstairsDbContext context, IConfiguration configuration, ILogger logger)
private static async Task SeedRolesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedCountriesAsync(DownstairsDbContext context, IHostEnvironment environment, ILogger logger)
private static async Task SeedCitiesAsync(DownstairsDbContext context, IHostEnvironment environment, ILogger logger)
private static async Task SeedKeyPlacesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedPropertyTypesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedGlobalSettingsAsync(DownstairsDbContext context, IConfiguration configuration, ILogger logger)

// Service and Product Seeders
private static async Task SeedDefaultServicesAsync(DownstairsDbContext context, IConfiguration configuration, ILogger logger)
private static async Task SeedDefaultServiceQuartersAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedDefaultProductsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedServicesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedProductsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedCategoriesAsync(DownstairsDbContext context, ILogger logger)

// User and Company Seeders
private static async Task SeedUsersAsync(DownstairsDbContext context, IHostEnvironment environment, ILogger logger)
private static async Task SeedUserCompaniesAsync(DownstairsDbContext context, ILogger logger)

// Business Logic Seeders
private static async Task SeedBlockDaysAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedStoresAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedAddFortnoxArticleIdAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedFixedPricesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedCustomerDiscountsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedNotificationsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedFeedbackAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedOauthAsync(DownstairsDbContext context, IConfiguration configuration, ILogger logger)

// Schedule and Workflow Seeders
private static async Task SeedSchedulesAsync(DownstairsDbContext context, IServiceProvider serviceProvider, ILogger logger)
private static async Task SeedScheduleProductsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedScheduleCleaningDeviationsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedTeamsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedOrderLaundryAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedCreditsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedWorkersWithoutSchedulesAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedLeaveRegistrationsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedScheduleChangeRequestsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedWorkHoursAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedTimeAdjustmentsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedPriceAdjustmentsAsync(DownstairsDbContext context, ILogger logger)

// Store and Sales Seeders
private static async Task SeedStoreAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedStoreProductsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedLaundryOrdersAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedLaundryOrderProductsAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedLaundryOrderHistoryAsync(DownstairsDbContext context, ILogger logger)
private static async Task SeedStoreSalesAsync(DownstairsDbContext context, ILogger logger)
```

### 3. **Entity Categories Discovered**

**Full Content Entities**: Country, GlobalSetting, User, Service, Product, FixedPrice
- Have business fields like Name, Description, Price, etc.
- Need meaningful data seeding

**Structural Entities**: KeyPlace, PropertyType, Category, ServiceQuarter  
- Have minimal fields (often just Id, relationships, timestamps)
- May only need placeholder/count seeding
- Content may be in separate translation/metadata tables

**Date/Time Entities**: BlockDay, Schedule
- Use specific .NET date types (DateOnly, TimeOnly)
- Need careful date formatting

### 4. **Required vs Optional Properties**
```csharp
// Check nullability in entity definitions:
public string Type { get; set; } = null!;  // Required
public string? FortnoxArticleId { get; set; }  // Optional
```

### 5. **Data Type Mapping**
```csharp
// Laravel → .NET type mappings discovered:
decimal Price { get; set; }     // money fields
byte VatGroup { get; set; }     // small integers (0-255)  
ushort CreditPrice { get; set; } // small positive integers
DateOnly BlockDate { get; set; } // dates without time
TimeOnly StartTime { get; set; } // time without date
```

### 6. **Dependency Order**
Some entities require others to exist first:
```csharp
// Countries must exist before Cities
await SeedCountriesAsync(context, environment, logger);
await SeedCitiesAsync(context, environment, logger);

// Services must exist before ServiceQuarters  
await SeedDefaultServicesAsync(context, configuration, logger);
await SeedDefaultServiceQuartersAsync(context, logger);
```

## Additional Scenarios Discovered

### 1. **Merge Seeders** (`DatabaseMergeSeeder`)
- Used for migrating from old database systems
- Requires careful data transformation
- May need special handling for legacy data formats

### 2. **Large SQL File Processing** 
- `cities.sql` contains 84,000+ records
- Consider memory-efficient streaming approaches
- May require chunked processing

### 3. **Complex Factory Relationships**
- Users with multiple related entities (addresses, employees, customers)
- Chained factory calls with relationship building
- Need equivalent test data generation in .NET

### 4. **Service Integration in Seeders**
- Business logic services injected into seeders
- Complex calculations (work hours, schedules)
- Service dependency management required

### 5. **Third-Party System Integration**
- Spatie Permission system with cache management
- OAuth token management for external APIs
- Need equivalent .NET implementations

### 6. **Environment-Specific SQL Execution**
- Raw SQL files for production data
- In-memory test data for testing
- Different data volumes per environment

---

## Next Steps

1. **Start with Core Entities**: Begin with foundational data (Countries, Cities, Permissions)
2. **Validate Data Integrity**: Ensure foreign key relationships are maintained
3. **Performance Testing**: Measure seeding time with large datasets
4. **Environment Testing**: Verify different data sets for different environments
5. **Deployment Integration**: Update CI/CD pipelines to include seeding steps

---

## Implementation Summary

### Required Implementation (Priority 1 - Active Seeders)
**41 Active Seeder Methods** from Laravel DatabaseSeeder.php - these MUST be implemented:

1. **Foundation (7 methods)**: Permissions, Roles, Countries, Cities, KeyPlaces, PropertyTypes, GlobalSettings
2. **Services & Products (7 methods)**: DefaultServices, DefaultServiceQuarters, DefaultProducts, Services, Products, Categories, Stores
3. **Users & Companies (2 methods)**: Users, UserCompanies  
4. **Business Logic (8 methods)**: BlockDays, AddFortnoxArticleId, FixedPrices, CustomerDiscounts, Notifications, Feedback, OAuth, Teams
5. **Schedules & Workflow (9 methods)**: Schedules, ScheduleProducts, ScheduleCleaningDeviations, OrderLaundry, Credits, WorkersWithoutSchedules, LeaveRegistrations, ScheduleChangeRequests, WorkHours, TimeAdjustments, PriceAdjustments
6. **Sales & Orders (8 methods)**: Store, StoreProducts, LaundryOrders, LaundryOrderProducts, LaundryOrderHistory, StoreSales

### Optional Implementation (Priority 2 - Commented/Special Purpose)
**12 Additional Seeder Methods** for specific scenarios:

- **Commented in Laravel (5 methods)**: Subscriptions, UserTest, SchedulePending, ScheduleDone, UnassignSubscriptions
- **Special Purpose (8 methods)**: DatabaseMerge, OldDatabase, SpecificSchedule, InitialCashier, PrimaryAddressRUTCoApplicant, ProductCategories, TestSubscriptions, UserMerge

### Data Sources Available
- **SQL Files**: `cities.sql` (84K+ records)
- **JSON Files**: `countries.json`, `global_settings.json`  
- **Laravel Configuration**: `config/downstairs.php` settings
- **Factory Classes**: For test data generation patterns

### Next Steps
1. Start with Foundation seeders (Countries, Cities, Permissions, Roles)
2. Implement entity examination workflow for each seeder
3. Use SQL/JSON files where available
4. Create placeholder methods for all 41 active seeders first
5. Implement Priority 2 seeders only if specific scenarios require them

---

## Best Practices for Refactored Implementation

### 1. **Modular Architecture Design**

Instead of implementing all seeders in a single large `DatabaseSeeder` class, consider a modular approach:

```csharp
// Core interface for all seeders
public interface ISeeder
{
    Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider);
    int Order { get; } // For dependency ordering
    string Name { get; }
}

// Base class with common functionality
public abstract class BaseSeeder : ISeeder
{
    protected ILogger Logger { get; }
    protected IConfiguration Configuration { get; }
    protected IHostEnvironment Environment { get; }
    
    public abstract int Order { get; }
    public abstract string Name { get; }
    
    protected BaseSeeder(IServiceProvider serviceProvider)
    {
        Logger = serviceProvider.GetRequiredService<ILogger<BaseSeeder>>();
        Configuration = serviceProvider.GetRequiredService<IConfiguration>();
        Environment = serviceProvider.GetRequiredService<IHostEnvironment>();
    }
    
    public abstract Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider);
    
    protected async Task<bool> ShouldSkipSeedingAsync<T>(DbSet<T> dbSet, string seederName) where T : class
    {
        if (await dbSet.AnyAsync())
        {
            Logger.LogInformation("{SeederName} already seeded, skipping...", seederName);
            return true;
        }
        return false;
    }
}

// Individual seeder implementations
public class CountriesSeeder : BaseSeeder
{
    public override int Order => 10;
    public override string Name => "Countries";
    
    public CountriesSeeder(IServiceProvider serviceProvider) : base(serviceProvider) { }
    
    public override async Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Countries, Name)) return;
        
        Logger.LogInformation("Seeding {SeederName}...", Name);
        
        if (Environment.EnvironmentName == "Testing")
        {
            await SeedTestCountriesAsync(context);
        }
        else
        {
            await SeedProductionCountriesAsync(context);
        }
        
        Logger.LogInformation("Completed seeding {SeederName}", Name);
    }
    
    private async Task SeedTestCountriesAsync(DownstairsDbContext context)
    {
        // Implementation for test data
    }
    
    private async Task SeedProductionCountriesAsync(DownstairsDbContext context)
    {
        // Implementation for production data
    }
}

// Orchestrator class
public class DatabaseSeederOrchestrator
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<DatabaseSeederOrchestrator> _logger;
    private readonly List<ISeeder> _seeders;
    
    public DatabaseSeederOrchestrator(IServiceProvider serviceProvider)
    {
        _serviceProvider = serviceProvider;
        _logger = serviceProvider.GetRequiredService<ILogger<DatabaseSeederOrchestrator>>();
        _seeders = DiscoverSeeders();
    }
    
    public async Task SeedAllAsync()
    {
        using var scope = _serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();
        
        // Execute seeders in dependency order
        var orderedSeeders = _seeders.OrderBy(s => s.Order).ToList();
        
        foreach (var seeder in orderedSeeders)
        {
            try
            {
                await seeder.SeedAsync(context, scope.ServiceProvider);
                await context.SaveChangesAsync();
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to execute seeder: {SeederName}", seeder.Name);
                throw;
            }
        }
    }
    
    private List<ISeeder> DiscoverSeeders()
    {
        // Auto-discover seeder implementations or register manually
        return new List<ISeeder>
        {
            new CountriesSeeder(_serviceProvider),
            new CitiesSeeder(_serviceProvider),
            // ... other seeders
        };
    }
}
```

### 2. **Configuration-Driven Approach**

Use configuration files to drive seeding behavior:

```json
// appsettings.json
{
  "DatabaseSeeding": {
    "EnabledSeeders": [
      "Countries",
      "Cities", 
      "Permissions",
      "Roles"
    ],
    "Environment": {
      "Testing": {
        "UseMinimalData": true,
        "SkipLargeDatasets": true
      },
      "Development": {
        "IncludeTestUsers": true,
        "GenerateSchedules": false
      },
      "Production": {
        "ValidateDataIntegrity": true,
        "UseExternalSqlFiles": true
      }
    },
    "DataSources": {
      "CitiesFilePath": "Data/Seeders/cities.sql",
      "CountriesFilePath": "Data/Seeders/countries.json",
      "GlobalSettingsFilePath": "Data/Seeders/global_settings.json"
    }
  }
}
```

### 3. **Data Source Abstraction**

Create abstracted data sources for flexibility:

```csharp
public interface ISeederDataSource<T>
{
    Task<IEnumerable<T>> GetDataAsync();
    Task<bool> IsAvailableAsync();
}

public class SqlFileDataSource<T> : ISeederDataSource<T>
{
    private readonly string _filePath;
    private readonly DownstairsDbContext _context;
    
    public SqlFileDataSource(string filePath, DownstairsDbContext context)
    {
        _filePath = filePath;
        _context = context;
    }
    
    public async Task<IEnumerable<T>> GetDataAsync()
    {
        if (!File.Exists(_filePath)) return Enumerable.Empty<T>();
        
        var sql = await File.ReadAllTextAsync(_filePath);
        await _context.Database.ExecuteSqlRawAsync(sql);
        
        // Return the seeded data for verification
        return await _context.Set<T>().ToListAsync();
    }
    
    public async Task<bool> IsAvailableAsync()
    {
        return File.Exists(_filePath);
    }
}

public class JsonFileDataSource<T> : ISeederDataSource<T>
{
    private readonly string _filePath;
    
    public JsonFileDataSource(string filePath)
    {
        _filePath = filePath;
    }
    
    public async Task<IEnumerable<T>> GetDataAsync()
    {
        if (!File.Exists(_filePath)) return Enumerable.Empty<T>();
        
        var json = await File.ReadAllTextAsync(_filePath);
        return JsonSerializer.Deserialize<T[]>(json, new JsonSerializerOptions
        {
            PropertyNamingPolicy = JsonNamingPolicy.CamelCase
        }) ?? Enumerable.Empty<T>();
    }
    
    public async Task<bool> IsAvailableAsync()
    {
        return File.Exists(_filePath);
    }
}

// Usage in seeder
public class CitiesSeeder : BaseSeeder
{
    public override async Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Cities, Name)) return;
        
        var sqlDataSource = new SqlFileDataSource<City>(
            Configuration["DatabaseSeeding:DataSources:CitiesFilePath"], 
            context);
            
        if (await sqlDataSource.IsAvailableAsync())
        {
            await sqlDataSource.GetDataAsync();
        }
        else
        {
            await SeedFallbackCitiesAsync(context);
        }
    }
}
```

### 4. **Performance Optimization Patterns**

```csharp
public class OptimizedBulkSeeder : BaseSeeder
{
    protected async Task BulkInsertAsync<T>(DownstairsDbContext context, IEnumerable<T> entities, int batchSize = 1000) where T : class
    {
        var entitiesList = entities.ToList();
        
        // Disable change tracking for bulk operations
        context.ChangeTracker.AutoDetectChangesEnabled = false;
        
        try
        {
            for (int i = 0; i < entitiesList.Count; i += batchSize)
            {
                var batch = entitiesList.Skip(i).Take(batchSize);
                await context.Set<T>().AddRangeAsync(batch);
                await context.SaveChangesAsync();
                
                // Clear change tracker to free memory
                context.ChangeTracker.Clear();
                
                Logger.LogInformation("Processed batch {BatchNumber}/{TotalBatches} for {EntityType}", 
                    (i / batchSize) + 1, 
                    (entitiesList.Count + batchSize - 1) / batchSize,
                    typeof(T).Name);
            }
        }
        finally
        {
            context.ChangeTracker.AutoDetectChangesEnabled = true;
        }
    }
}
```

### 5. **Error Handling and Rollback Strategy**

```csharp
public class TransactionalSeeder : BaseSeeder
{
    protected async Task ExecuteWithTransactionAsync(DownstairsDbContext context, Func<Task> seederAction)
    {
        using var transaction = await context.Database.BeginTransactionAsync();
        try
        {
            await seederAction();
            await context.SaveChangesAsync();
            await transaction.CommitAsync();
            
            Logger.LogInformation("Successfully completed seeding with transaction");
        }
        catch (Exception ex)
        {
            await transaction.RollbackAsync();
            Logger.LogError(ex, "Seeding failed, transaction rolled back");
            throw;
        }
    }
}
```

### 6. **Validation and Integrity Checks**

```csharp
public class ValidatedSeeder : BaseSeeder
{
    protected async Task<bool> ValidateSeededDataAsync<T>(DbSet<T> dbSet, Func<IQueryable<T>, Task<bool>> validationFunc) where T : class
    {
        try
        {
            var isValid = await validationFunc(dbSet);
            if (!isValid)
            {
                Logger.LogWarning("Data validation failed for {EntityType}", typeof(T).Name);
            }
            return isValid;
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Error during data validation for {EntityType}", typeof(T).Name);
            return false;
        }
    }
}
```

### 7. **Dependency Injection Registration**

For a system with 41+ seeders, organize DI registration in separate files for maintainability:

#### Create Dedicated DI Extension File

```csharp
// Infrastructure/DependencyInjection/SeederServiceExtensions.cs
using Microsoft.Extensions.DependencyInjection;
using Downstairs.Infrastructure.Persistence.Seeders;
using Downstairs.Infrastructure.Persistence.Seeders.Foundation;
using Downstairs.Infrastructure.Persistence.Seeders.Business;
using Downstairs.Infrastructure.Persistence.Seeders.Workflow;

namespace Downstairs.Infrastructure.DependencyInjection;

public static class SeederServiceExtensions
{
    public static IServiceCollection AddDatabaseSeeders(this IServiceCollection services)
    {
        // Register the orchestrator
        services.AddScoped<DatabaseSeederOrchestrator>();
        
        // Register all foundation seeders (Order 10-70)
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
    
    public static IServiceCollection AddSeederDataSources(this IServiceCollection services, IConfiguration configuration)
    {
        var dataSourcesConfig = configuration.GetSection("DatabaseSeeding:DataSources");
        
        // Register data sources with configuration paths
        services.AddScoped<ISeederDataSource<Country>>(provider => 
            new JsonFileDataSource<Country>(dataSourcesConfig["CountriesFilePath"] ?? "Data/Seeders/countries.json"));
            
        services.AddScoped<ISeederDataSource<City>>(provider => 
            new SqlFileDataSource<City>(
                dataSourcesConfig["CitiesFilePath"] ?? "Data/Seeders/cities.sql", 
                provider.GetRequiredService<DownstairsDbContext>()));
                
        services.AddScoped<ISeederDataSource<GlobalSetting>>(provider => 
            new JsonFileDataSource<GlobalSetting>(dataSourcesConfig["GlobalSettingsFilePath"] ?? "Data/Seeders/global_settings.json"));
        
        return services;
    }
    
    public static IServiceCollection AddOptionalSeeders(this IServiceCollection services)
    {
        // Register commented/optional seeders (not executed by default)
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
}
```

#### Organized Folder Structure

```
Infrastructure/
├── Persistence/
│   ├── Seeders/
│   │   ├── Interfaces/
│   │   │   ├── ISeeder.cs
│   │   │   └── ISeederDataSource.cs
│   │   ├── Base/
│   │   │   ├── BaseSeeder.cs
│   │   │   ├── OptimizedBulkSeeder.cs
│   │   │   ├── TransactionalSeeder.cs
│   │   │   └── ValidatedSeeder.cs
│   │   ├── Foundation/           # Order 10-70
│   │   │   ├── PermissionsSeeder.cs
│   │   │   ├── RolesSeeder.cs
│   │   │   ├── CountriesSeeder.cs
│   │   │   ├── CitiesSeeder.cs
│   │   │   ├── KeyPlacesSeeder.cs
│   │   │   ├── PropertyTypeSeeder.cs
│   │   │   └── GlobalSettingsSeeder.cs
│   │   ├── Business/             # Order 100-380
│   │   │   ├── Services/
│   │   │   │   ├── DefaultServicesSeeder.cs
│   │   │   │   ├── DefaultServiceQuartersSeeder.cs
│   │   │   │   ├── ServicesSeeder.cs
│   │   │   │   └── StoresSeeder.cs
│   │   │   ├── Products/
│   │   │   │   ├── DefaultProductsSeeder.cs
│   │   │   │   ├── ProductsSeeder.cs
│   │   │   │   └── CategoriesSeeder.cs
│   │   │   ├── Users/
│   │   │   │   ├── UsersSeeder.cs
│   │   │   │   └── UserCompaniesSeeder.cs
│   │   │   ├── Pricing/
│   │   │   │   ├── FixedPricesSeeder.cs
│   │   │   │   └── CustomerDiscountsSeeder.cs
│   │   │   └── System/
│   │   │       ├── BlockDaysSeeder.cs
│   │   │       ├── NotificationSeeder.cs
│   │   │       ├── FeedbackSeeder.cs
│   │   │       ├── OauthSeeder.cs
│   │   │       ├── TeamSeeder.cs
│   │   │       └── AddFortnoxArticleIdSeeder.cs
│   │   ├── Workflow/             # Order 400-580
│   │   │   ├── Schedules/
│   │   │   │   ├── ScheduleSeeder.cs
│   │   │   │   ├── ScheduleProductSeeder.cs
│   │   │   │   ├── ScheduleCleaningDeviationSeeder.cs
│   │   │   │   └── ScheduleChangeRequestSeeder.cs
│   │   │   ├── Orders/
│   │   │   │   ├── OrderLaundrySeeder.cs
│   │   │   │   ├── LaundryOrderSeeder.cs
│   │   │   │   ├── LaundryOrderProductSeeder.cs
│   │   │   │   └── LaundryOrderHistorySeeder.cs
│   │   │   ├── WorkForce/
│   │   │   │   ├── WorkersWithoutSchedulesSeeder.cs
│   │   │   │   ├── LeaveRegistrationSeeder.cs
│   │   │   │   └── WorkHourSeeder.cs
│   │   │   ├── Adjustments/
│   │   │   │   ├── TimeAdjustmentSeeder.cs
│   │   │   │   └── PriceAdjustmentSeeder.cs
│   │   │   └── Sales/
│   │   │       ├── StoreSeeder.cs
│   │   │       ├── StoreProductSeeder.cs
│   │   │       ├── StoreSaleSeeder.cs
│   │   │       └── CreditSeeder.cs
│   │   ├── Optional/             # Commented/Special purpose
│   │   │   ├── SubscriptionSeeder.cs
│   │   │   ├── UserTestSeeder.cs
│   │   │   ├── DatabaseMergeSeeder.cs
│   │   │   └── ... (other optional seeders)
│   │   ├── DataSources/
│   │   │   ├── SqlFileDataSource.cs
│   │   │   ├── JsonFileDataSource.cs
│   │   │   └── ConfigurationDataSource.cs
│   │   └── DatabaseSeederOrchestrator.cs
│   └── ... (other persistence files)
├── DependencyInjection/
│   ├── SeederServiceExtensions.cs
│   ├── PersistenceServiceExtensions.cs
│   └── ... (other DI extensions)
└── ... (other infrastructure)
```

#### Usage in Program.cs

```csharp
// Program.cs - Clean and simple registration
using Downstairs.Infrastructure.DependencyInjection;

var builder = WebApplication.CreateBuilder(args);

// Register all database seeders
builder.Services.AddDatabaseSeeders();
builder.Services.AddSeederDataSources(builder.Configuration);

// Optionally register special purpose seeders
if (builder.Environment.IsDevelopment())
{
    builder.Services.AddOptionalSeeders();
}

var app = builder.Build();

// Execute seeding
if (app.Environment.IsDevelopment() || args.Contains("--seed"))
{
    using var scope = app.Services.CreateScope();
    var seeder = scope.ServiceProvider.GetRequiredService<DatabaseSeederOrchestrator>();
    await seeder.SeedAllAsync();
}

app.Run();
```

#### Benefits of Separate DI Organization:

1. **Maintainability**: Easy to find and modify registrations
2. **Organization**: Logical grouping by functionality
3. **Testability**: Can register mock implementations easily
4. **Flexibility**: Conditional registration based on environment
5. **Clarity**: Clean Program.cs without clutter
6. **Extensibility**: Easy to add new seeder categories
7. **Performance**: Scoped registration prevents memory leaks
8. **Configuration**: Centralized data source configuration

This approach scales well and keeps your main application startup clean while providing excellent organization for the complex seeding system.

### 8. **Usage in Application**

```csharp
// In Program.cs
if (app.Environment.IsDevelopment() || args.Contains("--seed"))
{
    using var scope = app.Services.CreateScope();
    var seeder = scope.ServiceProvider.GetRequiredService<DatabaseSeederOrchestrator>();
    await seeder.SeedAllAsync();
}
```

### 9. **Benefits of This Refactored Approach**

- **Modularity**: Each seeder is independent and testable
- **Maintainability**: Easy to add/remove/modify individual seeders
- **Performance**: Optimized bulk operations and memory management
- **Flexibility**: Configuration-driven behavior for different environments
- **Reliability**: Proper error handling and transaction management
- **Extensibility**: Easy to add new data sources or validation rules
- **Testability**: Each seeder can be unit tested independently

This refactored approach transforms the monolithic seeding process into a clean, maintainable, and extensible system that follows .NET best practices.