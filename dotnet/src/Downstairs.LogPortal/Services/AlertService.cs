using Downstairs.LogPortal.Models;
using LogLevel = Downstairs.LogPortal.Models.LogLevel;

namespace Downstairs.LogPortal.Services;

/// <summary>
/// Service for managing alerts and notifications
/// </summary>
public interface IAlertService
{
    Task<List<AlertRule>> GetAlertRulesAsync();
    Task<AlertRule> CreateAlertRuleAsync(AlertRule rule);
    Task<AlertRule> UpdateAlertRuleAsync(AlertRule rule);
    Task DeleteAlertRuleAsync(int id);
    Task CheckAlertsAsync();
    Task SendAlertAsync(string subject, string message, AlertType type);
}

public class AlertService : IAlertService
{
    private readonly ILogger<AlertService> _logger;
    private readonly IConfiguration _configuration;
    private readonly ILogService _logService;
    private readonly IHealthCheckService _healthCheckService;
    private readonly IMetricsService _metricsService;
    private static readonly List<AlertRule> _alertRules = new();

    public AlertService(
        ILogger<AlertService> logger,
        IConfiguration configuration,
        ILogService logService,
        IHealthCheckService healthCheckService,
        IMetricsService metricsService)
    {
        _logger = logger;
        _configuration = configuration;
        _logService = logService;
        _healthCheckService = healthCheckService;
        _metricsService = metricsService;

        // Initialize default alert rules
        InitializeDefaultAlertRules();
    }

    public async Task<List<AlertRule>> GetAlertRulesAsync()
    {
        await Task.CompletedTask;
        return _alertRules.ToList();
    }

    public async Task<AlertRule> CreateAlertRuleAsync(AlertRule rule)
    {
        await Task.CompletedTask;

        rule.Id = _alertRules.Count > 0 ? _alertRules.Max(r => r.Id) + 1 : 1;
        rule.CreatedAt = DateTime.UtcNow;

        _alertRules.Add(rule);
        _logger.LogInformation("Created alert rule: {RuleName}", rule.Name);

        return rule;
    }

    public async Task<AlertRule> UpdateAlertRuleAsync(AlertRule rule)
    {
        await Task.CompletedTask;

        var existingRule = _alertRules.FirstOrDefault(r => r.Id == rule.Id);
        if (existingRule == null)
        {
            throw new ArgumentException($"Alert rule with ID {rule.Id} not found");
        }

        var index = _alertRules.IndexOf(existingRule);
        rule.CreatedAt = existingRule.CreatedAt;
        _alertRules[index] = rule;

        _logger.LogInformation("Updated alert rule: {RuleName}", rule.Name);
        return rule;
    }

    public async Task DeleteAlertRuleAsync(int id)
    {
        await Task.CompletedTask;

        var rule = _alertRules.FirstOrDefault(r => r.Id == id);
        if (rule != null)
        {
            _alertRules.Remove(rule);
            _logger.LogInformation("Deleted alert rule: {RuleName}", rule.Name);
        }
    }

    public async Task CheckAlertsAsync()
    {
        _logger.LogDebug("Checking alert conditions...");

        foreach (var rule in _alertRules.Where(r => r.IsEnabled))
        {
            try
            {
                var shouldAlert = await EvaluateAlertConditionAsync(rule);

                if (shouldAlert && ShouldSendAlert(rule))
                {
                    await SendAlertAsync(
                        $"Alert: {rule.Name}",
                        $"Alert condition met: {rule.Description}",
                        rule.Type
                    );

                    rule.LastTriggered = DateTime.UtcNow;
                    _logger.LogWarning("Alert triggered: {RuleName}", rule.Name);
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error evaluating alert rule: {RuleName}", rule.Name);
            }
        }
    }

    public async Task SendAlertAsync(string subject, string message, AlertType type)
    {
        try
        {
            // TODO: Implement actual email sending
            // This is a placeholder implementation
            _logger.LogWarning("ALERT [{AlertType}]: {Subject} - {Message}", type, subject, message);

            // In a real implementation, you would:
            // 1. Configure SMTP settings
            // 2. Send emails to configured recipients
            // 3. Potentially integrate with Slack, Teams, or other notification systems

            var emailEnabled = _configuration.GetValue<bool>("Alerts:EmailEnabled", false);
            if (emailEnabled)
            {
                await SendEmailAlertAsync(subject, message);
            }

            await Task.CompletedTask;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to send alert: {Subject}", subject);
        }
    }

    private async Task<bool> EvaluateAlertConditionAsync(AlertRule rule)
    {
        return rule.Type switch
        {
            AlertType.ErrorRate => await CheckErrorRateAsync(rule),
            AlertType.ResponseTime => await CheckResponseTimeAsync(rule),
            AlertType.HealthCheck => await CheckHealthStatusAsync(rule),
            AlertType.JobFailure => await CheckJobFailureAsync(rule),
            _ => false
        };
    }

    private async Task<bool> CheckErrorRateAsync(AlertRule rule)
    {
        // Check if error rate exceeds threshold
        var logs = await _logService.GetLogsAsync(DateTime.UtcNow.AddHours(-1), DateTime.UtcNow);
        var errorLogs = logs.Where(l => l.Level >= LogLevel.Error).Count();
        var totalLogs = logs.Count;

        if (totalLogs == 0)
        {
            return false;
        }

        var errorRate = (double)errorLogs / totalLogs;
        return errorRate > rule.Threshold;
    }

    private async Task<bool> CheckResponseTimeAsync(AlertRule rule)
    {
        // Check if average response time exceeds threshold
        var apiMetrics = await _metricsService.GetAllApiMetricsAsync();
        var avgResponseTime = apiMetrics.Average(m => m.AverageResponseTime);

        return avgResponseTime > rule.Threshold;
    }

    private async Task<bool> CheckHealthStatusAsync(AlertRule rule)
    {
        // Check if any service is unhealthy
        var healthChecks = await _healthCheckService.GetAllServiceHealthAsync();
        return healthChecks.Any(h => h.Status == HealthStatus.Unhealthy);
    }

    private async Task<bool> CheckJobFailureAsync(AlertRule rule)
    {
        // Check job failure rate
        var jobMetrics = await _metricsService.GetJobMetricsAsync();
        if (jobMetrics.JobsExecutedToday == 0)
        {
            return false;
        }

        var failureRate = (double)jobMetrics.JobsFailedToday / jobMetrics.JobsExecutedToday;
        return failureRate > rule.Threshold;
    }

    private bool ShouldSendAlert(AlertRule rule)
    {
        // Implement cooldown period to prevent spam
        if (rule.LastTriggered.HasValue)
        {
            var timeSinceLastAlert = DateTime.UtcNow - rule.LastTriggered.Value;
            return timeSinceLastAlert > TimeSpan.FromMinutes(30); // 30-minute cooldown
        }

        return true;
    }

    private async Task SendEmailAlertAsync(string subject, string message)
    {
        // TODO: Implement actual SMTP email sending
        // This is a placeholder for email integration
        var smtpServer = _configuration["Alerts:SmtpServer"];
        var smtpPort = _configuration.GetValue<int>("Alerts:SmtpPort", 587);
        var smtpUsername = _configuration["Alerts:SmtpUsername"];
        var smtpPassword = _configuration["Alerts:SmtpPassword"];
        var recipients = _configuration.GetSection("Alerts:Recipients").Get<string[]>() ?? Array.Empty<string>();

        if (string.IsNullOrEmpty(smtpServer) || !recipients.Any())
        {
            _logger.LogWarning("Email alerting not configured properly");
            return;
        }

        _logger.LogInformation("Would send email alert to {Recipients}: {Subject}",
            string.Join(", ", recipients), subject);

        await Task.CompletedTask;
    }

    private void InitializeDefaultAlertRules()
    {
        if (_alertRules.Any())
        {
            return;
        }

        _alertRules.AddRange(new[]
        {
            new AlertRule
            {
                Id = 1,
                Name = "High Error Rate",
                Description = "Alert when error rate exceeds 10%",
                Type = AlertType.ErrorRate,
                Threshold = 0.1,
                IsEnabled = true,
                CreatedAt = DateTime.UtcNow
            },
            new AlertRule
            {
                Id = 2,
                Name = "Slow API Response",
                Description = "Alert when average response time exceeds 2 seconds",
                Type = AlertType.ResponseTime,
                Threshold = 2000,
                IsEnabled = true,
                CreatedAt = DateTime.UtcNow
            },
            new AlertRule
            {
                Id = 3,
                Name = "Service Health Check Failure",
                Description = "Alert when any service health check fails",
                Type = AlertType.HealthCheck,
                Threshold = 0,
                IsEnabled = true,
                CreatedAt = DateTime.UtcNow
            },
            new AlertRule
            {
                Id = 4,
                Name = "Job Failure Rate",
                Description = "Alert when job failure rate exceeds 20%",
                Type = AlertType.JobFailure,
                Threshold = 0.2,
                IsEnabled = true,
                CreatedAt = DateTime.UtcNow
            }
        });

        _logger.LogInformation("Initialized {Count} default alert rules", _alertRules.Count);
    }
}