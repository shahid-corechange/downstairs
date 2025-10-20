namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleLaundry
{
    public long Id { get; set; }

    public long LaundryOrderId { get; set; }

    public string Type { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual LaundryOrder LaundryOrder { get; set; } = null!;
}