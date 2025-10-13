using Downstairs.Application.Common.Interfaces;

namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Query to get all customers
/// </summary>
public record GetCustomersQuery : IQuery<IEnumerable<CustomerDto>>;