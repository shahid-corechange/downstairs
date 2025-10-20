namespace Downstairs.Infrastructure.Persistence.Models;

public partial class StoreSale
{
    public long Id { get; set; }

    public long StoreId { get; set; }

    public long CauserId { get; set; }

    public string Status { get; set; } = null!;

    public string? PaymentMethod { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User Causer { get; set; } = null!;

    public virtual Store Store { get; set; } = null!;

    public virtual ICollection<StoreSaleProduct> StoreSaleProducts { get; set; } = new List<StoreSaleProduct>();
}