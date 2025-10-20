namespace Downstairs.Infrastructure.Persistence.Models;

public partial class StoreSaleProduct
{
    public long Id { get; set; }

    public long StoreSaleId { get; set; }

    public long ProductId { get; set; }

    public string Name { get; set; } = null!;

    public string? Note { get; set; }

    public byte Quantity { get; set; }

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public decimal Discount { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Product Product { get; set; } = null!;

    public virtual StoreSale StoreSale { get; set; } = null!;
}