using Downstairs.LogPortal.Services;
using Downstairs.LogPortal.Hubs;

namespace Downstairs.LogPortal.BackgroundServices;

/// <summary>
/// Background service that refreshes dashboard data every 30 seconds as requested
/// </summary>
public class DashboardRefreshService : BackgroundService
{
    private readonly IServiceProvider _serviceProvider;
    private readonly ILogger<DashboardRefreshService> _logger;
    private readonly TimeSpan _refreshInterval = TimeSpan.FromSeconds(30);

    public DashboardRefreshService(
        IServiceProvider serviceProvider,
        ILogger<DashboardRefreshService> logger)
    {
        _serviceProvider = serviceProvider;
        _logger = logger;
    }

    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        _logger.LogInformation("Dashboard refresh service started with {Interval} interval", _refreshInterval);

        while (!stoppingToken.IsCancellationRequested)
        {
            try
            {
                using var scope = _serviceProvider.CreateScope();
                
                var metricsService = scope.ServiceProvider.GetRequiredService<IMetricsService>();
                var healthService = scope.ServiceProvider.GetRequiredService<IHealthCheckService>();
                var alertService = scope.ServiceProvider.GetRequiredService<IAlertService>();
                var notificationService = scope.ServiceProvider.GetRequiredService<IDashboardNotificationService>();

                // Refresh business metrics
                var metrics = await metricsService.GetBusinessMetricsAsync();
                await notificationService.BroadcastMetricsUpdateAsync(metrics);

                // Refresh health checks
                var healthChecks = await healthService.GetAllServiceHealthAsync();
                await notificationService.BroadcastHealthUpdateAsync(healthChecks);

                // Check alert conditions
                await alertService.CheckAlertsAsync();

                _logger.LogDebug("Dashboard data refreshed successfully");
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error occurred while refreshing dashboard data");
            }

            try
            {
                await Task.Delay(_refreshInterval, stoppingToken);
            }
            catch (TaskCanceledException)
            {
                // Expected when cancellation is requested
                break;
            }
        }

        _logger.LogInformation("Dashboard refresh service stopped");
    }
}