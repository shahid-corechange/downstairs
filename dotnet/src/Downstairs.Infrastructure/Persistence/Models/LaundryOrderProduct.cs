namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LaundryOrderProduct
{
    public long Id { get; set; }

    public long LaundryOrderId { get; set; }

    public long ProductId { get; set; }

    public string Name { get; set; } = null!;

    public string? Note { get; set; }

    public byte Quantity { get; set; }

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public decimal Discount { get; set; }

    public bool HasRut { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual LaundryOrder LaundryOrder { get; set; } = null!;

    public virtual Product Product { get; set; } = null!;
}