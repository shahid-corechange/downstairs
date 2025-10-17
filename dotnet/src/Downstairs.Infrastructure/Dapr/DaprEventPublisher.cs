using Dapr.Client;
using Downstairs.Domain.Shared;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Dapr;

/// <summary>
/// Service for publishing domain events via Dapr pub/sub
/// </summary>
public class DaprEventPublisher : IEventPublisher
{
    private readonly DaprClient _daprClient;
    private readonly ILogger<DaprEventPublisher> _logger;

    public DaprEventPublisher(
        DaprClient daprClient,
        ILogger<DaprEventPublisher> logger)
    {
        _daprClient = daprClient;
        _logger = logger;
    }

    public async Task PublishAsync<T>(T domainEvent, CancellationToken cancellationToken = default)
        where T : DomainEvent
    {
        var eventName = typeof(T).Name;

        try
        {
            // Use Redis pub/sub for development, ServiceBus for production
            var pubsubComponent = Environment.GetEnvironmentVariable("ASPNETCORE_ENVIRONMENT") == "Development"
                ? "pubsub"
                : "pubsub-servicebus";

            await _daprClient.PublishEventAsync(
                pubsubComponent,
                eventName,
                domainEvent,
                cancellationToken);

            _logger.LogInformation(
                "Published domain event {EventType} with ID {EventId}",
                eventName,
                domainEvent.EventId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex,
                "Failed to publish domain event {EventType} with ID {EventId}",
                eventName,
                domainEvent.EventId);
            throw;
        }
    }

    public async Task PublishBatchAsync<T>(IEnumerable<T> domainEvents, CancellationToken cancellationToken = default)
        where T : DomainEvent
    {
        var tasks = domainEvents.Select(evt => PublishAsync(evt, cancellationToken));
        await Task.WhenAll(tasks);
    }
}

/// <summary>
/// Interface for publishing domain events
/// </summary>
public interface IEventPublisher
{
    Task PublishAsync<T>(T domainEvent, CancellationToken cancellationToken = default) where T : DomainEvent;
    Task PublishBatchAsync<T>(IEnumerable<T> domainEvents, CancellationToken cancellationToken = default) where T : DomainEvent;
}