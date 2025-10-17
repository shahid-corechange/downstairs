namespace Downstairs.LogPortal.Models;

/// <summary>
/// Represents a log entry from any service
/// </summary>
public class LogEntry
{
    public int Id { get; set; }
    public DateTime Timestamp { get; set; }
    public string ServiceName { get; set; } = string.Empty;
    public LogLevel Level { get; set; }
    public string Message { get; set; } = string.Empty;
    public string? Exception { get; set; }
    public string? StackTrace { get; set; }
    public Dictionary<string, object>? Properties { get; set; }
    public string? CorrelationId { get; set; }
    public string? UserId { get; set; }
}

/// <summary>
/// Business metrics for dashboard
/// </summary>
public class BusinessMetrics
{
    public int CustomersCreatedToday { get; set; }
    public int InvoicesCreatedToday { get; set; }
    public int TotalActiveCustomers { get; set; }
    public int TotalInvoicesThisMonth { get; set; }
    public decimal? AverageInvoiceAmount { get; set; }
    public double JobsSuccessRate { get; set; }
    public double ApiHealthScore { get; set; }
    public DateTime LastUpdated { get; set; }
}

/// <summary>
/// Service health status
/// </summary>
public class ServiceHealth
{
    public string ServiceName { get; set; } = string.Empty;
    public HealthStatus Status { get; set; }
    public string? Description { get; set; }
    public long? ResponseTime { get; set; } // milliseconds
    public DateTime LastChecked { get; set; }
    public string? ErrorMessage { get; set; }
    public List<DependencyHealth> Dependencies { get; set; } = new();
}

/// <summary>
/// Dependency health (database, external APIs, etc.)
/// </summary>
public class DependencyHealth
{
    public string Name { get; set; } = string.Empty;
    public HealthStatus Status { get; set; }
    public string? Description { get; set; }
    public TimeSpan? ResponseTime { get; set; }
}

/// <summary>
/// Performance metrics for API endpoints
/// </summary>
public class ApiMetrics
{
    public string ServiceName { get; set; } = string.Empty;
    public string Endpoint { get; set; } = string.Empty;
    public string Method { get; set; } = string.Empty;
    public int RequestCount { get; set; }
    public double AverageResponseTime { get; set; }
    public double MinResponseTime { get; set; }
    public double MaxResponseTime { get; set; }
    public int ErrorCount { get; set; }
    public double SuccessRate { get; set; }
    public double ErrorRate => RequestCount > 0 ? (double)ErrorCount / RequestCount * 100 : 0;
    public DateTime LastUpdated { get; set; }
}

/// <summary>
/// Job execution metrics
/// </summary>
public class JobMetrics
{
    public int TotalJobsScheduled { get; set; }
    public int JobsExecutedToday { get; set; }
    public int JobsSucceededToday { get; set; }
    public int JobsFailedToday { get; set; }
    public double SuccessRate => JobsExecutedToday > 0 ? (double)JobsSucceededToday / JobsExecutedToday : 0;
    public double AverageExecutionTime { get; set; } // milliseconds
    public DateTime? LastJobExecution { get; set; }
    public DateTime LastUpdated { get; set; }
    public string? LastError { get; set; }
}

/// <summary>
/// Real-time dashboard update
/// </summary>
public class DashboardUpdate
{
    public string Type { get; set; } = string.Empty; // "log", "metric", "health", "alert"
    public object Data { get; set; } = new();
    public DateTime Timestamp { get; set; } = DateTime.UtcNow;
}

/// <summary>
/// Alert configuration and status
/// </summary>
public class AlertRule
{
    public int Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public AlertType Type { get; set; }
    public double Threshold { get; set; }
    public string Condition { get; set; } = string.Empty; // JSON condition
    public bool IsEnabled { get; set; }
    public string[] EmailRecipients { get; set; } = Array.Empty<string>();
    public DateTime? LastTriggered { get; set; }
    public DateTime CreatedAt { get; set; }
    public int TriggerCount { get; set; }
}

public enum HealthStatus
{
    Healthy = 0,
    Degraded = 1,
    Unhealthy = 2,
    Unknown = 3
}

public enum LogLevel
{
    Trace = 0,
    Debug = 1,
    Information = 2,
    Warning = 3,
    Error = 4,
    Critical = 5
}

public enum AlertType
{
    ErrorRate = 0,
    ResponseTime = 1,
    HealthCheck = 2,
    JobFailure = 3,
    BusinessMetric = 4
}