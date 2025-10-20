namespace Downstairs.Infrastructure.Persistence.Models;

public partial class WorkHour
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public string? FortnoxAttendanceId { get; set; }

    public string Type { get; set; } = null!;

    public DateOnly Date { get; set; }

    public TimeOnly StartTime { get; set; }

    public TimeOnly EndTime { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual ICollection<CashierAttendance> CashierAttendances { get; set; } = new List<CashierAttendance>();

    public virtual ICollection<ScheduleEmployee> ScheduleEmployees { get; set; } = new List<ScheduleEmployee>();

    public virtual User User { get; set; } = null!;
}