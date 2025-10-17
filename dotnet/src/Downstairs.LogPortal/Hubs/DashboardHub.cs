using Downstairs.LogPortal.Models;
using Microsoft.AspNetCore.SignalR;

namespace Downstairs.LogPortal.Hubs;

/// <summary>
/// SignalR Hub for real-time dashboard updates
/// </summary>
public class DashboardHub : Hub
{
    private readonly ILogger<DashboardHub> _logger;

    public DashboardHub(ILogger<DashboardHub> logger)
    {
        _logger = logger;
    }

    public async Task JoinGroup(string groupName)
    {
        await Groups.AddToGroupAsync(Context.ConnectionId, groupName);
        _logger.LogInformation("Connection {ConnectionId} joined group {GroupName}", Context.ConnectionId, groupName);
    }

    public async Task LeaveGroup(string groupName)
    {
        await Groups.RemoveFromGroupAsync(Context.ConnectionId, groupName);
        _logger.LogInformation("Connection {ConnectionId} left group {GroupName}", Context.ConnectionId, groupName);
    }

    public override async Task OnConnectedAsync()
    {
        _logger.LogInformation("Client connected: {ConnectionId}", Context.ConnectionId);
        await base.OnConnectedAsync();
    }

    public override async Task OnDisconnectedAsync(Exception? exception)
    {
        _logger.LogInformation("Client disconnected: {ConnectionId}, Exception: {Exception}",
            Context.ConnectionId, exception?.Message);
        await base.OnDisconnectedAsync(exception);
    }
}

/// <summary>
/// Service for broadcasting real-time updates to dashboard clients
/// </summary>
public interface IDashboardNotificationService
{
    Task BroadcastMetricsUpdateAsync(BusinessMetrics metrics);
    Task BroadcastHealthUpdateAsync(List<ServiceHealth> healthChecks);
    Task BroadcastNewLogAsync(LogEntry logEntry);
    Task BroadcastAlertAsync(string message, AlertType type);
}

public class DashboardNotificationService : IDashboardNotificationService
{
    private readonly IHubContext<DashboardHub> _hubContext;
    private readonly ILogger<DashboardNotificationService> _logger;

    public DashboardNotificationService(
        IHubContext<DashboardHub> hubContext,
        ILogger<DashboardNotificationService> logger)
    {
        _hubContext = hubContext;
        _logger = logger;
    }

    public async Task BroadcastMetricsUpdateAsync(BusinessMetrics metrics)
    {
        try
        {
            await _hubContext.Clients.All.SendAsync("MetricsUpdate", metrics);
            _logger.LogDebug("Broadcasted metrics update to all clients");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to broadcast metrics update");
        }
    }

    public async Task BroadcastHealthUpdateAsync(List<ServiceHealth> healthChecks)
    {
        try
        {
            await _hubContext.Clients.All.SendAsync("HealthUpdate", healthChecks);
            _logger.LogDebug("Broadcasted health update to all clients");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to broadcast health update");
        }
    }

    public async Task BroadcastNewLogAsync(LogEntry logEntry)
    {
        try
        {
            await _hubContext.Clients.All.SendAsync("NewLog", logEntry);
            _logger.LogDebug("Broadcasted new log entry to all clients");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to broadcast new log entry");
        }
    }

    public async Task BroadcastAlertAsync(string message, AlertType type)
    {
        try
        {
            var alert = new
            {
                Message = message,
                Type = type,
                Timestamp = DateTime.UtcNow
            };
            await _hubContext.Clients.All.SendAsync("NewAlert", alert);
            _logger.LogDebug("Broadcasted alert to all clients: {Message}", message);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to broadcast alert");
        }
    }
}