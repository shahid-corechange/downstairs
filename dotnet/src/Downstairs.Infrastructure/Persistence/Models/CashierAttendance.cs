namespace Downstairs.Infrastructure.Persistence.Models;

public partial class CashierAttendance
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long StoreId { get; set; }

    public long? WorkHourId { get; set; }

    public DateTime CheckInAt { get; set; }

    public long? CheckInCauserId { get; set; }

    public DateTime? CheckOutAt { get; set; }

    public long? CheckOutCauserId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User? CheckInCauser { get; set; }

    public virtual User? CheckOutCauser { get; set; }

    public virtual Store Store { get; set; } = null!;

    public virtual User User { get; set; } = null!;

    public virtual WorkHour? WorkHour { get; set; }
}