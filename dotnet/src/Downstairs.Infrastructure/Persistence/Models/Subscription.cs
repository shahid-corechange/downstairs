namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Subscription
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long? CustomerId { get; set; }

    public long ServiceId { get; set; }

    public long? FixedPriceId { get; set; }

    public string SubscribableType { get; set; } = null!;

    public long SubscribableId { get; set; }

    public short Frequency { get; set; }

    public DateOnly StartAt { get; set; }

    public DateOnly? EndAt { get; set; }

    public bool IsPaused { get; set; }

    public bool IsFixed { get; set; }

    public string? Description { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Customer? Customer { get; set; }

    public virtual FixedPrice? FixedPrice { get; set; }

    public virtual ICollection<LaundryOrder> LaundryOrders { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual ICollection<Schedule> Schedules { get; set; } = new List<Schedule>();

    public virtual Service Service { get; set; } = null!;

    public virtual ICollection<SubscriptionItem> SubscriptionItems { get; set; } = new List<SubscriptionItem>();

    public virtual ICollection<SubscriptionStaffDetail> SubscriptionStaffDetails { get; set; } = new List<SubscriptionStaffDetail>();

    public virtual User User { get; set; } = null!;
}