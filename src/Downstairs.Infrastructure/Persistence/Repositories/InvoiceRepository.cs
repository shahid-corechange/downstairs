using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.Entities;
using Downstairs.Infrastructure.Persistence;
using Microsoft.EntityFrameworkCore;

namespace Downstairs.Infrastructure.Persistence.Repositories;

/// <summary>
/// Repository implementation for Invoice entity
/// </summary>
public class InvoiceRepository(DownstairsDbContext context) : IInvoiceRepository
{
    private readonly DownstairsDbContext _context = context;

    public async Task<Invoice?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.Invoices
            .Include(i => i.Customer)
            .Include(i => i.Lines)
            .FirstOrDefaultAsync(i => i.Id == id, cancellationToken);
    }

    public async Task<Invoice?> GetByInvoiceNumberAsync(string invoiceNumber, CancellationToken cancellationToken = default)
    {
        return await _context.Invoices
            .Include(i => i.Customer)
            .Include(i => i.Lines)
            .FirstOrDefaultAsync(i => i.InvoiceNumber == invoiceNumber, cancellationToken);
    }

    public async Task<IEnumerable<Invoice>> GetByCustomerIdAsync(Guid customerId, CancellationToken cancellationToken = default)
    {
        return await _context.Invoices
            .Include(i => i.Lines)
            .Where(i => i.CustomerId == customerId)
            .OrderByDescending(i => i.InvoiceDate)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<Invoice>> GetAllAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Invoices
            .Include(i => i.Customer)
            .Include(i => i.Lines)
            .OrderByDescending(i => i.InvoiceDate)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(Invoice invoice, CancellationToken cancellationToken = default)
    {
        await _context.Invoices.AddAsync(invoice, cancellationToken);
    }

    public Task UpdateAsync(Invoice invoice, CancellationToken cancellationToken = default)
    {
        _context.Invoices.Update(invoice);
        return Task.CompletedTask;
    }

    public async Task DeleteAsync(Guid id, CancellationToken cancellationToken = default)
    {
        var invoice = await GetByIdAsync(id, cancellationToken);
        if (invoice is not null)
        {
            _context.Invoices.Remove(invoice);
        }
    }
}