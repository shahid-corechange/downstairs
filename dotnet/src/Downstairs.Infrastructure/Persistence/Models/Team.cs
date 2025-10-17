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

    public virtual ICollection<ScheduleCleaning> ScheduleCleanings { get; set; } = new List<ScheduleCleaning>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<TeamUser> TeamUsers { get; set; } = new List<TeamUser>();
}