namespace Downstairs.Infrastructure.Persistence.Models;

public partial class UnassignSubscription
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long? CustomerId { get; set; }

    public long ServiceId { get; set; }

    public short Frequency { get; set; }

    public DateOnly StartAt { get; set; }

    public DateOnly? EndAt { get; set; }

    public bool IsFixed { get; set; }

    public string? Description { get; set; }

    public decimal? FixedPrice { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public string? AddonIds { get; set; }

    public string? ProductCarts { get; set; }

    public string? CleaningDetail { get; set; }

    public string? LaundryDetail { get; set; }

    public virtual Customer? Customer { get; set; }

    public virtual Service Service { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}