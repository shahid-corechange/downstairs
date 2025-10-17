using Downstairs.Domain.Shared;

namespace Downstairs.Domain.Events;

/// <summary>
/// Domain event raised when a new invoice is created
/// </summary>
public record InvoiceCreatedEvent(
    long InvoiceId,
    long CustomerId,
    string InvoiceNumber,
    decimal Amount) : DomainEvent;