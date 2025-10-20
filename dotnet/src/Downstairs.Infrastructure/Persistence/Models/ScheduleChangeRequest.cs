namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleChangeRequest
{
    public long Id { get; set; }

    public long ScheduleId { get; set; }

    public long? CauserId { get; set; }

    public DateTime? OriginalStartAt { get; set; }

    public DateTime StartAtChanged { get; set; }

    public DateTime? OriginalEndAt { get; set; }

    public DateTime EndAtChanged { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User? Causer { get; set; }

    public virtual Schedule Schedule { get; set; } = null!;
}