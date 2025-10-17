namespace Downstairs.Infrastructure.Persistence.Models;

public partial class PropertyUser
{
    public long MyRowId { get; set; }

    public long PropertyId { get; set; }

    public long UserId { get; set; }

    public virtual Property Property { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}