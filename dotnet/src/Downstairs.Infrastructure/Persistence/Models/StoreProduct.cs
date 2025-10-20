namespace Downstairs.Infrastructure.Persistence.Models;

public partial class StoreProduct
{
    public long StoreId { get; set; }

    public long ProductId { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Product Product { get; set; } = null!;

    public virtual Store Store { get; set; } = null!;
}