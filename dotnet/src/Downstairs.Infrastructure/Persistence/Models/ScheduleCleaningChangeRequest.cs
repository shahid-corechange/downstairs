namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaningChangeRequest
{
    public long Id { get; set; }

    public long ScheduleCleaningId { get; set; }

    public long? CauserId { get; set; }

    public string? OriginalStartAt { get; set; }

    public DateTime? StartAtChanged { get; set; }

    public string? OriginalEndAt { get; set; }

    public DateTime? EndAtChanged { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User? Causer { get; set; }

    public virtual ScheduleCleaning ScheduleCleaning { get; set; } = null!;
}