using Dapr.Client;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Dapr;

/// <summary>
/// Service for invoking other services via Dapr service invocation
/// </summary>
public class DaprServiceInvoker : IServiceInvoker
{
    private readonly DaprClient _daprClient;
    private readonly ILogger<DaprServiceInvoker> _logger;

    public DaprServiceInvoker(
        DaprClient daprClient,
        ILogger<DaprServiceInvoker> logger)
    {
        _daprClient = daprClient;
        _logger = logger;
    }

    public async Task<TResponse?> InvokeAsync<TResponse>(
        string serviceName,
        string methodName,
        object? request = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var response = await _daprClient.InvokeMethodAsync<object?, TResponse>(
                serviceName,
                methodName,
                request,
                cancellationToken);

            _logger.LogInformation(
                "Successfully invoked {ServiceName}.{MethodName}",
                serviceName,
                methodName);

            return response;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex,
                "Failed to invoke {ServiceName}.{MethodName}",
                serviceName,
                methodName);
            throw;
        }
    }

    public async Task InvokeAsync(
        string serviceName,
        string methodName,
        object? request = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            await _daprClient.InvokeMethodAsync(
                serviceName,
                methodName,
                request,
                cancellationToken);

            _logger.LogInformation(
                "Successfully invoked {ServiceName}.{MethodName}",
                serviceName,
                methodName);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex,
                "Failed to invoke {ServiceName}.{MethodName}",
                serviceName,
                methodName);
            throw;
        }
    }
}

/// <summary>
/// Interface for service invocation
/// </summary>
public interface IServiceInvoker
{
    Task<TResponse?> InvokeAsync<TResponse>(
        string serviceName,
        string methodName,
        object? request = null,
        CancellationToken cancellationToken = default);

    Task InvokeAsync(
        string serviceName,
        string methodName,
        object? request = null,
        CancellationToken cancellationToken = default);
}