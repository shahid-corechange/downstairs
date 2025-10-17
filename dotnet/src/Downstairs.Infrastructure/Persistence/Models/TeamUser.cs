namespace Downstairs.Infrastructure.Persistence.Models;

public partial class TeamUser
{
    public long MyRowId { get; set; }

    public long TeamId { get; set; }

    public long UserId { get; set; }

    public virtual Team Team { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}