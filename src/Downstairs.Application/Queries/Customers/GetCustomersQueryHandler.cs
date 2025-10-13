using Downstairs.Application.Common.Interfaces;
using Downstairs.Application.Queries.Customers;

namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Handler for getting all customers
/// </summary>
public class GetCustomersQueryHandler : IQueryHandler<GetCustomersQuery, IEnumerable<CustomerDto>>
{
    private readonly ICustomerRepository _customerRepository;

    public GetCustomersQueryHandler(ICustomerRepository customerRepository)
    {
        _customerRepository = customerRepository;
    }

    public async Task<IEnumerable<CustomerDto>> Handle(GetCustomersQuery request, CancellationToken cancellationToken)
    {
        var customers = await _customerRepository.GetAllAsync(cancellationToken);

        return customers.Select(customer => new CustomerDto(
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
            customer.UpdatedAt));
    }
}