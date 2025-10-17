namespace Downstairs.Infrastructure.Persistence.Models;

public partial class UnassignSubscription
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long CustomerId { get; set; }

    public long PropertyId { get; set; }

    public long ServiceId { get; set; }

    public short Frequency { get; set; }

    public DateOnly StartAt { get; set; }

    public DateOnly? EndAt { get; set; }

    public TimeOnly StartTimeAt { get; set; }

    public short Quarters { get; set; }

    public short RefillSequence { get; set; }

    public bool IsFixed { get; set; }

    public string? Description { get; set; }

    public decimal? FixedPrice { get; set; }

    public string? ProductIds { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Customer Customer { get; set; } = null!;

    public virtual Property Property { get; set; } = null!;

    public virtual Service Service { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}