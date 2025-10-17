using Downstairs.LogPortal.Models;
using Microsoft.Extensions.Diagnostics.HealthChecks;
using LogLevel = Downstairs.LogPortal.Models.LogLevel;

namespace Downstairs.LogPortal.Services;

/// <summary>
/// Service for collecting logs from all services
/// </summary>
public interface ILogService
{
    Task<List<LogEntry>> GetLogsAsync(DateTime? from = null, DateTime? to = null, string? serviceName = null, LogLevel? level = null);
    Task<List<LogEntry>> GetRealtimeLogsAsync(int count = 100);
    Task<Dictionary<string, int>> GetLogCountsByServiceAsync(DateTime from, DateTime to);
    Task<Dictionary<LogLevel, int>> GetLogCountsByLevelAsync(DateTime from, DateTime to);
}

public class LogService : ILogService
{
    private readonly ILogger<LogService> _logger;
    private readonly IConfiguration _configuration;

    public LogService(ILogger<LogService> logger, IConfiguration configuration)
    {
        _logger = logger;
        _configuration = configuration;
    }

    public async Task<List<LogEntry>> GetLogsAsync(DateTime? from = null, DateTime? to = null, string? serviceName = null, LogLevel? level = null)
    {
        // TODO: Implement actual log collection from centralized log store
        // For now, return mock data
        await Task.Delay(10);
        
        var logs = new List<LogEntry>();
        var services = new[] { "api", "jobs", "api-gateway", "admin" };
        var random = new Random();

        for (int i = 0; i < 50; i++)
        {
            logs.Add(new LogEntry
            {
                Id = i + 1,
                Timestamp = DateTime.UtcNow.AddMinutes(-random.Next(0, 1440)), // Last 24 hours
                ServiceName = services[random.Next(services.Length)],
                Level = (LogLevel)random.Next(0, 6),
                Message = $"Sample log message {i + 1}",
                CorrelationId = Guid.NewGuid().ToString()[..8]
            });
        }

        // Apply filters
        if (from.HasValue)
            logs = logs.Where(l => l.Timestamp >= from.Value).ToList();
        
        if (to.HasValue)
            logs = logs.Where(l => l.Timestamp <= to.Value).ToList();
        
        if (!string.IsNullOrEmpty(serviceName))
            logs = logs.Where(l => l.ServiceName.Contains(serviceName, StringComparison.OrdinalIgnoreCase)).ToList();
        
        if (level.HasValue)
            logs = logs.Where(l => l.Level >= level.Value).ToList();

        return logs.OrderByDescending(l => l.Timestamp).ToList();
    }

    public async Task<List<LogEntry>> GetRealtimeLogsAsync(int count = 100)
    {
        return await GetLogsAsync(DateTime.UtcNow.AddHours(-1), DateTime.UtcNow);
    }

    public async Task<Dictionary<string, int>> GetLogCountsByServiceAsync(DateTime from, DateTime to)
    {
        var logs = await GetLogsAsync(from, to);
        return logs.GroupBy(l => l.ServiceName)
                   .ToDictionary(g => g.Key, g => g.Count());
    }

    public async Task<Dictionary<LogLevel, int>> GetLogCountsByLevelAsync(DateTime from, DateTime to)
    {
        var logs = await GetLogsAsync(from, to);
        return logs.GroupBy(l => l.Level)
                   .ToDictionary(g => g.Key, g => g.Count());
    }
}