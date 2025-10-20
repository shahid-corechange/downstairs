namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Team
{
    public long Id { get; set; }

    public string Name { get; set; } = null!;

    public string? Avatar { get; set; }

    public string? Color { get; set; }

    public string? Description { get; set; }

    public bool? IsActive { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<LaundryOrder> LaundryOrderDeliveryTeams { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<LaundryOrder> LaundryOrderPickupTeams { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual ICollection<Schedule> Schedules { get; set; } = new List<Schedule>();

    public virtual ICollection<SubscriptionCleaningDetail> SubscriptionCleaningDetails { get; set; } = new List<SubscriptionCleaningDetail>();

    public virtual ICollection<SubscriptionLaundryDetail> SubscriptionLaundryDetailDeliveryTeams { get; set; } = new List<SubscriptionLaundryDetail>();

    public virtual ICollection<SubscriptionLaundryDetail> SubscriptionLaundryDetailPickupTeams { get; set; } = new List<SubscriptionLaundryDetail>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<TeamUser> TeamUsers { get; set; } = new List<TeamUser>();
}