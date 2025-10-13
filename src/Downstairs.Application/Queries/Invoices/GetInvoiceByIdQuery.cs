using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public record GetInvoiceByIdQuery(Guid Id) : IRequest<InvoiceDto?>;