using Dapr.Client;
using Downstairs.Application.Commands.Customers;
using Downstairs.Infrastructure.Locking;
using MediatR;
using Quartz;

namespace Downstairs.Jobs.Jobs;

/// <summary>
/// Job that runs at 01:00 to create a dummy customer in Fortnox
/// </summary>
[DisallowConcurrentExecution]
public class CreateFortnoxCustomerJob : IJob
{
    private readonly IMediator _mediator;
    private readonly DaprClient _daprClient;
    private readonly IDistributedLockService _lockService;
    private readonly ILogger<CreateFortnoxCustomerJob> _logger;

    public CreateFortnoxCustomerJob(
        IMediator mediator,
        DaprClient daprClient,
        IDistributedLockService lockService,
        ILogger<CreateFortnoxCustomerJob> logger)
    {
        _mediator = mediator;
        _daprClient = daprClient;
        _lockService = lockService;
        _logger = logger;
    }

    public async Task Execute(IJobExecutionContext context)
    {
        const string lockKey = "job:create-fortnox-customer";
        const int lockTimeoutMinutes = 5;

        _logger.LogInformation("Starting CreateFortnoxCustomerJob at {DateTime}", DateTime.UtcNow);

        // Acquire distributed lock to prevent multiple instances from running the same job
        var distributedLock = await _lockService.AcquireLockAsync(lockKey, TimeSpan.FromMinutes(lockTimeoutMinutes));
        if (distributedLock == null)
        {
            _logger.LogWarning("Could not acquire distributed lock for CreateFortnoxCustomerJob. Another instance may be running.");
            return;
        }

        try
        {
            await using var _ = distributedLock;
            // Create a dummy customer
            var command = new CreateCustomerCommand(
                Name: $"Fortnox Customer {DateTime.UtcNow:yyyy-MM-dd HH:mm}",
                Email: $"fortnox.customer.{DateTime.UtcNow:yyyyMMddHHmm}@example.com",
                OrganizationNumber: $"556{Random.Shared.Next(100000, 999999)}-{Random.Shared.Next(1000, 9999)}",
                Phone: "+46701234567",
                Street: "Testgatan 123",
                City: "Stockholm",
                PostalCode: "12345",
                Country: "Sweden");

            var customerId = await _mediator.Send(command);

            _logger.LogInformation("Successfully created customer {CustomerId} in Fortnox integration", customerId);

            // Publish event via Dapr
            await _daprClient.PublishEventAsync(
                "pubsub-servicebus",
                "FortnoxCustomerCreated",
                new
                {
                    CustomerId = customerId,
                    Name = command.Name,
                    Email = command.Email,
                    CreatedAt = DateTime.UtcNow,
                    JobType = "CreateFortnoxCustomer"
                });

            _logger.LogInformation("Published FortnoxCustomerCreated event for customer {CustomerId}", customerId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing CreateFortnoxCustomerJob");
            throw; // Let Quartz handle retry logic
        }
    }
}