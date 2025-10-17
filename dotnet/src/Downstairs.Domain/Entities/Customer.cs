using Downstairs.Domain.Events;
using Downstairs.Domain.Shared;
using Downstairs.Domain.ValueObjects;

namespace Downstairs.Domain.Entities;

/// <summary>
/// Customer entity representing business customers with Fortnox integration
/// </summary>
public class Customer : Entity<long>
{
    public string Name { get; private set; } = string.Empty;
    public string Email { get; private set; } = string.Empty;
    public string OrganizationNumber { get; private set; } = string.Empty;
    public string IdentityNumber => OrganizationNumber;
    public string MembershipType { get; private set; } = string.Empty;
    public string Type { get; private set; } = string.Empty;
    public string? Phone1 { get; private set; }
    public string Phone => Phone1 ?? string.Empty;
    public string? DialCode { get; private set; }
    public short DueDays { get; private set; }
    public string InvoiceMethod { get; private set; } = string.Empty;
    public string? Reference { get; private set; }
    public Address Address { get; private set; } = null!;
    public long AddressId { get; private set; }
    public string? FortnoxCustomerNumber { get; private set; }
    public string? FortnoxId { get; private set; }
    public long? CustomerRefId { get; private set; }
    public bool IsActive { get; private set; } = true;
    public DateTimeOffset CreatedAt { get; private set; }
    public DateTimeOffset? UpdatedAt { get; private set; }
    public DateTimeOffset? DeletedAt { get; private set; }

    private readonly List<Invoice> _invoices = [];
    public IReadOnlyCollection<Invoice> Invoices => _invoices.AsReadOnly();

    private Customer() : base() { } // EF Core

    private Customer(
        string name,
        string email,
        string organizationNumber,
        string phone,
        Address address)
    {
        Name = name;
        Email = email;
        SetIdentityNumber(organizationNumber);
        Phone1 = phone;
        ApplyAddress(address, 0);
        MembershipType = "Standard";
        Type = "Business";
        InvoiceMethod = "Standard";
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
            name,
            email,
            organizationNumber));

        return customer;
    }

    public void UpdateDetails(string name, string email, string phone, Address address)
    {
        Name = name;
        Email = email;
        Phone1 = phone;
        ApplyAddress(address, AddressId);
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void SetFortnoxCustomerNumber(string fortnoxCustomerNumber)
    {
        FortnoxCustomerNumber = fortnoxCustomerNumber;
        FortnoxId = fortnoxCustomerNumber;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void Deactivate()
    {
        IsActive = false;
        DeletedAt = DateTimeOffset.UtcNow;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void AddInvoice(Invoice invoice)
    {
        _invoices.Add(invoice);
    }

    internal static Customer FromPersistence(
        long id,
        string name,
        string email,
        string identityNumber,
        string membershipType,
        string type,
        string? phone1,
        string? dialCode,
        short dueDays,
        string invoiceMethod,
        string? reference,
        string? fortnoxId,
        long? customerRefId,
        long addressId,
        Address address,
        DateTimeOffset createdAt,
        DateTimeOffset? updatedAt,
        DateTimeOffset? deletedAt)
    {
        var customer = new Customer
        {
            Id = id,
            Name = name,
            Email = email
        };
        customer.SetIdentityNumber(identityNumber);
        customer.MembershipType = membershipType;
        customer.Type = type;
        customer.Phone1 = phone1;
        customer.DialCode = dialCode;
        customer.DueDays = dueDays;
        customer.InvoiceMethod = invoiceMethod;
        customer.Reference = reference;
        customer.FortnoxId = fortnoxId;
        customer.FortnoxCustomerNumber = fortnoxId;
        customer.CustomerRefId = customerRefId;
        customer.ApplyAddress(address, addressId);
        customer.CreatedAt = createdAt;
        customer.UpdatedAt = updatedAt;
        customer.DeletedAt = deletedAt;
        customer.IsActive = deletedAt is null;

        return customer;
    }

    internal void UpdatePersistenceMetadata(
        string membershipType,
        string type,
        string? phone1,
        string? dialCode,
        short dueDays,
        string invoiceMethod,
        string? reference,
        long addressId)
    {
        MembershipType = membershipType;
        Type = type;
        Phone1 = phone1;
        DialCode = dialCode;
        DueDays = dueDays;
        InvoiceMethod = invoiceMethod;
        Reference = reference;
        AddressId = addressId;
    }

    internal void SetCustomerReference(long? customerRefId)
    {
        CustomerRefId = customerRefId;
    }

    internal void SetFortnoxIdentifiers(string? fortnoxId)
    {
        FortnoxId = fortnoxId;
        FortnoxCustomerNumber = fortnoxId;
    }

    private void ApplyAddress(Address address, long addressId)
    {
        Address = address;
        AddressId = addressId;
    }

    private void SetIdentityNumber(string identityNumber)
    {
        OrganizationNumber = identityNumber;
    }
}