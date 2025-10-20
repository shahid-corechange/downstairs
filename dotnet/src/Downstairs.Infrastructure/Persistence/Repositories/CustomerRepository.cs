using Downstairs.Application.Common.Interfaces;
using Microsoft.EntityFrameworkCore;
using DomainAddress = Downstairs.Domain.ValueObjects.Address;
using DomainCustomer = Downstairs.Domain.Entities.Customer;
using PersistenceCustomer = Downstairs.Infrastructure.Persistence.Models.Customer;

namespace Downstairs.Infrastructure.Persistence.Repositories;

/// <summary>
/// Repository implementation for domain customers backed by the scaffolded persistence layer.
/// </summary>
internal sealed class CustomerRepository(DownstairsDbContext context) : RepositoryBase<PersistenceCustomer>(context), ICustomerRepository
{
    public async Task<DomainCustomer?> GetByIdAsync(long id, CancellationToken cancellationToken = default)
    {
        if (id < 0)
        {
            return null;
        }

        var entity = await QueryWithAddress().FirstOrDefaultAsync(customer => customer.Id == id, cancellationToken);

        return entity is null ? null : MapToDomain(entity);
    }

    public async Task<DomainCustomer?> GetByOrganizationNumberAsync(string organizationNumber, CancellationToken cancellationToken = default)
    {
        if (string.IsNullOrWhiteSpace(organizationNumber))
        {
            throw new ArgumentException("Organization number must be provided.", nameof(organizationNumber));
        }

        var entity = await QueryWithAddress()
            .FirstOrDefaultAsync(customer => customer.IdentityNumber == organizationNumber, cancellationToken);

        return entity is null ? null : MapToDomain(entity);
    }

    public async Task<IReadOnlyCollection<DomainCustomer>> GetAllAsync(CancellationToken cancellationToken = default)
    {
        var entities = await QueryWithAddress().ToListAsync(cancellationToken);
        return entities.Select(MapToDomain).ToArray();
    }

    public Task AddAsync(DomainCustomer customer, CancellationToken cancellationToken = default)
    {
        throw new NotSupportedException("Persisting domain customers with the scaffolded persistence model is not implemented yet.");
    }

    private IQueryable<PersistenceCustomer> QueryWithAddress()
    {
        return Query()
            .Include(customer => customer.Address)
                .ThenInclude(address => address!.City)
                    .ThenInclude(city => city.Country);
    }

    private static DomainCustomer MapToDomain(PersistenceCustomer entity)
    {
        var addressModel = entity.Address;
        var cityName = addressModel?.City?.Name ?? string.Empty;
        var countryName = addressModel?.City?.Country?.Name ?? string.Empty;
        var street = addressModel?.Address1 ?? string.Empty;
        var postalCode = addressModel?.PostalCode ?? string.Empty;

        var domainAddress = new DomainAddress(
            street,
            cityName,
            postalCode,
            countryName);

        var resolvedAddressId = addressModel?.Id ?? entity.AddressId ?? 0L;

        return DomainCustomer.FromPersistence(
            entity.Id,
            entity.Name,
            entity.Email ?? string.Empty,
            entity.IdentityNumber,
            entity.MembershipType,
            entity.Type,
            entity.Phone1,
            entity.DialCode,
            entity.DueDays,
            entity.InvoiceMethod,
            entity.Reference,
            entity.FortnoxId,
            entity.CustomerRefId,
            resolvedAddressId,
            domainAddress,
            ToDateTimeOffset(entity.CreatedAt),
            ToNullableDateTimeOffset(entity.UpdatedAt),
            ToNullableDateTimeOffset(entity.DeletedAt));
    }

    private static DateTimeOffset ToDateTimeOffset(DateTime? value)
    {
        var dateTime = value ?? DateTime.UtcNow;
        return new DateTimeOffset(DateTime.SpecifyKind(dateTime, DateTimeKind.Utc));
    }

    private static DateTimeOffset? ToNullableDateTimeOffset(DateTime? value)
    {
        return value is null ? null : new DateTimeOffset(DateTime.SpecifyKind(value.Value, DateTimeKind.Utc));
    }
}