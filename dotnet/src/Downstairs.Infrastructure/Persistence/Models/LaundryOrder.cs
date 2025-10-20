namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LaundryOrder
{
    public long Id { get; set; }

    public long StoreId { get; set; }

    public long UserId { get; set; }

    public long CauserId { get; set; }

    public long LaundryPreferenceId { get; set; }

    public long? SubscriptionId { get; set; }

    public long CustomerId { get; set; }

    public long? PickupPropertyId { get; set; }

    public long? PickupTeamId { get; set; }

    public TimeOnly? PickupTime { get; set; }

    public long? DeliveryPropertyId { get; set; }

    public long? DeliveryTeamId { get; set; }

    public TimeOnly? DeliveryTime { get; set; }

    public string Status { get; set; } = null!;

    public string? PaymentMethod { get; set; }

    public DateTime OrderedAt { get; set; }

    public DateTime? PaidAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User Causer { get; set; } = null!;

    public virtual Customer Customer { get; set; } = null!;

    public virtual Property? DeliveryProperty { get; set; }

    public virtual Team? DeliveryTeam { get; set; }

    public virtual ICollection<LaundryOrderHistory> LaundryOrderHistories { get; set; } = new List<LaundryOrderHistory>();

    public virtual ICollection<LaundryOrderProduct> LaundryOrderProducts { get; set; } = new List<LaundryOrderProduct>();

    public virtual LaundryPreference LaundryPreference { get; set; } = null!;

    public virtual Property? PickupProperty { get; set; }

    public virtual Team? PickupTeam { get; set; }

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual ICollection<ScheduleLaundry> ScheduleLaundries { get; set; } = new List<ScheduleLaundry>();

    public virtual Store Store { get; set; } = null!;

    public virtual Subscription? Subscription { get; set; }

    public virtual User User { get; set; } = null!;
}