namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleEmployee
{
    public long Id { get; set; }

    public long? ScheduleId { get; set; }

    public long UserId { get; set; }

    public string? ScheduleableType { get; set; }

    public long? ScheduleableId { get; set; }

    public long? WorkHourId { get; set; }

    public decimal? StartLatitude { get; set; }

    public decimal? StartLongitude { get; set; }

    public string? StartIp { get; set; }

    public DateTime? StartAt { get; set; }

    public decimal? EndLatitude { get; set; }

    public decimal? EndLongitude { get; set; }

    public string? EndIp { get; set; }

    public DateTime? EndAt { get; set; }

    public string? Description { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Schedule? Schedule { get; set; }

    public virtual ICollection<Task> Tasks { get; set; } = new List<Task>();

    public virtual ICollection<TimeAdjustment> TimeAdjustments { get; set; } = new List<TimeAdjustment>();

    public virtual User User { get; set; } = null!;

    public virtual WorkHour? WorkHour { get; set; }
}