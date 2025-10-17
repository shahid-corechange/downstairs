using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Service
{
    public long Id { get; set; }

    public string? FortnoxArticleId { get; set; }

    public string Type { get; set; } = null!;

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public bool HasRut { get; set; }

    public string? ThumbnailImage { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual ICollection<Product> Products { get; set; } = new List<Product>();

    public virtual ICollection<ServiceQuarter> ServiceQuarters { get; set; } = new List<ServiceQuarter>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<UnassignSubscription> UnassignSubscriptions { get; set; } = new List<UnassignSubscription>();
}
