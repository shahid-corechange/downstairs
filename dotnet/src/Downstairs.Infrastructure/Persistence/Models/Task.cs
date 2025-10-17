using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Task
{
    public long Id { get; set; }

    public long CustomTaskId { get; set; }

    public long ScheduleEmployeeId { get; set; }

    public string Name { get; set; } = null!;

    public string? Description { get; set; }

    public bool IsCompleted { get; set; }

    public virtual CustomTask CustomTask { get; set; } = null!;

    public virtual ScheduleEmployee ScheduleEmployee { get; set; } = null!;
}
