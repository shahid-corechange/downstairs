using System.Diagnostics;
using System.Diagnostics.Metrics;
using System.Text.Json;
using Downstairs.Application.Common.Interfaces;
using Microsoft.Extensions.Caching.Distributed;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Caching;

/// <summary>
/// Redis-based caching service with telemetry and error handling
/// </summary>
public class RedisCacheService(IDistributedCache cache, ILogger<RedisCacheService> logger) : ICacheService
{
    private readonly IDistributedCache _cache = cache;
    private readonly ILogger<RedisCacheService> _logger = logger;
    
    private static readonly ActivitySource ActivitySource = new("Downstairs.Cache");
    private static readonly Meter Meter = new("Downstairs.Cache");
    private static readonly Counter<int> CacheHits = Meter.CreateCounter<int>("cache_hits_total", "count", "Number of cache hits");
    private static readonly Counter<int> CacheMisses = Meter.CreateCounter<int>("cache_misses_total", "count", "Number of cache misses");
    private static readonly Histogram<double> CacheOperationDuration = Meter.CreateHistogram<double>("cache_operation_duration", "ms", "Duration of cache operations");

    private readonly JsonSerializerOptions _jsonOptions = new()
    {
        PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
        WriteIndented = false
    };

    public async Task<T?> GetAsync<T>(string key, CancellationToken cancellationToken = default) where T : class
    {
        using var activity = ActivitySource.StartActivity("cache_get");
        activity?.SetTag("cache.key", key);
        activity?.SetTag("cache.type", typeof(T).Name);

        var stopwatch = Stopwatch.StartNew();

        try
        {
            var cachedData = await _cache.GetStringAsync(key, cancellationToken);
            
            if (cachedData is null)
            {
                CacheMisses.Add(1, new KeyValuePair<string, object?>("cache.key", key));
                activity?.SetTag("cache.hit", false);
                _logger.LogDebug("Cache miss for key: {Key}", key);
                return null;
            }

            var result = JsonSerializer.Deserialize<T>(cachedData, _jsonOptions);
            CacheHits.Add(1, new KeyValuePair<string, object?>("cache.key", key));
            activity?.SetTag("cache.hit", true);
            _logger.LogDebug("Cache hit for key: {Key}", key);
            
            CacheOperationDuration.Record(stopwatch.ElapsedMilliseconds);
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to get cached data for key: {Key}", key);
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
            return null;
        }
    }

    public async Task SetAsync<T>(string key, T value, TimeSpan? expiry = null, CancellationToken cancellationToken = default) where T : class
    {
        using var activity = ActivitySource.StartActivity("cache_set");
        activity?.SetTag("cache.key", key);
        activity?.SetTag("cache.type", typeof(T).Name);
        activity?.SetTag("cache.expiry_minutes", expiry?.TotalMinutes ?? 0);

        var stopwatch = Stopwatch.StartNew();

        try
        {
            var serializedData = JsonSerializer.Serialize(value, _jsonOptions);
            
            var options = new DistributedCacheEntryOptions();
            if (expiry.HasValue)
            {
                options.SetAbsoluteExpiration(expiry.Value);
            }
            else
            {
                // Default 5 minutes TTL
                options.SetAbsoluteExpiration(TimeSpan.FromMinutes(5));
            }

            await _cache.SetStringAsync(key, serializedData, options, cancellationToken);
            
            _logger.LogDebug("Cached data for key: {Key} with expiry: {Expiry}", key, expiry ?? TimeSpan.FromMinutes(5));
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cache data for key: {Key}", key);
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
        }
        finally
        {
            CacheOperationDuration.Record(stopwatch.ElapsedMilliseconds);
        }
    }

    public async Task RemoveAsync(string key, CancellationToken cancellationToken = default)
    {
        using var activity = ActivitySource.StartActivity("cache_remove");
        activity?.SetTag("cache.key", key);

        var stopwatch = Stopwatch.StartNew();

        try
        {
            await _cache.RemoveAsync(key, cancellationToken);
            _logger.LogDebug("Removed cached data for key: {Key}", key);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove cached data for key: {Key}", key);
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
        }
        finally
        {
            CacheOperationDuration.Record(stopwatch.ElapsedMilliseconds);
        }
    }

    public Task RemoveByPatternAsync(string pattern, CancellationToken cancellationToken = default)
    {
        using var activity = ActivitySource.StartActivity("cache_remove_pattern");
        activity?.SetTag("cache.pattern", pattern);

        try
        {
            // Note: This is a simplified implementation. In production, you might want to use 
            // Redis SCAN with pattern matching for better performance
            _logger.LogDebug("Pattern-based cache removal for pattern: {Pattern}", pattern);
            
            // For now, we'll log a warning that this operation is not fully implemented
            _logger.LogWarning("Pattern-based cache removal is not fully implemented. Consider using specific keys or implementing Redis SCAN.");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to remove cached data for pattern: {Pattern}", pattern);
            activity?.SetStatus(ActivityStatusCode.Error, ex.Message);
        }
        
        return Task.CompletedTask;
    }
}