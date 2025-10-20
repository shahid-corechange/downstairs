namespace Downstairs.Infrastructure.Persistence.Models;

public partial class StoreUser
{
    public long MyRowId { get; set; }

    public long StoreId { get; set; }

    public long UserId { get; set; }

    public virtual Store Store { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}