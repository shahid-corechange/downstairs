namespace Downstairs.Infrastructure.Persistence.Models;

public partial class BlockDay
{
    public long Id { get; set; }

    public DateOnly BlockDate { get; set; }

    public TimeOnly? StartBlockTime { get; set; }

    public TimeOnly? EndBlockTime { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}