using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaningDeviation
{
    public long Id { get; set; }

    public long ScheduleCleaningId { get; set; }

    public string Types { get; set; } = null!;

    public bool IsHandled { get; set; }

    public string? Meta { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ScheduleCleaning ScheduleCleaning { get; set; } = null!;
}
