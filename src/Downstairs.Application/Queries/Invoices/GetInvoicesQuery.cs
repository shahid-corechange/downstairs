using Downstairs.Application.Common.Interfaces;
using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public record GetInvoicesQuery() : IRequest<IEnumerable<InvoiceDto>>;