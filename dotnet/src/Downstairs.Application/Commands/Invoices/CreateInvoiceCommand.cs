using Downstairs.Application.Queries.Invoices;
using MediatR;

namespace Downstairs.Application.Commands.Invoices;

/// <summary>
/// Command to create a new invoice
/// </summary>
public record CreateInvoiceCommand(
    long CustomerId,
    string InvoiceNumber,
    decimal TotalAmount,
    string Currency,
    DateOnly InvoiceDate,
    DateOnly DueDate,
    List<InvoiceLineDto> Lines) : IRequest<long>;