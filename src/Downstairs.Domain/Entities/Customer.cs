using Downstairs.Domain.Shared;
using Downstairs.Domain.ValueObjects;
using Downstairs.Domain.Events;

namespace Downstairs.Domain.Entities;

/// <summary>
/// Customer entity representing business customers with Fortnox integration
/// </summary>
public class Customer : Entity<Guid>
{
    public string Name { get; private set; } = string.Empty;
    public string Email { get; private set; } = string.Empty;
    public string OrganizationNumber { get; private set; } = string.Empty;
    public string Phone { get; private set; } = string.Empty;
    public Address Address { get; private set; } = null!;
    public string? FortnoxCustomerNumber { get; private set; }
    public bool IsActive { get; private set; } = true;
    public DateTimeOffset CreatedAt { get; private set; }
    public DateTimeOffset? UpdatedAt { get; private set; }
    
    private readonly List<Invoice> _invoices = [];
    public IReadOnlyCollection<Invoice> Invoices => _invoices.AsReadOnly();

    private Customer() : base() { } // EF Core

    private Customer(
        string name, 
        string email, 
        string organizationNumber, 
        string phone, 
        Address address) : base(Guid.NewGuid())
    {
        Name = name;
        Email = email;
        OrganizationNumber = organizationNumber;
        Phone = phone;
        Address = address;
        CreatedAt = DateTimeOffset.UtcNow;
    }

    public static Customer Create(
        string name,
        string email,
        string organizationNumber,
        string phone,
        Address address)
    {
        var customer = new Customer(name, email, organizationNumber, phone, address);
        
        customer.AddDomainEvent(new CustomerCreatedEvent(
            customer.Id,
            customer.Name,
            customer.Email,
            customer.OrganizationNumber));

        return customer;
    }

    public void UpdateDetails(string name, string email, string phone, Address address)
    {
        Name = name;
        Email = email;
        Phone = phone;
        Address = address;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void SetFortnoxCustomerNumber(string fortnoxCustomerNumber)
    {
        FortnoxCustomerNumber = fortnoxCustomerNumber;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void Deactivate()
    {
        IsActive = false;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void AddInvoice(Invoice invoice)
    {
        _invoices.Add(invoice);
    }
}