using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Base;

/// <summary>
/// Base class for all database seeders
/// Provides common functionality and dependencies
/// </summary>
public abstract class BaseSeeder : ISeeder
{
    protected ILogger Logger { get; }
    protected IConfiguration Configuration { get; }
    protected IHostEnvironment Environment { get; }

    public abstract int Order { get; }
    public abstract string Name { get; }
    public virtual bool IsEnabled => true;

    protected BaseSeeder(IServiceProvider serviceProvider)
    {
        Logger = serviceProvider.GetRequiredService<ILogger<BaseSeeder>>();
        Configuration = serviceProvider.GetRequiredService<IConfiguration>();
        Environment = serviceProvider.GetRequiredService<IHostEnvironment>();
    }

    public abstract Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider);

    /// <summary>
    /// Check if seeding should be skipped for the given entity type
    /// </summary>
    /// <typeparam name="T">Entity type</typeparam>
    /// <param name="dbSet">Database set to check</param>
    /// <param name="seederName">Seeder name for logging</param>
    /// <returns>True if seeding should be skipped</returns>
    protected async Task<bool> ShouldSkipSeedingAsync<T>(DbSet<T> dbSet, string seederName) where T : class
    {
        if (await dbSet.AnyAsync())
        {
            Logger.LogInformation("{SeederName} already seeded, skipping...", seederName);
            return true;
        }
        return false;
    }

    /// <summary>
    /// Execute seeding with proper logging
    /// </summary>
    /// <param name="context">Database context</param>
    /// <param name="seedingAction">Action to perform seeding</param>
    protected async Task ExecuteSeedingAsync(DownstairsDbContext context, Func<Task> seedingAction)
    {
        Logger.LogInformation("Starting seeding: {SeederName}", Name);

        try
        {
            await seedingAction();
            await context.SaveChangesAsync();

            Logger.LogInformation("Completed seeding: {SeederName}", Name);
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to seed: {SeederName}", Name);
            throw;
        }
    }

    /// <summary>
    /// Get environment-specific behavior
    /// </summary>
    protected bool IsTestingEnvironment => Environment.EnvironmentName == "Testing";
    protected bool IsDevelopmentEnvironment => Environment.IsDevelopment();
    protected bool IsProductionEnvironment => Environment.IsProduction();
}