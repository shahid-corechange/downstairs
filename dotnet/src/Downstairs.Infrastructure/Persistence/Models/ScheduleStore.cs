using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleStore
{
    public long Id { get; set; }

    public int DistrictId { get; set; }

    public int UserId { get; set; }

    public int ContactId { get; set; }

    public string Status { get; set; } = null!;

    public DateTime StartAt { get; set; }

    public DateTime EndAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}
