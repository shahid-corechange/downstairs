using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleStoreDetail
{
    public long Id { get; set; }

    public int ScheduleStoreId { get; set; }

    public DateTime? BeginsAtChanged { get; set; }

    public DateTime? EndsAtChanged { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}
