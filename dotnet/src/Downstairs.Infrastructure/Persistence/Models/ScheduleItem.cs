namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleItem
{
    public long Id { get; set; }

    public long ScheduleId { get; set; }

    public string ItemableType { get; set; } = null!;

    public long ItemableId { get; set; }

    public decimal Price { get; set; }

    public decimal Quantity { get; set; }

    public byte DiscountPercentage { get; set; }

    public string PaymentMethod { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Schedule Schedule { get; set; } = null!;
}