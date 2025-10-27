namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaning
{
    public long Id { get; set; }

    public long? LaundryOrderId { get; set; }

    public string? LaundryType { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual LaundryOrder? LaundryOrder { get; set; }
}