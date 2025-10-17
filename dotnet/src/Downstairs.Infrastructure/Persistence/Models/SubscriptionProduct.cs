namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionProduct
{
    public long Id { get; set; }

    public long SubscriptionId { get; set; }

    public long ProductId { get; set; }

    public int? Quantity { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Product Product { get; set; } = null!;

    public virtual Subscription Subscription { get; set; } = null!;
}