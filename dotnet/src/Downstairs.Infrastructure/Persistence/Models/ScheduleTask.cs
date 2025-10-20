namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleTask
{
    public long Id { get; set; }

    public long CustomTaskId { get; set; }

    public long ScheduleId { get; set; }

    public bool IsCompleted { get; set; }

    public virtual CustomTask CustomTask { get; set; } = null!;

    public virtual Schedule Schedule { get; set; } = null!;
}