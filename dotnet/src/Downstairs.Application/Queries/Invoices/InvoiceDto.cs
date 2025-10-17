namespace Downstairs.Application.Queries.Invoices;

/// <summary>
/// Data transfer object for invoice information
/// </summary>
public record InvoiceDto(
    long Id,
    long CustomerId,
    string InvoiceNumber,
    decimal TotalAmount,
    string Currency,
    DateOnly InvoiceDate,
    DateOnly DueDate,
    string Status,
    List<InvoiceLineDto> Lines);

/// <summary>
/// Data transfer object for invoice line information
/// </summary>
public record InvoiceLineDto(
    string Description,
    int Quantity,
    decimal UnitPrice,
    string Currency,
    decimal TotalAmount);