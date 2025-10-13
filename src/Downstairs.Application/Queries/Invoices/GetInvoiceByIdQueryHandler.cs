using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.ValueObjects;
using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public class GetInvoiceByIdQueryHandler : IRequestHandler<GetInvoiceByIdQuery, InvoiceDto?>
{
    private readonly IInvoiceRepository _invoiceRepository;

    public GetInvoiceByIdQueryHandler(IInvoiceRepository invoiceRepository)
    {
        _invoiceRepository = invoiceRepository;
    }

    public async Task<InvoiceDto?> Handle(GetInvoiceByIdQuery request, CancellationToken cancellationToken)
    {
        var invoice = await _invoiceRepository.GetByIdAsync(request.Id);
        
        if (invoice == null)
        {
            return null;
        }

        return new InvoiceDto(
            invoice.Id,
            invoice.CustomerId,
            invoice.InvoiceNumber,
            invoice.TotalAmount.Amount,
            invoice.TotalAmount.Currency,
            invoice.InvoiceDate,
            invoice.DueDate,
            invoice.Status.ToString(),
            invoice.Lines.Select(l => new InvoiceLineDto(
                l.Description,
                l.Quantity,
                l.UnitPrice.Amount,
                l.UnitPrice.Currency,
                l.TotalPrice.Amount)).ToList());
    }
}