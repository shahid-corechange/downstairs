using Dapr.Client;
using Downstairs.Application.Commands.Customers;
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
    private readonly ILogger<CreateFortnoxCustomerJob> _logger;

    public CreateFortnoxCustomerJob(
        IMediator mediator,
        DaprClient daprClient,
        ILogger<CreateFortnoxCustomerJob> logger)
    {
        _mediator = mediator;
        _daprClient = daprClient;
        _logger = logger;
    }

    public async Task Execute(IJobExecutionContext context)
    {
        _logger.LogInformation("Starting CreateFortnoxCustomerJob at {DateTime}", DateTime.UtcNow);

        try
        {
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