namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionLaundryDetail
{
    public long Id { get; set; }

    public long StoreId { get; set; }

    public long LaundryPreferenceId { get; set; }

    public long? PickupPropertyId { get; set; }

    public long? PickupTeamId { get; set; }

    public TimeOnly? PickupTime { get; set; }

    public long? DeliveryPropertyId { get; set; }

    public long? DeliveryTeamId { get; set; }

    public TimeOnly? DeliveryTime { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Property? DeliveryProperty { get; set; }

    public virtual Team? DeliveryTeam { get; set; }

    public virtual LaundryPreference LaundryPreference { get; set; } = null!;

    public virtual Property? PickupProperty { get; set; }

    public virtual Team? PickupTeam { get; set; }

    public virtual Store Store { get; set; } = null!;
}