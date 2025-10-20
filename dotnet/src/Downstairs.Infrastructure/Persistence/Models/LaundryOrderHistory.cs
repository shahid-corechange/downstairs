namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LaundryOrderHistory
{
    public long Id { get; set; }

    public long LaundryOrderId { get; set; }

    public string Type { get; set; } = null!;

    public string Note { get; set; } = null!;

    public long CauserId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User Causer { get; set; } = null!;

    public virtual LaundryOrder LaundryOrder { get; set; } = null!;
}