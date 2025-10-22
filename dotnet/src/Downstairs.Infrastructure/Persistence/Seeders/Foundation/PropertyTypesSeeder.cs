using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds property types data from Laravel PropertyTypesSeeder
/// Foundation seeder for real estate property classifications
/// </summary>
public class PropertyTypesSeeder : OptimizedBulkSeeder
{
    public override int Order => 60;
    public override string Name => "PropertyTypes";

    public PropertyTypesSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.PropertyTypes, Name))
        {
            return;
        }

        var propertyTypes = GetPropertyTypes();
        await BulkInsertAsync(context, propertyTypes, batchSize: 20);

        Logger.LogInformation("Seeded {Count} property types", propertyTypes.Count);
    }

    private List<PropertyType> GetPropertyTypes()
    {
        var now = DateTime.UtcNow;

        return new List<PropertyType>
        {
            // Basic property types based on database structure
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now },
            new() { CreatedAt = now, UpdatedAt = now }
        };
    }
}