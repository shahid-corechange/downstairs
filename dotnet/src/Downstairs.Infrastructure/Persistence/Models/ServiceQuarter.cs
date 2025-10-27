namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ServiceQuarter
{
    public long Id { get; set; }

    public long ServiceId { get; set; }

    public long MinSquareMeters { get; set; }

    public long MaxSquareMeters { get; set; }

    public long Quarters { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Service Service { get; set; } = null!;
}