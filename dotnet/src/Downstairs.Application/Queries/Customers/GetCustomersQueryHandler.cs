using Downstairs.Application.Common.Constants;
using Downstairs.Application.Common.Interfaces;

namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Handler for getting all customers with caching support
/// </summary>
public class GetCustomersQueryHandler(
    ICustomerRepository customerRepository,
    ICacheService cacheService) : IQueryHandler<GetCustomersQuery, IEnumerable<CustomerDto>>
{
    private readonly ICustomerRepository _customerRepository = customerRepository;
    private readonly ICacheService _cacheService = cacheService;

    public async Task<IEnumerable<CustomerDto>> Handle(GetCustomersQuery request, CancellationToken cancellationToken)
    {
        // Try to get from cache first
        var cachedCustomers = await _cacheService.GetAsync<CustomerDto[]>(CacheKeys.AllCustomers, cancellationToken);

        if (cachedCustomers != null)
        {
            return cachedCustomers;
        }

        // If not in cache, get from database
        var customers = await _customerRepository.GetAllAsync(cancellationToken);

        var customerDtos = customers.Select(customer => new CustomerDto(
            customer.Id,
            customer.Name,
            customer.Email,
            customer.OrganizationNumber,
            customer.Phone,
            customer.Address.Street,
            customer.Address.City,
            customer.Address.PostalCode,
            customer.Address.Country,
            customer.FortnoxCustomerNumber,
            customer.IsActive,
            customer.CreatedAt,
            customer.UpdatedAt)).ToArray();

        // Cache the result for 10 minutes
        await _cacheService.SetAsync(CacheKeys.AllCustomers, customerDtos, CacheKeys.MediumCacheDuration, cancellationToken);

        return customerDtos;
    }
}