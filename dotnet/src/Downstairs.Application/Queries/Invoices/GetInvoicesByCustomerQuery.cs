using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public record GetInvoicesByCustomerQuery(long CustomerId) : IRequest<IEnumerable<InvoiceDto>>;