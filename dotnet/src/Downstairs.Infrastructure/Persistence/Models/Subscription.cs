namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Subscription
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long CustomerId { get; set; }

    public long? TeamId { get; set; }

    public long PropertyId { get; set; }

    public long ServiceId { get; set; }

    public long? FixedPriceId { get; set; }

    public short Frequency { get; set; }

    public DateOnly StartAt { get; set; }

    public DateOnly? EndAt { get; set; }

    public TimeOnly StartTimeAt { get; set; }

    public TimeOnly EndTimeAt { get; set; }

    public short Quarters { get; set; }

    public short RefillSequence { get; set; }

    public bool IsPaused { get; set; }

    public bool IsFixed { get; set; }

    public string? Description { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Customer Customer { get; set; } = null!;

    public virtual FixedPrice? FixedPrice { get; set; }

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual Property Property { get; set; } = null!;

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual Service Service { get; set; } = null!;

    public virtual ICollection<SubscriptionProduct> SubscriptionProducts { get; set; } = new List<SubscriptionProduct>();

    public virtual ICollection<SubscriptionStaffDetail> SubscriptionStaffDetails { get; set; } = new List<SubscriptionStaffDetail>();

    public virtual Team? Team { get; set; }

    public virtual User User { get; set; } = null!;
}