namespace Downstairs.Infrastructure.Persistence.Models;

public partial class MonthlyWorkHour
{
    public long UserId { get; set; }

    public string? FortnoxId { get; set; }

    public string? Fullname { get; set; }

    public int? Month { get; set; }

    public int? Year { get; set; }

    public decimal? TotalWorkHours { get; set; }

    public decimal AdjustmentHours { get; set; }

    public decimal BookingHours { get; set; }

    public long ScheduleCleaningDeviation { get; set; }

    public long ScheduleEmployeeDeviation { get; set; }
}