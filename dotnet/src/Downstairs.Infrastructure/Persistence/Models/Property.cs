namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Property
{
    public long Id { get; set; }

    public long AddressId { get; set; }

    public long PropertyTypeId { get; set; }

    public string MembershipType { get; set; } = null!;

    public decimal SquareMeter { get; set; }

    public string Status { get; set; } = null!;

    public string? KeyInformation { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Address Address { get; set; } = null!;

    public virtual ICollection<KeyPlace> KeyPlaces { get; set; } = new List<KeyPlace>();

    public virtual PropertyType PropertyType { get; set; } = null!;

    public virtual ICollection<PropertyUser> PropertyUsers { get; set; } = new List<PropertyUser>();

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<UnassignSubscription> UnassignSubscriptions { get; set; } = new List<UnassignSubscription>();
}