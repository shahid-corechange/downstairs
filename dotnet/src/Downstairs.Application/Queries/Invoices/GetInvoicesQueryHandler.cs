using Downstairs.Application.Common.Constants;
using Downstairs.Application.Common.Interfaces;
using MediatR;

namespace Downstairs.Application.Queries.Invoices;

public class GetInvoicesQueryHandler(
    IInvoiceRepository invoiceRepository,
    ICacheService cacheService) : IRequestHandler<GetInvoicesQuery, IEnumerable<InvoiceDto>>
{
    private readonly IInvoiceRepository _invoiceRepository = invoiceRepository;
    private readonly ICacheService _cacheService = cacheService;

    public async Task<IEnumerable<InvoiceDto>> Handle(GetInvoicesQuery request, CancellationToken cancellationToken)
    {
        // Try to get from cache first
        var cachedInvoices = await _cacheService.GetAsync<InvoiceDto[]>(CacheKeys.AllInvoices, cancellationToken);

        if (cachedInvoices != null)
        {
            return cachedInvoices;
        }

        // If not in cache, get from database
        var invoices = await _invoiceRepository.GetAllAsync(cancellationToken);
        var invoiceDtos = invoices.Select(i => new InvoiceDto(
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
                l.TotalPrice.Amount)).ToList())).ToArray();

        // Cache the result for 10 minutes
        await _cacheService.SetAsync(CacheKeys.AllInvoices, invoiceDtos, CacheKeys.MediumCacheDuration, cancellationToken);

        return invoiceDtos;
    }
}