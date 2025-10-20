namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Deviation
{
    public long Id { get; set; }

    public long? ScheduleId { get; set; }

    public long? ScheduleCleaningId { get; set; }

    public long UserId { get; set; }

    public string Type { get; set; } = null!;

    public string? Reason { get; set; }

    public bool IsHandled { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Schedule? Schedule { get; set; }

    public virtual ScheduleCleaning? ScheduleCleaning { get; set; }

    public virtual User User { get; set; } = null!;
}