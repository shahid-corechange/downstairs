using System.Collections.Generic;
using System.Threading;
using System.Threading.Tasks;
using Downstairs.Domain.Entities;

namespace Downstairs.Application.Common.Interfaces;

/// <summary>
/// Repository abstraction for working with <see cref="Invoice"/> aggregates.
/// </summary>
public interface IInvoiceRepository
{
    /// <summary>
    /// Retrieves an invoice by its primary identifier.
    /// </summary>
    Task<Invoice?> GetByIdAsync(long id, CancellationToken cancellationToken = default);

    /// <summary>
    /// Retrieves all invoices associated with a specific customer.
    /// </summary>
    Task<IReadOnlyCollection<Invoice>> GetByCustomerIdAsync(long customerId, CancellationToken cancellationToken = default);

    /// <summary>
    /// Retrieves all invoices.
    /// </summary>
    Task<IReadOnlyCollection<Invoice>> GetAllAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// Retrieves an invoice by its business invoice number.
    /// </summary>
    Task<Invoice?> GetByInvoiceNumberAsync(string invoiceNumber, CancellationToken cancellationToken = default);

    /// <summary>
    /// Adds a new invoice to the underlying store.
    /// </summary>
    Task AddAsync(Invoice invoice, CancellationToken cancellationToken = default);
}
