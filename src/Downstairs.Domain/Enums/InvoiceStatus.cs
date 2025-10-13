namespace Downstairs.Domain.Enums;

/// <summary>
/// Invoice status enumeration
/// </summary>
public enum InvoiceStatus
{
    Draft = 0,
    Sent = 1,
    Paid = 2,
    Overdue = 3,
    Cancelled = 4
}