namespace Downstairs.Infrastructure.Persistence.Seeders.Interfaces;

/// <summary>
/// Core interface for all database seeders
/// Provides consistent seeding behavior across all seeder implementations
/// </summary>
public interface ISeeder
{
    /// <summary>
    /// Execute the seeding operation
    /// </summary>
    /// <param name="context">Database context for seeding operations</param>
    /// <param name="serviceProvider">Service provider for dependency resolution</param>
    /// <returns>Task representing the seeding operation</returns>
    Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider);

    /// <summary>
    /// Execution order for dependency management
    /// Lower numbers execute first
    /// </summary>
    int Order { get; }

    /// <summary>
    /// Seeder name for logging and identification
    /// </summary>
    string Name { get; }

    /// <summary>
    /// Whether this seeder is enabled by default
    /// Used for optional/special purpose seeders
    /// </summary>
    bool IsEnabled { get; }
}