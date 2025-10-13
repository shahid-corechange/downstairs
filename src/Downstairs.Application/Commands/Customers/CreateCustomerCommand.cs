using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.ValueObjects;

namespace Downstairs.Application.Commands.Customers;

/// <summary>
/// Command to create a new customer
/// </summary>
public record CreateCustomerCommand(
    string Name,
    string Email,
    string OrganizationNumber,
    string Phone,
    string Street,
    string City,
    string PostalCode,
    string Country) : ICommand<Guid>;