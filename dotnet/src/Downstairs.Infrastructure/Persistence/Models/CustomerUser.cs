namespace Downstairs.Infrastructure.Persistence.Models;

public partial class CustomerUser
{
    public long MyRowId { get; set; }

    public long CustomerId { get; set; }

    public long UserId { get; set; }

    public virtual Customer Customer { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}