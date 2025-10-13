using Downstairs.Application.Common.Interfaces;
using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public class GetInvoicesByCustomerQueryHandler : IRequestHandler<GetInvoicesByCustomerQuery, IEnumerable<InvoiceDto>>
{
    private readonly IInvoiceRepository _invoiceRepository;

    public GetInvoicesByCustomerQueryHandler(IInvoiceRepository invoiceRepository)
    {
        _invoiceRepository = invoiceRepository;
    }

    public async Task<IEnumerable<InvoiceDto>> Handle(GetInvoicesByCustomerQuery request, CancellationToken cancellationToken)
    {
        var invoices = await _invoiceRepository.GetByCustomerIdAsync(request.CustomerId);
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