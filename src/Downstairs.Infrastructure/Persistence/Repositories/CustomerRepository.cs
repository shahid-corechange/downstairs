using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.Entities;
using Downstairs.Infrastructure.Persistence;
using Microsoft.EntityFrameworkCore;

namespace Downstairs.Infrastructure.Persistence.Repositories;

/// <summary>
/// Repository implementation for Customer entity
/// </summary>
public class CustomerRepository : ICustomerRepository
{
    private readonly DownstairsDbContext _context;

    public CustomerRepository(DownstairsDbContext context)
    {
        _context = context;
    }

    public async Task<Customer?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default)
    {
        return await _context.Customers
            .Include(c => c.Invoices)
            .FirstOrDefaultAsync(c => c.Id == id, cancellationToken);
    }

    public async Task<Customer?> GetByOrganizationNumberAsync(string organizationNumber, CancellationToken cancellationToken = default)
    {
        return await _context.Customers
            .FirstOrDefaultAsync(c => c.OrganizationNumber == organizationNumber, cancellationToken);
    }

    public async Task<IEnumerable<Customer>> GetAllAsync(CancellationToken cancellationToken = default)
    {
        return await _context.Customers
            .Where(c => c.IsActive)
            .OrderBy(c => c.Name)
            .ToListAsync(cancellationToken);
    }

    public async Task<IEnumerable<Customer>> SearchAsync(string searchTerm, CancellationToken cancellationToken = default)
    {
        return await _context.Customers
            .Where(c => c.IsActive && 
                       (c.Name.Contains(searchTerm) || 
                        c.Email.Contains(searchTerm) || 
                        c.OrganizationNumber.Contains(searchTerm)))
            .OrderBy(c => c.Name)
            .ToListAsync(cancellationToken);
    }

    public async Task AddAsync(Customer customer, CancellationToken cancellationToken = default)
    {
        await _context.Customers.AddAsync(customer, cancellationToken);
    }

    public Task UpdateAsync(Customer customer, CancellationToken cancellationToken = default)
    {
        _context.Customers.Update(customer);
        return Task.CompletedTask;
    }

    public async Task DeleteAsync(Guid id, CancellationToken cancellationToken = default)
    {
        var customer = await GetByIdAsync(id, cancellationToken);
        if (customer is not null)
        {
            customer.Deactivate(); // Soft delete
        }
    }
}