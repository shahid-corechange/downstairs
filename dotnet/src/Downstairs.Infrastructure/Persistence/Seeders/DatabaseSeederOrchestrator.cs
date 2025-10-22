using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders;

/// <summary>
/// Orchestrates the execution of all database seeders
/// Manages dependency order, error handling, and logging for the complete seeding process
/// </summary>
public class DatabaseSeederOrchestrator
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<DatabaseSeederOrchestrator> _logger;
    private readonly IConfiguration _configuration;

    public DatabaseSeederOrchestrator(IServiceProvider serviceProvider)
    {
        _serviceProvider = serviceProvider ?? throw new ArgumentNullException(nameof(serviceProvider));
        _logger = _serviceProvider.GetRequiredService<ILogger<DatabaseSeederOrchestrator>>();
        _configuration = _serviceProvider.GetRequiredService<IConfiguration>();
    }

    /// <summary>
    /// Execute all enabled seeders in dependency order
    /// </summary>
    public async Task SeedAllAsync()
    {
        using var scope = _serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();

        _logger.LogInformation("Starting comprehensive database seeding...");

        try
        {
            var seeders = GetEnabledSeeders(scope.ServiceProvider);
            var orderedSeeders = seeders.OrderBy(s => s.Order).ToList();

            _logger.LogInformation("Found {Count} enabled seeders to execute", orderedSeeders.Count);

            foreach (var seeder in orderedSeeders)
            {
                try
                {
                    _logger.LogInformation("Executing seeder: {SeederName} (Order: {Order})",
                        seeder.Name, seeder.Order);

                    await seeder.SeedAsync(context, scope.ServiceProvider);

                    _logger.LogInformation("Successfully completed seeder: {SeederName}", seeder.Name);
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Failed to execute seeder: {SeederName}", seeder.Name);

                    // Check if we should continue on error
                    var continueOnError = _configuration.GetValue<bool>("DatabaseSeeding:ContinueOnError", false);
                    if (!continueOnError)
                    {
                        throw;
                    }

                    _logger.LogWarning("Continuing seeding process despite error in {SeederName}", seeder.Name);
                }
            }

            _logger.LogInformation("Database seeding completed successfully - {Count} seeders executed", orderedSeeders.Count);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Database seeding process failed");
            throw;
        }
    }

    /// <summary>
    /// Execute specific seeders by name
    /// </summary>
    /// <param name="seederNames">Names of seeders to execute</param>
    public async Task SeedSpecificAsync(params string[] seederNames)
    {
        using var scope = _serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();

        _logger.LogInformation("Starting specific database seeding for: {SeederNames}", string.Join(", ", seederNames));

        try
        {
            var allSeeders = GetAllSeeders(scope.ServiceProvider);
            var targetSeeders = allSeeders
                .Where(s => seederNames.Contains(s.Name, StringComparer.OrdinalIgnoreCase))
                .OrderBy(s => s.Order)
                .ToList();

            if (targetSeeders.Count != seederNames.Length)
            {
                var foundNames = targetSeeders.Select(s => s.Name).ToArray();
                var missingNames = seederNames.Except(foundNames, StringComparer.OrdinalIgnoreCase).ToArray();
                _logger.LogWarning("Some seeders not found: {MissingNames}", string.Join(", ", missingNames));
            }

            foreach (var seeder in targetSeeders)
            {
                _logger.LogInformation("Executing specific seeder: {SeederName}", seeder.Name);
                await seeder.SeedAsync(context, scope.ServiceProvider);
                _logger.LogInformation("Completed specific seeder: {SeederName}", seeder.Name);
            }

            _logger.LogInformation("Specific database seeding completed - {Count} seeders executed", targetSeeders.Count);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Specific database seeding failed");
            throw;
        }
    }

    /// <summary>
    /// Get list of all available seeders for information purposes
    /// </summary>
    /// <returns>List of seeder information</returns>
    public List<SeederInfo> GetAvailableSeeders()
    {
        using var scope = _serviceProvider.CreateScope();
        var allSeeders = GetAllSeeders(scope.ServiceProvider);

        return allSeeders
            .OrderBy(s => s.Order)
            .Select(s => new SeederInfo
            {
                Name = s.Name,
                Order = s.Order,
                IsEnabled = s.IsEnabled
            })
            .ToList();
    }

    /// <summary>
    /// Get all enabled seeders from DI container
    /// </summary>
    private List<ISeeder> GetEnabledSeeders(IServiceProvider serviceProvider)
    {
        var enabledSeederNames = _configuration.GetSection("DatabaseSeeding:EnabledSeeders").Get<string[]>();

        var allSeeders = serviceProvider.GetServices<ISeeder>().ToList();

        // If no specific configuration, return all enabled seeders
        if (enabledSeederNames == null || !enabledSeederNames.Any())
        {
            return allSeeders.Where(s => s.IsEnabled).ToList();
        }

        // Filter by configuration
        return allSeeders
            .Where(s => s.IsEnabled && enabledSeederNames.Contains(s.Name, StringComparer.OrdinalIgnoreCase))
            .ToList();
    }

    /// <summary>
    /// Get all seeders from DI container (including disabled ones)
    /// </summary>
    private List<ISeeder> GetAllSeeders(IServiceProvider serviceProvider)
    {
        return serviceProvider.GetServices<ISeeder>().ToList();
    }
}

/// <summary>
/// Information about a seeder for reporting purposes
/// </summary>
public class SeederInfo
{
    public string Name { get; set; } = string.Empty;
    public int Order { get; set; }
    public bool IsEnabled { get; set; }
}