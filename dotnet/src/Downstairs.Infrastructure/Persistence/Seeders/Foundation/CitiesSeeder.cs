using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds cities data from Laravel CitiesSeeder
/// Foundation seeder - depends on countries
/// </summary>
public class CitiesSeeder : OptimizedBulkSeeder
{
    public override int Order => 40;
    public override string Name => "Cities";

    public CitiesSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Cities, Name))
        {
            return;
        }

        var cities = await GetCitiesAsync(context);
        await BulkInsertAsync(context, cities, batchSize: 50);

        Logger.LogInformation("Seeded {Count} cities", cities.Count);
    }

    private async System.Threading.Tasks.Task<List<City>> GetCitiesAsync(DownstairsDbContext context)
    {
        var sweden = await context.Countries.FirstAsync(c => c.Code == "SE");
        var norway = await context.Countries.FirstAsync(c => c.Code == "NO");
        var denmark = await context.Countries.FirstAsync(c => c.Code == "DK");

        return new List<City>
        {
            // Swedish cities
            new() { Name = "Stockholm", CountryId = sweden.Id },
            new() { Name = "Göteborg", CountryId = sweden.Id },
            new() { Name = "Malmö", CountryId = sweden.Id },
            new() { Name = "Uppsala", CountryId = sweden.Id },
            new() { Name = "Västerås", CountryId = sweden.Id },
            new() { Name = "Örebro", CountryId = sweden.Id },
            new() { Name = "Linköping", CountryId = sweden.Id },
            new() { Name = "Helsingborg", CountryId = sweden.Id },
            new() { Name = "Jönköping", CountryId = sweden.Id },
            new() { Name = "Norrköping", CountryId = sweden.Id },
            new() { Name = "Lund", CountryId = sweden.Id },
            new() { Name = "Umeå", CountryId = sweden.Id },
            new() { Name = "Gävle", CountryId = sweden.Id },
            new() { Name = "Borås", CountryId = sweden.Id },
            new() { Name = "Eskilstuna", CountryId = sweden.Id },
            new() { Name = "Södertälje", CountryId = sweden.Id },
            new() { Name = "Karlstad", CountryId = sweden.Id },
            new() { Name = "Täby", CountryId = sweden.Id },
            new() { Name = "Växjö", CountryId = sweden.Id },
            new() { Name = "Halmstad", CountryId = sweden.Id },
            
            // Norwegian cities
            new() { Name = "Oslo", CountryId = norway.Id },
            new() { Name = "Bergen", CountryId = norway.Id },
            new() { Name = "Trondheim", CountryId = norway.Id },
            new() { Name = "Stavanger", CountryId = norway.Id },
            new() { Name = "Bærum", CountryId = norway.Id },
            new() { Name = "Kristiansand", CountryId = norway.Id },
            new() { Name = "Fredrikstad", CountryId = norway.Id },
            new() { Name = "Tromsø", CountryId = norway.Id },
            new() { Name = "Sandnes", CountryId = norway.Id },
            new() { Name = "Drammen", CountryId = norway.Id },
            
            // Danish cities
            new() { Name = "Copenhagen", CountryId = denmark.Id },
            new() { Name = "Aarhus", CountryId = denmark.Id },
            new() { Name = "Odense", CountryId = denmark.Id },
            new() { Name = "Aalborg", CountryId = denmark.Id },
            new() { Name = "Frederiksberg", CountryId = denmark.Id },
            new() { Name = "Esbjerg", CountryId = denmark.Id },
            new() { Name = "Randers", CountryId = denmark.Id },
            new() { Name = "Kolding", CountryId = denmark.Id },
            new() { Name = "Horsens", CountryId = denmark.Id },
            new() { Name = "Vejle", CountryId = denmark.Id }
        };
    }
}