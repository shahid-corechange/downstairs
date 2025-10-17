using Downstairs.Domain.Entities;

namespace Downstairs.Application.Common.Interfaces;

/// <summary>
/// Repository abstraction for working with <see cref="Customer"/> aggregates.
/// </summary>
public interface ICustomerRepository
{
    /// <summary>
    /// Retrieves a customer by its primary identifier.
    /// </summary>
    Task<Customer?> GetByIdAsync(long id, CancellationToken cancellationToken = default);

    /// <summary>
    /// Retrieves a customer by its organization number if one exists.
    /// </summary>
    Task<Customer?> GetByOrganizationNumberAsync(string organizationNumber, CancellationToken cancellationToken = default);

    /// <summary>
    /// Retrieves all customers.
    /// </summary>
    Task<IReadOnlyCollection<Customer>> GetAllAsync(CancellationToken cancellationToken = default);

    /// <summary>
    /// Adds a new customer to the underlying store.
    /// </summary>
    Task AddAsync(Customer customer, CancellationToken cancellationToken = default);
}
