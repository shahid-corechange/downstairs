using Dapr.Client;
using Downstairs.Application.Commands.Customers;
using Downstairs.Application.Commands.Invoices;
using Downstairs.Application.Queries.Customers;
using Downstairs.Application.Queries.Invoices;
using Downstairs.Domain.Shared;
using MediatR;
using Quartz;

namespace Downstairs.Jobs.Jobs;

/// <summary>
/// Job that runs at 02:00 to create a dummy invoice in Fortnox
/// </summary>
[DisallowConcurrentExecution]
public class CreateFortnoxInvoiceJob : IJob
{
    private readonly IMediator _mediator;
    private readonly DaprClient _daprClient;
    private readonly ILogger<CreateFortnoxInvoiceJob> _logger;

    public CreateFortnoxInvoiceJob(
        IMediator mediator,
        DaprClient daprClient,
        ILogger<CreateFortnoxInvoiceJob> logger)
    {
        _mediator = mediator;
        _daprClient = daprClient;
        _logger = logger;
    }

    public async Task Execute(IJobExecutionContext context)
    {
        _logger.LogInformation("Starting CreateFortnoxInvoiceJob at {DateTime}", DateTime.UtcNow);

        try
        {
            // Get a random customer to create invoice for
            var customers = await _mediator.Send(new GetCustomersQuery());
            var customerList = customers.ToList();

            if (!customerList.Any())
            {
                _logger.LogWarning("No customers found to create invoice for. Creating a customer first.");

                // Create a customer first
                var customerCommand = new CreateCustomerCommand(
                    Name: $"Invoice Customer {DateTime.UtcNow:yyyy-MM-dd HH:mm}",
                    Email: $"invoice.customer.{DateTime.UtcNow:yyyyMMddHHmm}@example.com",
                    OrganizationNumber: $"556{Random.Shared.Next(100000, 999999)}-{Random.Shared.Next(1000, 9999)}",
                    Phone: "+46701234568",
                    Street: "Fakturagatan 456",
                    City: "Gothenburg",
                    PostalCode: "41234",
                    Country: "Sweden");

                var customerId = await _mediator.Send(customerCommand);
                customerList.Add(new CustomerDto(customerId, customerCommand.Name, customerCommand.Email, customerCommand.OrganizationNumber, customerCommand.Phone, customerCommand.Street, customerCommand.City, customerCommand.PostalCode, customerCommand.Country, null, true, DateTimeOffset.UtcNow, null));
            }

            var selectedCustomer = customerList[Random.Shared.Next(customerList.Count)];
            var invoiceNumber = $"FTX-{DateTime.UtcNow:yyyyMMdd}-{Random.Shared.Next(1000, 9999)}";

            // Create invoice lines
            var lines = new List<InvoiceLineDto>
            {
                new("Consulting Services - System Integration", 10, 1500.00m, DomainConstants.Currency.SEK, 15000.00m),
                new("License Fee - Monthly", 1, 2500.00m, DomainConstants.Currency.SEK, 2500.00m),
                new("Support & Maintenance", 1, 800.00m, DomainConstants.Currency.SEK, 800.00m)
            };

            var totalAmount = lines.Sum(l => l.TotalAmount);

            // Create invoice command
            var invoiceCommand = new CreateInvoiceCommand(
                CustomerId: selectedCustomer.Id,
                InvoiceNumber: invoiceNumber,
                TotalAmount: totalAmount,
                Currency: DomainConstants.Currency.SEK,
                InvoiceDate: DateOnly.FromDateTime(DateTime.UtcNow.Date),
                DueDate: DateOnly.FromDateTime(DateTime.UtcNow.Date.AddDays(DomainConstants.Invoice.DefaultDueDays)),
                Lines: lines);

            var invoiceId = await _mediator.Send(invoiceCommand);

            _logger.LogInformation("Successfully created invoice {InvoiceId} ({InvoiceNumber}) for customer {CustomerId} in Fortnox integration",
                invoiceId, invoiceNumber, selectedCustomer.Id);

            // Publish event via Dapr
            await _daprClient.PublishEventAsync(
                "pubsub-servicebus",
                "FortnoxInvoiceCreated",
                new
                {
                    InvoiceId = invoiceId,
                    InvoiceNumber = invoiceNumber,
                    CustomerId = selectedCustomer.Id,
                    CustomerName = selectedCustomer.Name,
                    TotalAmount = totalAmount,
                    Currency = DomainConstants.Currency.SEK,
                    CreatedAt = DateTime.UtcNow,
                    JobType = "CreateFortnoxInvoice"
                });

            _logger.LogInformation("Published FortnoxInvoiceCreated event for invoice {InvoiceId}", invoiceId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing CreateFortnoxInvoiceJob");
            throw; // Let Quartz handle retry logic
        }
    }
}