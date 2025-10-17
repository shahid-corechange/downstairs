using Downstairs.Application.Common.Constants;
using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.Entities;
using Downstairs.Domain.ValueObjects;

namespace Downstairs.Application.Commands.Customers;

/// <summary>
/// Handler for creating a new customer with cache invalidation
/// </summary>
public class CreateCustomerCommandHandler(
    ICustomerRepository customerRepository,
    IUnitOfWork unitOfWork,
    ICacheService cacheService) : ICommandHandler<CreateCustomerCommand, long>
{
    private readonly ICustomerRepository _customerRepository = customerRepository;
    private readonly IUnitOfWork _unitOfWork = unitOfWork;
    private readonly ICacheService _cacheService = cacheService;

    public async Task<long> Handle(CreateCustomerCommand request, CancellationToken cancellationToken)
    {
        // Check if customer with organization number already exists
        var existingCustomer = await _customerRepository.GetByOrganizationNumberAsync(
            request.OrganizationNumber, cancellationToken);

        if (existingCustomer is not null)
        {
            throw new InvalidOperationException($"Customer with organization number {request.OrganizationNumber} already exists");
        }

        // Create address value object
        var address = new Address(request.Street, request.City, request.PostalCode, request.Country);

        // Create customer entity
        var customer = Customer.Create(
            request.Name,
            request.Email,
            request.OrganizationNumber,
            request.Phone,
            address);

        // Save to repository
        await _customerRepository.AddAsync(customer, cancellationToken);
        await _unitOfWork.SaveChangesAsync(cancellationToken);

        // Invalidate customer-related cache entries
        await _cacheService.RemoveAsync(CacheKeys.AllCustomers, cancellationToken);

        return customer.Id;
    }
}