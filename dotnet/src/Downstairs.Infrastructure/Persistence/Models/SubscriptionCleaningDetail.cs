namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionCleaningDetail
{
    public long Id { get; set; }

    public long PropertyId { get; set; }

    public long TeamId { get; set; }

    public ushort Quarters { get; set; }

    public TimeOnly StartTime { get; set; }

    public TimeOnly EndTime { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Property Property { get; set; } = null!;

    public virtual Team Team { get; set; } = null!;
}