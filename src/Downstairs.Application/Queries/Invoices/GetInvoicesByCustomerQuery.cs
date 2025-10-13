using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public record GetInvoicesByCustomerQuery(Guid CustomerId) : IRequest<IEnumerable<InvoiceDto>>;