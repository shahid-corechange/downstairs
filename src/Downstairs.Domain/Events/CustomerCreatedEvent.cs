using Downstairs.Domain.Shared;

namespace Downstairs.Domain.Events;

/// <summary>
/// Domain event raised when a new customer is created
/// </summary>
public record CustomerCreatedEvent(
    Guid CustomerId,
    string Name,
    string Email,
    string OrganizationNumber) : DomainEvent;