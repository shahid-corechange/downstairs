using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds countries data from Laravel CountriesSeeder
/// Foundation seeder for geographical data
/// </summary>
public class CountriesSeeder : OptimizedBulkSeeder
{
    public override int Order => 30;
    public override string Name => "Countries";

    public CountriesSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Countries, Name))
        {
            return;
        }

        var countries = GetCountries();
        await BulkInsertAsync(context, countries, batchSize: 50);

        Logger.LogInformation("Seeded {Count} countries", countries.Count);
    }

    private List<Country> GetCountries()
    {
        return new List<Country>
        {
            new() { Code = "SE", Name = "Sweden", Currency = "SEK", DialCode = "+46", Flag = "🇸🇪" },
            new() { Code = "NO", Name = "Norway", Currency = "NOK", DialCode = "+47", Flag = "🇳🇴" },
            new() { Code = "DK", Name = "Denmark", Currency = "DKK", DialCode = "+45", Flag = "🇩🇰" },
            new() { Code = "FI", Name = "Finland", Currency = "EUR", DialCode = "+358", Flag = "🇫🇮" },
            new() { Code = "IS", Name = "Iceland", Currency = "ISK", DialCode = "+354", Flag = "🇮🇸" },
            new() { Code = "DE", Name = "Germany", Currency = "EUR", DialCode = "+49", Flag = "🇩🇪" },
            new() { Code = "GB", Name = "United Kingdom", Currency = "GBP", DialCode = "+44", Flag = "🇬🇧" },
            new() { Code = "FR", Name = "France", Currency = "EUR", DialCode = "+33", Flag = "🇫🇷" },
            new() { Code = "NL", Name = "Netherlands", Currency = "EUR", DialCode = "+31", Flag = "🇳🇱" },
            new() { Code = "BE", Name = "Belgium", Currency = "EUR", DialCode = "+32", Flag = "🇧🇪" },
            new() { Code = "AT", Name = "Austria", Currency = "EUR", DialCode = "+43", Flag = "🇦🇹" },
            new() { Code = "CH", Name = "Switzerland", Currency = "CHF", DialCode = "+41", Flag = "🇨🇭" },
            new() { Code = "IT", Name = "Italy", Currency = "EUR", DialCode = "+39", Flag = "🇮🇹" },
            new() { Code = "ES", Name = "Spain", Currency = "EUR", DialCode = "+34", Flag = "🇪🇸" },
            new() { Code = "PT", Name = "Portugal", Currency = "EUR", DialCode = "+351", Flag = "🇵🇹" },
            new() { Code = "PL", Name = "Poland", Currency = "PLN", DialCode = "+48", Flag = "🇵🇱" },
            new() { Code = "CZ", Name = "Czech Republic", Currency = "CZK", DialCode = "+420", Flag = "🇨🇿" },
            new() { Code = "SK", Name = "Slovakia", Currency = "EUR", DialCode = "+421", Flag = "🇸🇰" },
            new() { Code = "HU", Name = "Hungary", Currency = "HUF", DialCode = "+36", Flag = "🇭🇺" },
            new() { Code = "SI", Name = "Slovenia", Currency = "EUR", DialCode = "+386", Flag = "🇸🇮" },
            new() { Code = "HR", Name = "Croatia", Currency = "EUR", DialCode = "+385", Flag = "🇭🇷" },
            new() { Code = "EE", Name = "Estonia", Currency = "EUR", DialCode = "+372", Flag = "🇪🇪" },
            new() { Code = "LV", Name = "Latvia", Currency = "EUR", DialCode = "+371", Flag = "🇱🇻" },
            new() { Code = "LT", Name = "Lithuania", Currency = "EUR", DialCode = "+370", Flag = "🇱🇹" },
            new() { Code = "US", Name = "United States", Currency = "USD", DialCode = "+1", Flag = "🇺🇸" },
            new() { Code = "CA", Name = "Canada", Currency = "CAD", DialCode = "+1", Flag = "🇨🇦" }
        };
    }
}