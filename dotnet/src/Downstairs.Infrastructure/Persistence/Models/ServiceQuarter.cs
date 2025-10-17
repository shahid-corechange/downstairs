using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ServiceQuarter
{
    public long Id { get; set; }

    public long ServiceId { get; set; }

    public uint MinSquareMeters { get; set; }

    public uint MaxSquareMeters { get; set; }

    public uint Quarters { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Service Service { get; set; } = null!;
}
