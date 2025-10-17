using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public record GetInvoiceByIdQuery(long Id) : IRequest<InvoiceDto?>;