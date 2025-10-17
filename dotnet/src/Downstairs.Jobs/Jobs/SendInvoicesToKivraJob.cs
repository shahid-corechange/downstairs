using Dapr.Client;
using Downstairs.Application.Queries.Invoices;
using MediatR;
using Quartz;

namespace Downstairs.Jobs.Jobs;

/// <summary>
/// Job that runs at 03:00 to send invoices to Kivra
/// </summary>
[DisallowConcurrentExecution]
public class SendInvoicesToKivraJob : IJob
{
    private readonly IMediator _mediator;
    private readonly DaprClient _daprClient;
    private readonly ILogger<SendInvoicesToKivraJob> _logger;

    public SendInvoicesToKivraJob(
        IMediator mediator,
        DaprClient daprClient,
        ILogger<SendInvoicesToKivraJob> logger)
    {
        _mediator = mediator;
        _daprClient = daprClient;
        _logger = logger;
    }

    public async Task Execute(IJobExecutionContext context)
    {
        _logger.LogInformation("Starting SendInvoicesToKivraJob at {DateTime}", DateTime.UtcNow);

        try
        {
            // Get all invoices that haven't been sent to Kivra
            var invoices = await _mediator.Send(new GetInvoicesQuery());
            var pendingInvoices = invoices.Where(i =>
                i.Status == "Created" || i.Status == "Draft").ToList();

            if (!pendingInvoices.Any())
            {
                _logger.LogInformation("No pending invoices found to send to Kivra");
                return;
            }

            _logger.LogInformation("Found {Count} pending invoices to send to Kivra", pendingInvoices.Count);

            // Process each invoice
            var successCount = 0;
            var failureCount = 0;

            foreach (var invoice in pendingInvoices)
            {
                try
                {
                    // Simulate Kivra integration call
                    await SimulateKivraDelivery(invoice);

                    // Publish success event via Dapr
                    await _daprClient.PublishEventAsync(
                        "pubsub-servicebus",
                        "InvoiceSentToKivra",
                        new
                        {
                            InvoiceId = invoice.Id,
                            InvoiceNumber = invoice.InvoiceNumber,
                            CustomerId = invoice.CustomerId,
                            TotalAmount = invoice.TotalAmount,
                            Currency = invoice.Currency,
                            SentAt = DateTime.UtcNow,
                            KivraDeliveryId = Guid.NewGuid(), // Simulated Kivra delivery ID
                            JobType = "SendInvoiceToKivra"
                        });

                    _logger.LogInformation("Successfully sent invoice {InvoiceId} ({InvoiceNumber}) to Kivra",
                        invoice.Id, invoice.InvoiceNumber);

                    successCount++;
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Failed to send invoice {InvoiceId} ({InvoiceNumber}) to Kivra",
                        invoice.Id, invoice.InvoiceNumber);
                    failureCount++;

                    // Publish failure event
                    await _daprClient.PublishEventAsync(
                        "pubsub-servicebus",
                        "InvoiceKivraDeliveryFailed",
                        new
                        {
                            InvoiceId = invoice.Id,
                            InvoiceNumber = invoice.InvoiceNumber,
                            CustomerId = invoice.CustomerId,
                            Error = ex.Message,
                            FailedAt = DateTime.UtcNow,
                            JobType = "SendInvoiceToKivraFailed"
                        });
                }

                // Add small delay between calls to avoid overwhelming Kivra
                await Task.Delay(TimeSpan.FromSeconds(2));
            }

            _logger.LogInformation("SendInvoicesToKivraJob completed. Success: {SuccessCount}, Failures: {FailureCount}",
                successCount, failureCount);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing SendInvoicesToKivraJob");
            throw; // Let Quartz handle retry logic
        }
    }

    private async Task SimulateKivraDelivery(InvoiceDto invoice)
    {
        // Simulate Kivra API call with realistic delay and occasional failures
        await Task.Delay(TimeSpan.FromMilliseconds(Random.Shared.Next(500, 2000)));

        // Simulate 5% failure rate for demonstration
        if (Random.Shared.NextDouble() < 0.05)
        {
            throw new InvalidOperationException("Simulated Kivra API failure - service temporarily unavailable");
        }

        _logger.LogDebug("Simulated successful Kivra delivery for invoice {InvoiceNumber}", invoice.InvoiceNumber);
    }
}