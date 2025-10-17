using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Customer
{
    public long Id { get; set; }

    public string? FortnoxId { get; set; }

    public long? CustomerRefId { get; set; }

    public long AddressId { get; set; }

    public string MembershipType { get; set; } = null!;

    public string Type { get; set; } = null!;

    public string IdentityNumber { get; set; } = null!;

    public string Name { get; set; } = null!;

    public string Email { get; set; } = null!;

    public string? Phone1 { get; set; }

    public string? DialCode { get; set; }

    public short DueDays { get; set; }

    public string InvoiceMethod { get; set; } = null!;

    public string? Reference { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Address Address { get; set; } = null!;

    public virtual Customer? CustomerRef { get; set; }

    public virtual ICollection<CustomerUser> CustomerUsers { get; set; } = new List<CustomerUser>();

    public virtual ICollection<Customer> InverseCustomerRef { get; set; } = new List<Customer>();

    public virtual ICollection<Invoice> Invoices { get; set; } = new List<Invoice>();

    public virtual ICollection<OldCustomer> OldCustomers { get; set; } = new List<OldCustomer>();

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<UnassignSubscription> UnassignSubscriptions { get; set; } = new List<UnassignSubscription>();
}
