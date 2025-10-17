using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence;

/// <summary>
/// Database seeding service for initial sample data
/// </summary>
public static class DatabaseSeeder
{
    /// <summary>
    /// Seed initial data including customers and invoices
    /// </summary>
    public static async Task SeedAsync(IServiceProvider serviceProvider)
    {
        using var scope = serviceProvider.CreateScope();
        var logger = scope.ServiceProvider.GetRequiredService<ILogger<DownstairsDbContext>>();

        logger.LogInformation("Skipping seed: database-first models are in use and seeding is managed externally.");

        await Task.CompletedTask;
    }
}