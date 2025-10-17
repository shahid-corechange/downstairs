namespace Downstairs.Infrastructure.Persistence.Models;

public partial class TimeAdjustment
{
    public long Id { get; set; }

    public long ScheduleEmployeeId { get; set; }

    public long CauserId { get; set; }

    public sbyte Quarters { get; set; }

    public string Reason { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual User Causer { get; set; } = null!;

    public virtual ScheduleEmployee ScheduleEmployee { get; set; } = null!;
}