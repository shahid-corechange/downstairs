namespace Downstairs.Application.Queries.Customers;

/// <summary>
/// Data transfer object for customer information
/// </summary>
public record CustomerDto(
    Guid Id,
    string Name,
    string Email,
    string OrganizationNumber,
    string Phone,
    string Street,
    string City,
    string PostalCode,
    string Country,
    string? FortnoxCustomerNumber,
    bool IsActive,
    DateTimeOffset CreatedAt,
    DateTimeOffset? UpdatedAt);