using System.Diagnostics;
using StackExchange.Redis;

namespace Downstairs.Infrastructure.Locking;

/// <summary>
/// Redis-based distributed locking service
/// </summary>
public class RedisDistributedLockService(IConnectionMultiplexer redis) : IDistributedLockService
{
    private readonly IDatabase _database = redis.GetDatabase();
    private static readonly ActivitySource ActivitySource = new("Downstairs.Locking");

    public async Task<IDistributedLock?> AcquireLockAsync(string lockKey, TimeSpan expiry, CancellationToken cancellationToken = default)
    {
        using var activity = ActivitySource.StartActivity("acquire_lock");
        activity?.SetTag("lock.key", lockKey);
        activity?.SetTag("lock.expiry_seconds", expiry.TotalSeconds);

        try
        {
            var lockId = Guid.NewGuid().ToString();
            var acquired = await _database.StringSetAsync(lockKey, lockId, expiry, When.NotExists);

            if (acquired)
            {
                activity?.SetTag("lock.acquired", true);
                return new RedisDistributedLock(_database, lockKey, lockId);
            }

            activity?.SetTag("lock.acquired", false);
            return null;
        }
        catch (Exception ex)
        {
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
            throw;
        }
    }

    public async Task<bool> ReleaseLockAsync(string lockKey, string lockId, CancellationToken cancellationToken = default)
    {
        using var activity = ActivitySource.StartActivity("release_lock");
        activity?.SetTag("lock.key", lockKey);

        try
        {
            const string script = @"
                if redis.call('GET', KEYS[1]) == ARGV[1] then
                    return redis.call('DEL', KEYS[1])
                else
                    return 0
                end";

            var result = await _database.ScriptEvaluateAsync(script, new RedisKey[] { lockKey }, new RedisValue[] { lockId });
            var released = result.ToString() == "1";

            activity?.SetTag("lock.released", released);
            return released;
        }
        catch (Exception ex)
        {
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
            throw;
        }
    }
}

/// <summary>
/// Redis implementation of a distributed lock
/// </summary>
internal class RedisDistributedLock(IDatabase database, string lockKey, string lockId) : IDistributedLock
{
    private readonly IDatabase _database = database;
    private readonly string _lockKey = lockKey;
    private readonly string _lockId = lockId;
    private bool _disposed = false;

    public async ValueTask DisposeAsync()
    {
        if (!_disposed)
        {
            const string script = @"
                if redis.call('GET', KEYS[1]) == ARGV[1] then
                    return redis.call('DEL', KEYS[1])
                else
                    return 0
                end";

            await _database.ScriptEvaluateAsync(script, new RedisKey[] { _lockKey }, new RedisValue[] { _lockId });
            _disposed = true;
        }
    }

    public async Task<bool> ExtendAsync(TimeSpan newExpiry)
    {
        if (_disposed)
        {
            return false;
        }

        const string script = @"
            if redis.call('GET', KEYS[1]) == ARGV[1] then
                return redis.call('EXPIRE', KEYS[1], ARGV[2])
            else
                return 0
            end";

        var result = await _database.ScriptEvaluateAsync(script,
            new RedisKey[] { _lockKey },
            new RedisValue[] { _lockId, (int)newExpiry.TotalSeconds });

        return result.ToString() == "1";
    }
}