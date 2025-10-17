using Downstairs.Application.Common.Interfaces;

namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Handler for getting a customer by ID
/// </summary>
public class GetCustomerByIdQueryHandler : IQueryHandler<GetCustomerByIdQuery, CustomerDto?>
{
    private readonly ICustomerRepository _customerRepository;

    public GetCustomerByIdQueryHandler(ICustomerRepository customerRepository)
    {
        _customerRepository = customerRepository;
    }

    public async Task<CustomerDto?> Handle(GetCustomerByIdQuery request, CancellationToken cancellationToken)
    {
        var customer = await _customerRepository.GetByIdAsync(request.Id, cancellationToken);

        if (customer == null)
        {
            return null;
        }

        return new CustomerDto(
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
            customer.UpdatedAt);
    }
}