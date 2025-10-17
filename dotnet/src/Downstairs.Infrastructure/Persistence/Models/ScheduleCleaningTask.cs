using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaningTask
{
    public long Id { get; set; }

    public long CustomTaskId { get; set; }

    public long ScheduleCleaningId { get; set; }

    public bool IsCompleted { get; set; }

    public virtual CustomTask CustomTask { get; set; } = null!;

    public virtual ScheduleCleaning ScheduleCleaning { get; set; } = null!;
}
