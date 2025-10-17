using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class CustomTask
{
    public long Id { get; set; }

    public string TaskableType { get; set; } = null!;

    public long TaskableId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual ICollection<ScheduleCleaningTask> ScheduleCleaningTasks { get; set; } = new List<ScheduleCleaningTask>();

    public virtual ICollection<Task> Tasks { get; set; } = new List<Task>();
}
