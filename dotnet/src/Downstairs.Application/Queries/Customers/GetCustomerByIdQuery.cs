using Downstairs.Application.Common.Interfaces;

namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Query to get a customer by ID
/// </summary>
public record GetCustomerByIdQuery(long Id) : IQuery<CustomerDto?>;