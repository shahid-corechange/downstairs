namespace Downstairs.Infrastructure.Persistence.Models;

public partial class FixedPriceRow
{
    public long Id { get; set; }

    public long FixedPriceId { get; set; }

    public string Type { get; set; } = null!;

    public long Quantity { get; set; }

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public bool HasRut { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual FixedPrice FixedPrice { get; set; } = null!;
}