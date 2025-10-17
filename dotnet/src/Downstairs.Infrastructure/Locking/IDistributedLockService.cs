namespace Downstairs.Infrastructure.Locking;

/// <summary>
/// Interface for distributed locking operations
/// </summary>
public interface IDistributedLockService
{
    /// <summary>
    /// Attempts to acquire a distributed lock
    /// </summary>
    /// <param name="lockKey">The key for the lock</param>
    /// <param name="expiry">How long the lock should be held</param>
    /// <param name="cancellationToken">Cancellation token</param>
    /// <returns>The lock if acquired, null if not available</returns>
    Task<IDistributedLock?> AcquireLockAsync(string lockKey, TimeSpan expiry, CancellationToken cancellationToken = default);

    /// <summary>
    /// Releases a distributed lock
    /// </summary>
    /// <param name="lockKey">The key for the lock</param>
    /// <param name="lockId">The unique ID of the lock</param>
    /// <param name="cancellationToken">Cancellation token</param>
    /// <returns>True if released successfully</returns>
    Task<bool> ReleaseLockAsync(string lockKey, string lockId, CancellationToken cancellationToken = default);
}

/// <summary>
/// Represents a distributed lock
/// </summary>
public interface IDistributedLock : IAsyncDisposable
{
    /// <summary>
    /// Extends the lock expiry time
    /// </summary>
    /// <param name="newExpiry">The new expiry duration</param>
    /// <returns>True if extended successfully</returns>
    Task<bool> ExtendAsync(TimeSpan newExpiry);
}