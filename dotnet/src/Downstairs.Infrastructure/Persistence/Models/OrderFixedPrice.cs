namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OrderFixedPrice
{
    public long Id { get; set; }

    public long? FixedPriceId { get; set; }

    public string Type { get; set; } = null!;

    public bool IsPerOrder { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual FixedPrice? FixedPrice { get; set; }

    public virtual ICollection<OrderFixedPriceRow> OrderFixedPriceRows { get; set; } = new List<OrderFixedPriceRow>();

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual ICollection<Product> Products { get; set; } = new List<Product>();
}