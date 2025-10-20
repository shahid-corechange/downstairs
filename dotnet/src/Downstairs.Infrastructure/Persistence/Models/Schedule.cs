namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Schedule
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long ServiceId { get; set; }

    public long? TeamId { get; set; }

    public long? CustomerId { get; set; }

    public long PropertyId { get; set; }

    public long? SubscriptionId { get; set; }

    public string ScheduleableType { get; set; } = null!;

    public long ScheduleableId { get; set; }

    public string Status { get; set; } = null!;

    public DateTime StartAt { get; set; }

    public DateTime EndAt { get; set; }

    public DateTime? OriginalStartAt { get; set; }

    public short Quarters { get; set; }

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

    public virtual Customer? Customer { get; set; }

    public virtual ICollection<Deviation> Deviations { get; set; } = new List<Deviation>();

    public virtual Property Property { get; set; } = null!;

    public virtual ICollection<ScheduleChangeRequest> ScheduleChangeRequests { get; set; } = new List<ScheduleChangeRequest>();

    public virtual ICollection<ScheduleDeviation> ScheduleDeviations { get; set; } = new List<ScheduleDeviation>();

    public virtual ICollection<ScheduleEmployee> ScheduleEmployees { get; set; } = new List<ScheduleEmployee>();

    public virtual ICollection<ScheduleItem> ScheduleItems { get; set; } = new List<ScheduleItem>();

    public virtual ICollection<ScheduleTask> ScheduleTasks { get; set; } = new List<ScheduleTask>();

    public virtual Service Service { get; set; } = null!;

    public virtual Subscription? Subscription { get; set; }

    public virtual Team? Team { get; set; }

    public virtual User User { get; set; } = null!;
}