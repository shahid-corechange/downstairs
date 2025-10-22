using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds key places data from Laravel KeyPlacesSeeder
/// Foundation seeder for important locations and landmarks
/// </summary>
public class KeyPlacesSeeder : OptimizedBulkSeeder
{
    public override int Order => 50;
    public override string Name => "KeyPlaces";

    public KeyPlacesSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.KeyPlaces, Name))
        {
            return;
        }

        var keyPlaces = GetKeyPlaces();
        await BulkInsertAsync(context, keyPlaces, batchSize: 20);

        Logger.LogInformation("Seeded {Count} key places", keyPlaces.Count);
    }

    private List<KeyPlace> GetKeyPlaces()
    {
        var now = DateTime.UtcNow;

        return new List<KeyPlace>
        {
            // Key places based on database structure
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now },
            new() { PropertyId = null, CreatedAt = now, UpdatedAt = now }
        };
    }
}