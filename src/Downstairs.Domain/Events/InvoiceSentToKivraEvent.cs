using Downstairs.Domain.Shared;

namespace Downstairs.Domain.Events;

/// <summary>
/// Domain event raised when an invoice is sent to Kivra
/// </summary>
public record InvoiceSentToKivraEvent(
    Guid InvoiceId,
    Guid CustomerId,
    string InvoiceNumber) : DomainEvent;