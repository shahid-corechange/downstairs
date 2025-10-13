using Downstairs.Application.Common.Interfaces;
using Downstairs.Application.Queries.Invoices;
using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public class GetInvoicesQueryHandler : IRequestHandler<GetInvoicesQuery, IEnumerable<InvoiceDto>>
{
    private readonly IInvoiceRepository _invoiceRepository;

    public GetInvoicesQueryHandler(IInvoiceRepository invoiceRepository)
    {
        _invoiceRepository = invoiceRepository;
    }

    public async Task<IEnumerable<InvoiceDto>> Handle(GetInvoicesQuery request, CancellationToken cancellationToken)
    {
        var invoices = await _invoiceRepository.GetAllAsync();
        return invoices.Select(i => new InvoiceDto(
            i.Id,
            i.CustomerId,
            i.InvoiceNumber,
            i.TotalAmount.Amount,
            i.TotalAmount.Currency,
            i.InvoiceDate,
            i.DueDate,
            i.Status.ToString(),
            i.Lines.Select(l => new InvoiceLineDto(
                l.Description,
                l.Quantity,
                l.UnitPrice.Amount,
                l.UnitPrice.Currency,
                l.TotalPrice.Amount)).ToList()));
    }
}