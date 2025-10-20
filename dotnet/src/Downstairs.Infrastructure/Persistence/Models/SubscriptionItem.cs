namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionItem
{
    public long Id { get; set; }

    public long SubscriptionId { get; set; }

    public string ItemableType { get; set; } = null!;

    public long ItemableId { get; set; }

    public ushort Quantity { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Subscription Subscription { get; set; } = null!;
}