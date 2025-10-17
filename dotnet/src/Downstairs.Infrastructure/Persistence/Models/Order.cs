using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Order
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long CustomerId { get; set; }

    public long? ServiceId { get; set; }

    public long? SubscriptionId { get; set; }

    public long? InvoiceId { get; set; }

    public long? OrderFixedPriceId { get; set; }

    public string OrderableType { get; set; } = null!;

    public long OrderableId { get; set; }

    public string Status { get; set; } = null!;

    public string PaidBy { get; set; } = null!;

    public DateTime? PaidAt { get; set; }

    public DateTime OrderedAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Customer Customer { get; set; } = null!;

    public virtual Invoice? Invoice { get; set; }

    public virtual OrderFixedPrice? OrderFixedPrice { get; set; }

    public virtual ICollection<OrderRow> OrderRows { get; set; } = new List<OrderRow>();

    public virtual Service? Service { get; set; }

    public virtual Subscription? Subscription { get; set; }

    public virtual User User { get; set; } = null!;
}
