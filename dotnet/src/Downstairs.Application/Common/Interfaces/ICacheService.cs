namespace Downstairs.Application.Common.Interfaces;

/// <summary>
/// Interface for distributed caching operations
/// </summary>
public interface ICacheService
{
    /// <summary>
    /// Gets a cached value by key
    /// </summary>
    /// <typeparam name="T">The type of the cached object</typeparam>
    /// <param name="key">The cache key</param>
    /// <param name="cancellationToken">Cancellation token</param>
    /// <returns>The cached value or null if not found</returns>
    Task<T?> GetAsync<T>(string key, CancellationToken cancellationToken = default) where T : class;

    /// <summary>
    /// Sets a value in the cache with optional expiry
    /// </summary>
    /// <typeparam name="T">The type of the object to cache</typeparam>
    /// <param name="key">The cache key</param>
    /// <param name="value">The value to cache</param>
    /// <param name="expiry">Optional expiry time (defaults to 5 minutes)</param>
    /// <param name="cancellationToken">Cancellation token</param>
    Task SetAsync<T>(string key, T value, TimeSpan? expiry = null, CancellationToken cancellationToken = default) where T : class;

    /// <summary>
    /// Removes a cached value by key
    /// </summary>
    /// <param name="key">The cache key</param>
    /// <param name="cancellationToken">Cancellation token</param>
    Task RemoveAsync(string key, CancellationToken cancellationToken = default);

    /// <summary>
    /// Removes cached values matching a pattern
    /// </summary>
    /// <param name="pattern">The key pattern to match</param>
    /// <param name="cancellationToken">Cancellation token</param>
    Task RemoveByPatternAsync(string pattern, CancellationToken cancellationToken = default);
}