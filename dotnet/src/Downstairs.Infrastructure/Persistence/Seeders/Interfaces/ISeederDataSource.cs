namespace Downstairs.Infrastructure.Persistence.Seeders.Interfaces;

/// <summary>
/// Data source abstraction for seeder data
/// Allows flexible data loading from various sources (SQL files, JSON files, configuration, etc.)
/// </summary>
/// <typeparam name="T">Entity type to be seeded</typeparam>
public interface ISeederDataSource<T> where T : class
{
    /// <summary>
    /// Retrieve data from the source
    /// </summary>
    /// <returns>Collection of entities to be seeded</returns>
    Task<IEnumerable<T>> GetDataAsync();

    /// <summary>
    /// Check if the data source is available
    /// </summary>
    /// <returns>True if data source is available, false otherwise</returns>
    Task<bool> IsAvailableAsync();

    /// <summary>
    /// Get the data source name for logging
    /// </summary>
    string SourceName { get; }
}