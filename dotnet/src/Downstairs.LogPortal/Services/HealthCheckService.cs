using Downstairs.LogPortal.Models;
using Microsoft.Extensions.Diagnostics.HealthChecks;
using HealthStatus = Downstairs.LogPortal.Models.HealthStatus;

namespace Downstairs.LogPortal.Services;

/// <summary>
/// Service for monitoring health of all services in the solution
/// </summary>
public interface IHealthCheckService
{
    Task<List<ServiceHealth>> GetAllServiceHealthAsync();
    Task<ServiceHealth> GetServiceHealthAsync(string serviceName);
    Task<HealthCheckResult> CheckDatabaseHealthAsync();
    Task<HealthCheckResult> CheckRedisHealthAsync();
    Task<HealthCheckResult> CheckServiceBusHealthAsync();
}

public class HealthCheckService : IHealthCheckService
{
    private readonly ILogger<HealthCheckService> _logger;
    private readonly IConfiguration _configuration;
    private readonly HttpClient _httpClient;

    public HealthCheckService(
        ILogger<HealthCheckService> logger,
        IConfiguration configuration,
        HttpClient httpClient)
    {
        _logger = logger;
        _configuration = configuration;
        _httpClient = httpClient;
    }

    public async Task<List<ServiceHealth>> GetAllServiceHealthAsync()
    {
        var services = new List<ServiceHealth>();

        // Check all services in the solution
        var serviceEndpoints = new Dictionary<string, string>
        {
            ["api"] = "https+http://downstairs-api/health",
            ["api-gateway"] = "https+http://downstairs-api-gateway/health",
            ["jobs"] = "https+http://downstairs-jobs/health",
            ["admin"] = "https+http://downstairs-admin/health"
        };

        foreach (var service in serviceEndpoints)
        {
            try
            {
                var health = await CheckServiceEndpointAsync(service.Key, service.Value);
                services.Add(health);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Failed to check health for service {ServiceName}", service.Key);
                services.Add(new ServiceHealth
                {
                    ServiceName = service.Key,
                    Status = HealthStatus.Unhealthy,
                    ResponseTime = null,
                    LastChecked = DateTime.UtcNow,
                    ErrorMessage = ex.Message
                });
            }
        }

        // Add infrastructure health checks
        services.Add(await GetInfrastructureHealthAsync("database", CheckDatabaseHealthAsync));
        services.Add(await GetInfrastructureHealthAsync("redis", CheckRedisHealthAsync));
        services.Add(await GetInfrastructureHealthAsync("servicebus", CheckServiceBusHealthAsync));

        return services;
    }

    public async Task<ServiceHealth> GetServiceHealthAsync(string serviceName)
    {
        var allServices = await GetAllServiceHealthAsync();
        return allServices.FirstOrDefault(s => s.ServiceName == serviceName)
            ?? new ServiceHealth
            {
                ServiceName = serviceName,
                Status = HealthStatus.Unhealthy,
                ErrorMessage = "Service not found",
                LastChecked = DateTime.UtcNow
            };
    }

    public async Task<HealthCheckResult> CheckDatabaseHealthAsync()
    {
        try
        {
            // TODO: Implement actual database health check
            // This would typically try to connect to MySQL and run a simple query
            await Task.Delay(50); // Simulate database check

            return HealthCheckResult.Healthy("Database is responding");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Database health check failed");
            return HealthCheckResult.Unhealthy("Database connection failed", ex);
        }
    }

    public async Task<HealthCheckResult> CheckRedisHealthAsync()
    {
        try
        {
            // TODO: Implement actual Redis health check
            // This would typically try to ping Redis
            await Task.Delay(30); // Simulate Redis check

            return HealthCheckResult.Healthy("Redis is responding");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Redis health check failed");
            return HealthCheckResult.Unhealthy("Redis connection failed", ex);
        }
    }

    public async Task<HealthCheckResult> CheckServiceBusHealthAsync()
    {
        try
        {
            // TODO: Implement actual Service Bus health check
            // This would typically try to connect to Azure Service Bus
            await Task.Delay(100); // Simulate Service Bus check

            return HealthCheckResult.Healthy("Service Bus is responding");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Service Bus health check failed");
            return HealthCheckResult.Unhealthy("Service Bus connection failed", ex);
        }
    }

    private async Task<ServiceHealth> CheckServiceEndpointAsync(string serviceName, string healthUrl)
    {
        var stopwatch = System.Diagnostics.Stopwatch.StartNew();

        try
        {
            var response = await _httpClient.GetAsync(healthUrl);
            stopwatch.Stop();

            var status = response.IsSuccessStatusCode ? HealthStatus.Healthy : HealthStatus.Unhealthy;

            return new ServiceHealth
            {
                ServiceName = serviceName,
                Status = status,
                ResponseTime = stopwatch.ElapsedMilliseconds,
                LastChecked = DateTime.UtcNow,
                ErrorMessage = status == HealthStatus.Unhealthy ? $"HTTP {response.StatusCode}" : null
            };
        }
        catch (Exception ex)
        {
            stopwatch.Stop();
            _logger.LogError(ex, "Health check failed for {ServiceName}", serviceName);

            return new ServiceHealth
            {
                ServiceName = serviceName,
                Status = HealthStatus.Unhealthy,
                ResponseTime = stopwatch.ElapsedMilliseconds,
                LastChecked = DateTime.UtcNow,
                ErrorMessage = ex.Message
            };
        }
    }

    private async Task<ServiceHealth> GetInfrastructureHealthAsync(string serviceName, Func<Task<HealthCheckResult>> healthCheck)
    {
        var stopwatch = System.Diagnostics.Stopwatch.StartNew();

        try
        {
            var result = await healthCheck();
            stopwatch.Stop();

            var status = result.Status switch
            {
                Microsoft.Extensions.Diagnostics.HealthChecks.HealthStatus.Healthy => HealthStatus.Healthy,
                Microsoft.Extensions.Diagnostics.HealthChecks.HealthStatus.Degraded => HealthStatus.Degraded,
                _ => HealthStatus.Unhealthy
            };

            return new ServiceHealth
            {
                ServiceName = serviceName,
                Status = status,
                ResponseTime = stopwatch.ElapsedMilliseconds,
                LastChecked = DateTime.UtcNow,
                ErrorMessage = result.Status != Microsoft.Extensions.Diagnostics.HealthChecks.HealthStatus.Healthy
                    ? result.Description : null
            };
        }
        catch (Exception ex)
        {
            stopwatch.Stop();

            return new ServiceHealth
            {
                ServiceName = serviceName,
                Status = HealthStatus.Unhealthy,
                ResponseTime = stopwatch.ElapsedMilliseconds,
                LastChecked = DateTime.UtcNow,
                ErrorMessage = ex.Message
            };
        }
    }
}