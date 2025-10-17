using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ActivityLog
{
    public long Id { get; set; }

    public string? LogName { get; set; }

    public string Description { get; set; } = null!;

    public string? SubjectType { get; set; }

    public string? Event { get; set; }

    public long? SubjectId { get; set; }

    public string? CauserType { get; set; }

    public long? CauserId { get; set; }

    public string? Properties { get; set; }

    public Guid? BatchUuid { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}
