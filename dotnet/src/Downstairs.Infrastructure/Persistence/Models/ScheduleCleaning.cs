using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaning
{
    public long Id { get; set; }

    public long SubscriptionId { get; set; }

    public long TeamId { get; set; }

    public long CustomerId { get; set; }

    public long PropertyId { get; set; }

    public string Status { get; set; } = null!;

    public DateTime StartAt { get; set; }

    public DateTime EndAt { get; set; }

    public DateTime? OriginalStartAt { get; set; }

    public short? Quarters { get; set; }

    public bool IsFixed { get; set; }

    public string? KeyInformation { get; set; }

    public string? Note { get; set; }

    public string? CancelableType { get; set; }

    public long? CancelableId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public DateTime? CanceledAt { get; set; }

    public virtual ICollection<CreditTransaction> CreditTransactions { get; set; } = new List<CreditTransaction>();

    public virtual ICollection<Credit> Credits { get; set; } = new List<Credit>();

    public virtual Customer Customer { get; set; } = null!;

    public virtual ICollection<Deviation> Deviations { get; set; } = new List<Deviation>();

    public virtual Property Property { get; set; } = null!;

    public virtual ICollection<ScheduleCleaningChangeRequest> ScheduleCleaningChangeRequests { get; set; } = new List<ScheduleCleaningChangeRequest>();

    public virtual ICollection<ScheduleCleaningDeviation> ScheduleCleaningDeviations { get; set; } = new List<ScheduleCleaningDeviation>();

    public virtual ICollection<ScheduleCleaningProduct> ScheduleCleaningProducts { get; set; } = new List<ScheduleCleaningProduct>();

    public virtual ICollection<ScheduleCleaningTask> ScheduleCleaningTasks { get; set; } = new List<ScheduleCleaningTask>();

    public virtual Subscription Subscription { get; set; } = null!;

    public virtual Team Team { get; set; } = null!;
}
